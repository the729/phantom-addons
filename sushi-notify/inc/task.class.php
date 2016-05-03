<?php
require __DIR__ . '/../vendor/autoload.php';
require_once(__DIR__ . "/util.php");
require_once(__DIR__ . "/user.class.php");

use Pheanstalk\Pheanstalk;

class Task
{
    public $id = 0;
    
    static public function create_new($scheme_id, $delay, $action) {
        $pheanstalk = new Pheanstalk('127.0.0.1');
        if (! $pheanstalk->getConnection()->isServiceListening()) {
            return NULL;
        }
        $pheanstalk->useTube('sushi-notify');

        $scheme_id = intval($scheme_id);
        $sql = "insert into notify_task (notify_scheme_id, state, created_at, updated_at) values ($scheme_id, 'waiting', now(), now())";
        
        $res = mysql_query($sql);
        if (!$res || mysql_affected_rows() < 1) {
            return NULL;
        }
        
        $task_id = mysql_insert_id();
        
        $data = array();
        $data['type'] = "DoorTimeout-v1";
        $data['content'] = array();
        $data['content']['task_id'] = $task_id;
        
        $data = json_encode($data);
        $pheanstalk->put($data, Pheanstalk::DEFAULT_PRIORITY, $delay);
        
        $task = new self();
        $task->id = $task_id;
        return $task;
    }
    
    static public function close_waiting_tasks($sushi_id, $user_id) {
        $sushi_id = intval($sushi_id);
        $user_id = intval($user_id);
        
        # note: we should close task even if scheme is disabled
        $sql = "update notify_task inner join notify_scheme on notify_task.notify_scheme_id = notify_scheme.id set state = 'closed', updated_at = now() where monitor_sushi = $sushi_id and phantom_user_id = $user_id and state = 'waiting'";
        $res = mysql_query($sql);
        if (!$res) return NULL;
        return mysql_affected_rows();
    }
    
    static public function close_alarmed_tasks($sushi_id, $user_id) {
        $sushi_id = intval($sushi_id);
        $user_id = intval($user_id);
        
        # note: we should close task even if scheme is disabled
        $sql = "select notify_action from notify_task inner join notify_scheme on notify_task.notify_scheme_id = notify_scheme.id where monitor_sushi = $sushi_id and phantom_user_id = $user_id and state = 'alarmed'";
        
        $res = mysql_query($sql);
        $actions = array();
        while ($row = mysql_fetch_row($res)) {
            $actions[] = $row[0];
        }
        
        $sql = "update notify_task inner join notify_scheme on notify_task.notify_scheme_id = notify_scheme.id set state = 'closed', updated_at = now() where monitor_sushi = $sushi_id and phantom_user_id = $user_id and state = 'alarmed'";
        $res = mysql_query($sql);

        foreach ($actions as $action) {
            $a = json_decode($action);
            if (!$a->auto_disarm) 
                self::run_disarm_action($a, $user_id);
        }
        
        if (!$res) return NULL;
        return mysql_affected_rows();
    }
    
    static public function run_disarm_action($action, $user_id)
    {
        log_open();
        log_write("[DD] execute disarm action.");
        
        if ($action->version == 1) {
            $user = User::get_user_by_id($user_id);
            $user->execute_actions($action->disarm_action);
        }
        return;
    }
    
    static public function run_alarm_action($action, $user_id)
    {
        log_open();
        log_write("[DD] execute alarm action.");
        
        if ($action->version == 1) {
            $user = User::get_user_by_id($user_id);
            $user->execute_actions($action->alarm_action);
            
            if ($action->auto_disarm > 0) {
                $pheanstalk = new Pheanstalk('127.0.0.1');
                if (! $pheanstalk->getConnection()->isServiceListening()) {
                    return;
                }
                $pheanstalk->useTube('sushi-notify');

                $data = array();
                $data['type'] = "Disarm-v1";
                $data['content'] = array(
                    'action_str' => $action,
                    'user_id' => $user_id,
                );
                
                $data = json_encode($data);
                $pheanstalk->put($data, Pheanstalk::DEFAULT_PRIORITY, $action->auto_disarm);
            }
        }
        return;
    }
    
    static public function get_task_by_id($task_id)
    {
        $task = new self();
        $task->id = $task_id;
        return $task;
    }
    
    public function door_timeout()
    {
        $task_id = intval($this->id);
        $sql = "update notify_task inner join notify_scheme on notify_task.notify_scheme_id = notify_scheme.id set state = 'alarmed', updated_at = now() where enabled and notify_task.id = $task_id and state = 'waiting'";
        
        $res = mysql_query($sql);
        if (!$res || mysql_affected_rows() < 1) return;
        
        $sql = "select notify_action, phantom_user_id from notify_task inner join notify_scheme on notify_task.notify_scheme_id = notify_scheme.id where notify_task.id = $task_id";
        
        $res = mysql_query($sql);
        if (!$res || mysql_num_rows($res) < 1) return;
        $dat = mysql_fetch_assoc($res);
        $action = $dat['notify_action'];
        $user_id = $dat['phantom_user_id'];
        
        $action = json_decode($action);
        
        self::run_alarm_action($action, $user_id);
        return;
    }
}