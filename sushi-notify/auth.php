<?php
require_once("inc/util.php");
require_once("inc/user.class.php");

mysession_start();

if ($_REQUEST['action'] == 'logout') {
    
    session_destroy();
    echo "Logout successful. <br/>";
    echo "<a href=\"main.php\">Login again</a>";
    
} elseif ($_REQUEST['code']) {
    // We got code, goto exchange for token
    
    // set post fields
    $post = [
        'client_id' => APP_ID,
        'client_secret' => APP_SECRET,
        'redirect_uri'  => APP_BASE . '/auth.php',
        'grant_type' => 'authorization_code',
        'code' => $_REQUEST['code']
    ];
    
    $r = http_request('https://huantengsmart.com/oauth2/token', 'POST', $post);

    $response = json_decode($r['response']);
    
    if (array_key_exists('error', $response)) {
        echo "<h1>Authorization failed.</h1>";
        exit(0);
    }
    
    db_conn();
    
    $user = User::update_user_by_token($response);
    if (!$user) {
        echo "<h1>Get user details failed.</h1>";
        exit(0);
    }
    
    $_SESSION['puser_id'] = $user->id;
    $_SESSION['puser_name'] = $user->name;
    header("Location: main.php");
    
} else {
    $data = [
        'client_id' => APP_ID,
        'redirect_uri' => APP_BASE . '/auth.php',
        'response_type' => 'code',
        'scope' => 'read_user read_wall_switch write_wall_switch read_door_sensor monitor_door_sensor read_bulb write_bulb'
    ];
    header("Location: https://huantengsmart.com/oauth2/authorize?" . http_build_query($data));
}
