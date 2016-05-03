<?php
require_once(__DIR__ . "/../inc/util.php");
require_once(__DIR__ . "/../inc/scheme.class.php");
require_once(__DIR__ . "/../inc/task.class.php");

function parse_sushi_notify($task) {
    $user_id = intval(substr($task->type, strrpos($task->type, "-")+1));
    
    $event = $task->content;
    
    if ($event->is_open) {
        # door opened
        $schemes = Scheme::list_schemes($event->door_sensor_id, $user_id);
        if (!$schemes) return;
        foreach($schemes as $scheme) {
            if ( $scheme['task_id'] === null ) {
                # create new task
                $task = Task::create_new(
                    $scheme['scheme_id'], 
                    $scheme['notify_delay_sec'], 
                    $scheme['notify_action']
                );
            }
        }
    } else {
        # door closed
        Task::close_waiting_tasks($event->door_sensor_id, $user_id);
        Task::close_alarmed_tasks($event->door_sensor_id, $user_id);
    }
}

#db_conn();
#parse_sushi_notify(json_decode('{"type":"DoorSensorsChanged-v1-1372684091","content":{"door_sensor_id":83,"alert_status":1,"alert_mode":0,"is_open":true}}'));