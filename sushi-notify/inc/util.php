<?php
require_once "conf.php";
require_once "db.php";

function mysession_start() {
    if (!isset($_SESSION)) {
        session_start();
    }
    // $now = time();
    // if(isset($_SESSION['session_time']) && ($now-$_SESSION['session_time'])>SESSION_TIMEOUT)
    // {
    //     //time out
    //     $_SESSION = array();
    // }
    // $_SESSION['session_time']=$now;
}

function http_request($url, $method, $data = NULL, $token = NULL)
{
    $ch = curl_init($url);
    $header = array();
    $header[] = "Accept: application/json";
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); //timeout in seconds
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        if (is_string($data)) {
            $header[] = "Content-Type: application/json";
        }
    }
    if ($token) {
        $header[] = "Authorization: bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    // execute!
    $response = curl_exec($ch);
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // close the connection, release resources used
    curl_close($ch);

    //echo "Verbose information:\n<pre>", htmlspecialchars($verboseLog), "</pre>\n";
    //$response = json_decode($response);
    return ['status' => $status, 'response' => $response];
}

function log_open()
{
    if (! isset($GLOBALS['logfile']))
        $GLOBALS['logfile'] = fopen(__DIR__."/../notify-log.txt", "a");
}

function log_write($data)
{
    fwrite($GLOBALS['logfile'], date("Y-m-d H:i:s") . " - $data \r\n");
}

function log_close()
{
    if (isset($GLOBALS['logfile'])) {
        fclose($GLOBALS['logfile']);
        unset($GLOBALS['logfile']);
    }
}

/*
function do_auth() {
    // require_once("inc/phpFlickr.php");
    if ( isset($_SESSION['phpFlickr_auth_redirect']) && !empty($_SESSION['phpFlickr_auth_redirect']) ) {
        $redirect = $_SESSION['phpFlickr_auth_redirect'];
        unset($_SESSION['phpFlickr_auth_redirect']);
    }
    
    $f = new phpFlickr(API_KEY, API_SECRET);
    
    if (empty($_GET['frob'])) {
        $r = $f->auth('write');
        $_SESSION['nsid'] = $f->parsed_response['auth']['user']['nsid'];
        $_SESSION['user'] = $f->parsed_response['auth']['user'];
        return $f;
    } else {
        $f->auth_getToken($_GET['frob']);
        $_SESSION['nsid'] = $f->parsed_response['auth']['user']['nsid'];
        update_guest_votes();
    }
    
    if (empty($redirect)) {
        header("Location: index.php");
    } else {
        header("Location: " . $redirect);
    }
    exit();
}*/
/*
function update_guest_votes() {
    db_conn();
    $nuid = mysql_escape_string($_SESSION['nsid']);
    $ouid = mysql_escape_string("GUEST" . session_id());
    $sql = "update choice set userid='$nuid' where userid='$ouid' ";
    mysql_query($sql);
}
*/
function getIP(){
    if (getenv('HTTP_CLIENT_IP')) {
        $ip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ips = explode(',',getenv('HTTP_X_FORWARDED_FOR'));
        $ip = $ips[0];
    } else {
        $ip = getenv('REMOTE_ADDR');
    }
    return $ip;
}
