<?php
//header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?>';
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

$db = new DB();
if( $db->connect() )
	$prices = load_prices( $db );
else
	$price_count = 0;
//print_r($prices);
//print "!";die();

$q = $db->query("SELECT ROWID FROM ITEMS");
$r = $db->fetch_assoc($q);
print_r($r);
die();
$hier = array();
for( $i = 1; $i < $price_count; ++$i )
	$hier[ $i ] = $db->fetch_tree( 'SELECT ROWID, Kommentariy name FROM COSTS WHERE VidCZeniCzeni='.$prices[ $i ][ 'ID' ].' AND Razdel_f=0 AND Razdel=' );

//print_r($db);
die();

function get_cats($cats, $n) {
	global $xml, $db;
	foreach ($cats as $cat) {
		$xml .= '<category>';
		$xml .= '<PorNomer>'.(10 * ($n++)).'</PorNomer> ';
		$xml .= '<ID>'.$cat['ROWID'].'</ID> ';
		$xml .= '<name>'.iconv("windows-1251","utf-8",$cat['name']).'</name> ';
		if (count($cat['childs'])) {
			$xml .= '<childs>';
			get_cats($cat['childs'], 1);
			$xml .= '</childs>';
		}
		$f_query = $db->query( 'SELECT c.NomenklaturaCzeni, i.Foto, i.Opisanie from COSTS c LEFT JOIN ITEMS i ON c.NomenklaturaCzeni=i.ROWID WHERE c.ROWID='.$cat['ROWID']);
		if( ( $f_rec = $db->fetch_assoc( $f_query ) ) ) {
			$xml .= '<image>'.$f_rec[ 'Foto' ].'</image> ';
			$query = $db->query( 'SELECT data FROM ITEMS_ WHERE ROWID='.$f_rec[ 'Opisanie' ] );
			if (($rec = $db->fetch_assoc( $query ))) {
				$rec['data'] = iconv("windows-1251","utf-8",$rec['data']);
				if ($rec['data']) $xml .= '<desc>'.htmlspecialchars(str_replace("
 ","",$rec['data'])).'</desc> ';
			}
		}
		$xml .= '</category>';
	}
}

$xml = "<categories>";
foreach($hier as $i => $cats) {
	$xml .= '<category>';
	$xml .= '<PorNomer>'.($i*10).'</PorNomer> ';
	$xml .= '<price_id>'.$i.'</price_id>';
	$xml .= '<ID>'.$prices[$i]['ID'].'</ID>';
	$xml .= '<Ver>'.$prices[$i]['Ver'].'</Ver>';
	$xml .= '<name>'.iconv("windows-1251","utf-8",$prices[$i]['Name']).'</name>';
	$xml .= '<childs>';
	get_cats($cats, 1);
	$xml .= '</childs>';
	$xml .= '</category>';
}
$xml .= "</categories>";
print $xml;
$db->close();