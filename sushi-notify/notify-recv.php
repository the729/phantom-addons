<?php
require __DIR__ . '/vendor/autoload.php';
require_once("inc/util.php");

use Pheanstalk\Pheanstalk;

header("HTTP/1.1 204 No Content");

log_open();
$data = file_get_contents('php://input');
log_write("[DD] $data");

$jdata = json_decode($data);

if (!$jdata) exit(0);

$pheanstalk = new Pheanstalk('127.0.0.1');
if (! $pheanstalk->getConnection()->isServiceListening()) {
    log_write("[EE] Beanstalkd not working.");
    exit(0);
}
$pheanstalk->useTube('sushi-notify');

if ( 0 === strpos($jdata->type, 'DoorSensorsChanged-v1')) {
    // Door sensor notification
    $pheanstalk->put($data);
}

log_close();
