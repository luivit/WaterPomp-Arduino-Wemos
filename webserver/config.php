<?php
define("USER","admin");
define("PASS","admin");
define("URL_BASE","/valvola/");
$vldb = new mysqli("localhost", "root", "", "valvola");
if ($vldb->connect_errno) {
    echo "Failed to connect to MySQL: (" . $lgdb->connect_errno . ") " . $lgdb->connect_error;
}
?>