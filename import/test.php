<?php
require 'export/config.php';
require 'export/db.php';
require 'export/prices.php';
require 'export/foto.php';


$db = new DB();
print "start<br/>";
if ($db->connect()) {
	print "connect<br/>";
    error_reporting(E_ALL);
    $results = $db->fetch_array("SELECT * FROM ACCOUNTS");
    print_r($results);
}