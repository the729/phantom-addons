<?php
require_once("inc/conf.php");

$cmd = "ps aux | grep ${argv[0]} | wc -l";
$workerNum = intval( shell_exec($cmd) ) - 1;

if ($workerNum > MAX_WORKER_NUM) exit(0);

include(__DIR__ . "/worker/worker_real.php");
