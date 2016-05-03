<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'phantom_sushi_notify');
define('DB_USER', 'db_username');
define('DB_PASSWD', 'db_password');

// define('SESSION_TIMEOUT', '1200');

define('APP_ID', 'app_id');
define('APP_SECRET', 'app_secret');

define('APP_BASE', 'http://xxxxx');
#define('APP_BASE', 'http://pg1.wutj.info/sushi-notify');

define('API_BASE', 'https://huantengsmart.com/api');
define('BATCHAPI_BASE', 'https://huantengsmart.com/batchapi');

define('MAX_WORKER_NUM', 6);
define('MAX_WORKER_LIFE_TIME', 1800);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
