<?php
require_once(__DIR__ . "/util.php");

class Scheme
{
    static public function list_schemes($sushi_id, $user_id)
    {
        # list all schemes matches sushi_id and user_id 
        # and join unfinished tasks if there are any
        $sushi_id = intval($sushi_id);
        $user_id = intval($user_id);
        $sql = "select notify_scheme.id as scheme_id, notify_delay_sec, notify_action, notify_task.id as task_id, state, created_at, updated_at from notify_scheme left join notify_task on notify_scheme.id = notify_task.notify_scheme_id and state != 'closed' where enabled and monitor_sushi = $sushi_id and phantom_user_id = $user_id";

        $res = mysql_query($sql);
        if (!$res) return NULL;
        
        $d = array();
        while ($row = mysql_fetch_assoc($res)) {
            $d[] = $row;
        }
        
        return $d;
    }
}