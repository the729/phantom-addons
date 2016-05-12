<?php
require_once(__DIR__ . "/util.php");

class Sushi
{
    static public function update_event($sushi_id, $user_id, $event_time)
    {
        # check if the current event is the latest
        # return 1 if this is new event. 0 if not.
        # update sushi memory table accordingly.
        $sushi_id = intval($sushi_id);
        $user_id = intval($user_id);
        $event_time = intval($event_time);
        $sql = "insert into sushi_update_time (phantom_user_id, sushi_id, update_event_time) values (${user_id}, ${sushi_id}, ${event_time}) on duplicate key update update_event_time = if(update_event_time > values(update_event_time), update_event_time, values(update_event_time))";

        $res = mysql_query($sql);
        if (!$res) return 0;
        
        if (mysql_affected_rows() == 0) {
            # no row updated. must be old
            return 0;
        } else {
            # new
            return 1;
        }
    }
}
