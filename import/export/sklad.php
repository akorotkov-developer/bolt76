<?php
print file_get_contents("http://stroyprofi.prominado.ru/import/export/cats.php");
die();

require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

set_time_limit(3600);

$db = new DB();
$image_name = '+no-image';
if ($db->connect() )
{
	$prices = load_prices( $db );
	//$q = 'SELECT PorNomer, NomerSklada, NomenklaturaSkla, TipZapasa, Priznaki, DopPriznaki, DopPriznaki2, Ispolzovanie, VRezerve, Razdel, Razdel_f, Ostatok from SKLAD';
	$q = 'SELECT NomenklaturaSkla, Ostatok from SKLAD WHERE NomenklaturaSkla=12997';
	$result = $db->fetch_array($q);
	print "<pre>".print_r($result, true)."</pre>";
}
