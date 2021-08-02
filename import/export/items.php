<?php
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?>';
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

set_time_limit(3600);
global $SERVER;
global $CON_STR;
global $OWNER;


$db = new DB();
$image_name = '+no-image';
if ($db->connect() )
{
	$prices = load_prices( $db );
	$root_record = $_REQUEST['cat']?$_REQUEST['cat']:$ROOT;
	$price_id = $_GET['price_id'];
	$q =  'SELECT c.ROWID, i.ROWID iROWID, c.Razdel_f, c.Kommentariy, c.PorNomer, c.KodUpakovki cKodUpakovki, i.KratnostOtpuska, i.KodUpakovki, i.Artikul, i.Svertka, i.Naimenovanie, i.Foto, i.CZena1, i.CZena2, i.CZena3, i.EdIzmereniya, i.Opisanie from COSTS c LEFT JOIN ITEMS i ON c.NomenklaturaCzeni=i.ROWID WHERE c.Razdel='.$root_record;
	if($price_id) $q .= ' AND c.VidCZeniCzeni='.$prices[ $price_id ][ 'ID' ];
	$q .= ' ORDER BY c.PorNomer' ;
	$result = $db->fetch_array($q);
	?><items><?
	foreach($result as $item) {
		if (!$item['Artikul']) continue;
		$q = "SELECT NomenklaturaUpak, Kod, VUpakovke, VesUpakovki, SHtrihKod, VidUpakovki, ROWID, VARSTRING FROM PACKAGES WHERE NomenklaturaUpak=".$item['iROWID']." ";// AND Kod=".$item['KodUpakovki'];
		$package = $db->fetch_array($q);
		$q = 'SELECT NomenklaturaSkla, Ostatok, VRezerve from SKLAD WHERE NomenklaturaSkla='.$item['iROWID'];
		$sklads = $db->fetch_array($q);
		$ostatok = 0;
		foreach($sklads as $sklad) {
			$ostatok += (float)$sklad['Ostatok'];
			$ostatok -= (float)round($sklad['VRezerve'], 3);
		}
		$item['Svertka'] = $item['Svertka']?$item['Svertka']:"-";
		$item['Naimenovanie'] = $item['Naimenovanie']?$item['Naimenovanie']:$item['Svertka'];

		$toUtf8 = array("Svertka", "Kommentariy", "Naimenovanie", "EdIzmereniya");
		foreach ($toUtf8 as $name) {
			$item[$name] = iconv("windows-1251","utf-8",$item[$name]);
		}
		?><item><?
			?><ID><?=$item['ROWID'];?></ID><?
			?><iROWID><?=$item['iROWID'];?></iROWID><?
			?><Razdel_f><?=$item['Razdel_f'];?></Razdel_f><?
			?><VUpakovke><?=$package[0]['VUpakovke'];?></VUpakovke><?
			?><VUpakovke2><?=$package[1]['VUpakovke'];?></VUpakovke2><?
			?><Ostatok><?=$ostatok;?></Ostatok><?
			?><VRezerve>0</VRezerve><?
			?><KodUpakovki><?=$item['KodUpakovki'];?></KodUpakovki><?
			?><KratnostOtpuska><?=$item['KratnostOtpuska'];?></KratnostOtpuska><?
			?><Kommentariy><?=$item['Kommentariy'];?></Kommentariy><?
			?><PorNomer><?=$item['PorNomer'];?></PorNomer><?
			?><Artikul><?=$item['Artikul'];?></Artikul><?
			?><Svertka><?=$item['Svertka'];?></Svertka><?
			?><Naimenovanie><?=$item['Naimenovanie'];?></Naimenovanie><?
			?><Foto><?=$item['Foto'];?></Foto><?
			?><CZena1><?=$item['CZena1'];?></CZena1><?
			?><CZena2><?=$item['CZena2'];?></CZena2><?
			?><CZena3><?=$item['CZena3'];?></CZena3><?
			?><EdIzmereniya><?=$item['EdIzmereniya'];?></EdIzmereniya><?
			?><Opisanie><?=$item['Opisanie'];?></Opisanie><?
			$query = $db->query( 'SELECT data FROM ITEMS_ WHERE ROWID='.$item['Opisanie']);
			if (($rec = $db->fetch_assoc( $query ))) {
				$rec['data'] = iconv("windows-1251","utf-8",str_replace("
 ","",$rec['data']));
				if ($rec['data']) print '<desc>'.htmlspecialchars(str_replace("
 ","",$rec['data'])).'</desc> ';
			}
			?></item><?
	}
	?></items><?
}
