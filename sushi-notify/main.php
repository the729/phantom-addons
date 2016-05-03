<?php
require_once("inc/util.php");

mysession_start();

if (!$_SESSION['puser_id']) {
    header("Location: auth.php");
    exit(0);
}

echo "<h1>User Info</h1>";
echo "Hello ${_SESSION['puser_name']} ";
echo "<a href=\"auth.php?action=logout\">Logout</a>";
