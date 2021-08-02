<?php
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

set_time_limit(3600);

$db = new DB();
$image_name = '+no-image';
print "-3491, -3630, +6848, +3571";
if ($db->connect() )
{
    $q = "SELECT NomenklaturaSkla FROM SKLAD WHERE NomerSklada = 9 AND TipZapasa = 0";
    $result = $db->fetch_array($q);
    foreach($result as $item) {
        print_r($item);
	    print "<hr/>";
    }

}
