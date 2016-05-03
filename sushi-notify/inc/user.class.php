<?php
require_once(__DIR__ . "/util.php");

class User
{
    public $id = 0;
    public $name = '';
    
    static public function update_user_by_token($token_pack)
    {
        $r = http_request(API_BASE . '/user', 'GET', NULL, $token_pack->access_token);
        
        if ($r['status'] != 200) 
            return NULL;
        
        $r = json_decode($r['response']);
        
        $puser_id = intval($r->user_uniq_id);
        $name = mysql_real_escape_string($r->name);
        $access_token = mysql_real_escape_string($token_pack->access_token);
        $token_expire = intval($token_pack->created_at + $token_pack->expires_in);
        $refresh_token = mysql_real_escape_string($token_pack->refresh_token);
        
        $sql = "insert into phantom_user (phantom_user_id, name, access_token, access_token_expire, refresh_token) values (${puser_id}, '${name}', '${access_token}', FROM_UNIXTIME(${token_expire}), '${refresh_token}') on duplicate key update name='${name}', access_token='${access_token}', access_token_expire=FROM_UNIXTIME(${token_expire}), refresh_token='${refresh_token}'";
        $res = mysql_query($sql);
        if (!$res || mysql_affected_rows() < 1) {
            return NULL;
        }
        
        $user = new self();
        $user->id = $puser_id;
        $user->name = $name;
        return $user;
    }
    
    static public function get_user_by_id($user_id)
    {
        $user = new self();
        $user->id = intval($user_id);
        return $user;
    }
    
    public function get_access_token()
    {
        if (!$this->id) return NULL;
        
        $puser_id = intval($this->id);
        $sql = "select access_token, UNIX_TIMESTAMP(access_token_expire) as expire, refresh_token from phantom_user where phantom_user_id = '${puser_id}'";
        $res = mysql_query($sql);
        if (!$res || mysql_num_rows($res) < 1) {
            return NULL;
        }
        $dat = mysql_fetch_assoc($res);
        if ($dat['expire'] < time() + 100) {
            // refresh token
            $post = [
                'grant_type' => 'refresh_token',
                'refresh_token' => $dat['refresh_token']
            ];
            
            $r = http_request('https://huantengsmart.com/oauth2/token', 'POST', $post);

            $response = json_decode($r['response']);
            
            if (array_key_exists('error', $response)) {
                return NULL;
            }
            update_user_by_token($response);
            return $response->access_token;
        } else {
            return $dat['access_token'];
        }
    }
    
    public function execute_actions($action_list)
    {
        $token = $this->get_access_token();
        if (!$token) return;
        
        $ops = array(
            'ops' => array(),
            'sequential' => true
        );
        foreach ($action_list as $action_line) {
            if ($action_line->type == 'bulb') {
                if ($action_line->action == 'on') {
                    $bulb_id = intval($action_line->id);
                    $op = array(
                        'method' => 'POST',
                        'url' => "/api/bulbs/${bulb_id}/switch_on"
                    );
                } elseif ($action_line->action == 'off') {
                    $bulb_id = intval($action_line->id);
                    $op = array(
                        'method' => 'POST',
                        'url' => "/api/bulbs/${bulb_id}/switch_off"
                    );
                } elseif ($action_line->action == 'tune') {
                    $bulb_id = intval($action_line->id);
                    $op = array(
                        'method' => 'POST',
                        'url' => "/api/bulbs/${bulb_id}/tune",
                        'params' => array (
                            'brightness' => floatval($action_line->brightness),
                            'hue' => floatval($action_line->hue)
                        )
                    );
                } else {
                    continue;
                }
                $op['headers'] = array(
                    'Authorization' => "bearer $token"
                );
                $ops['ops'][] = $op;
            }
        }
        $ops = json_encode($ops);
        
        echo "Begin request";
        $r = http_request(BATCHAPI_BASE, 'POST', $ops);
        
        var_dump($r);
        return $r;
    }
}
