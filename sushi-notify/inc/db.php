<?php
require_once("util.php");

function db_conn() {
	@$mysql_id = mysql_connect(DB_HOST, DB_USER, DB_PASSWD);
	if ($mysql_id == false or !$mysql_id) {
		echo ("can not connect database: " . mysql_error());
		exit(1);
	}
	@$sel=mysql_select_db(DB_NAME,$mysql_id);	
	if ($sel == false) {
		echo ("can not select database: " . mysql_error());
		exit(1);
	}
	@mysql_query('SET NAMES \'utf8\'');
}
