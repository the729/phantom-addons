<?php
require __DIR__ . '/../vendor/autoload.php';

require_once(__DIR__ . "/../inc/util.php");
require_once(__DIR__ . "/parse_sushi_notify.php");
require_once(__DIR__ . "/parse_door_timeout.php");

use Pheanstalk\Pheanstalk;

$pheanstalk = new Pheanstalk('127.0.0.1');

log_open();
log_write("[DD] Start worker...");
echo "Start worker\r\n";
log_close();    

$startTime = time();
while (time() - $startTime < MAX_WORKER_LIFE_TIME) {
    
    if (!$pheanstalk->getConnection()->isServiceListening()) {
        sleep(60);
        continue;
    }
    
    $job = $pheanstalk
        ->watch('sushi-notify')
        ->ignore('default')
        ->reserve();
    
    if (!is_object($job)) {
        sleep(30);
        continue;
    }
    
    log_open();
    
    db_conn();
    
    $task = $job->getData();
    log_write("[DD] Get task: $task");
    
    $task = json_decode($task);
    if ( 0 === strpos($task->type, 'DoorSensorsChanged-v1')) {
        parse_sushi_notify($task);
    } elseif ( 0 === strpos($task->type, 'DoorTimeout')) {
        parse_door_timeout($task);
    } elseif ( 0 === strpos($task->type, 'Disarm')) {
        parse_disarm($task);
    }
    
    $pheanstalk->delete($job);
    
    flush();
    log_close();
}
