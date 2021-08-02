<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="windows-1251">
    <title></title>
</head>
<body>
<?php
require 'config.php';
require 'db_odbc.php';

ini_set('display_errors', 'on');
error_reporting(E_ALL);
set_time_limit(15);
$db = new DB();
if ($db->connect() )
{
    $Razdel = 17287;
    $q = "SELECT NomenklaturaSkla FROM SKLAD WHERE NomerSklada = 9 AND NomenklaturaSkla IN (SELECT ROWID FROM ITEMS WHERE Razdel = ".$Razdel.")";
    $result = $db->fetch_array($q);
    $onSklad = Array();
    foreach ($result as $r) {
        $onSklad[] = $r['NomenklaturaSkla'];
    }
    print_r($onSklad);
}



die();
if ($db->connect() )
{
	$tables = file_get_contents("tables.txt");
	$tables = explode("\n",$tables);
	$tableName = "";
	$i = 0;
	$f = fopen(date("d.m.Y.H.i.s").".html", "a+");
	foreach ($tables as $table) {
		$table = trim($table);
		if ($table) {
			if (!strpos($table, " ")) {
				$tableName = $table;
				fputs($f, "<b>".$tableName."</b><br/>");
			} else {
				if ($tableName) {
					$column = explode(" ", $table);
					$column = $column[0];
					$r = $db->fetch_array("SELECT ".$column." FROM ".$tableName." WHERE ".$column." LIKE '%����%'");
					if (count($r)) {
						fputs($f, "$column:<br/>");
						fputs($f, print_r($r, true));
						fputs($f, "<hr/>");
					}
				}
			}
		}
		//if ($i++ > 100) die("i");
	}
	fclose($f);
	die();
	$q = 'SELECT ROWID, Kommentariy name FROM COSTS WHERE ROWID=17287';
	$result = $db->fetch_array($q);
	print "<pre>";
	print_r($result);
	print "</pre>";
}
?>
</body>
</html>