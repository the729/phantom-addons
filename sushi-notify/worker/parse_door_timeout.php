<?php
require_once(__DIR__ . "/../inc/util.php");
require_once(__DIR__ . "/../inc/scheme.class.php");
require_once(__DIR__ . "/../inc/task.class.php");

function parse_door_timeout($task) {
    $task_id = $task->content->task_id;
    
    $task = Task::get_task_by_id($task_id);
    $task->door_timeout();
}

function parse_disarm($task) {
    Task::run_disarm_action($task->content->action_str, $task->content->user_id);
}
