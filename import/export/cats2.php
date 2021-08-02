<?php
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?>';
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

$db = new DB();
if ($db->connect())
	$prices = load_prices( $db );
else
	$price_count = 0;

set_time_limit(120);

if (isset($_GET['SITE_ID']) and is_numeric($_GET['SITE_ID'])) {
    $showOnSiteID = $_GET['SITE_ID'];
}

$results = $db->fetch_array("SELECT * FROM PARAMHST WHERE ParametrIstoriya=".$showOnSiteID);
global $displayCats;
$displayCats = Array(
    "0" => 1
);
foreach($results as $result) {
    $displayCats[$result['LiczoIstoriya']] = (int)$result['Znachenie'];
}
asort($displayCats);

function get_cats($catID, $parentLizco) {
    global $xml, $db, $displayCats, $skladID;
    $catsOnSklad = $db->fetch_array("SELECT NomenklaturaSkla, ROWID FROM SKLAD WHERE NomerSklada=$skladID AND Razdel=$catID AND Razdel_f=0");
    $toSelect = Array();
    foreach($catsOnSklad as $catOnSklad) {
        $toSelect[$catOnSklad['NomenklaturaSkla']] = $catOnSklad['ROWID'];
    }
    if (count($toSelect) > 0) {
        //$cats = $db->fetch_array("SELECT i.Naimenovanie Name, i.Liczo_ Liczo_, i.ROWID ROWID, i.Foto Fata, i.Opisanie Opisanie, s.ROWID sROWID FROM ITEMS i, SKLAD s WHERE s.NomenklaturaSkla = i.ROWID AND s.NomerSklada=$skladID AND s.Razdel=$catID");
        $cats = $db->fetch_array('SELECT ROWID, Naimenovanie Name, Foto, Opisanie, Liczo_ FROM ITEMS WHERE ROWID IN ('.implode(", ", array_keys($toSelect)).') ORDER BY Naimenovanie ASC');
        if (count($cats) > 0) {
            $datasIDs = Array();
            $catIDs = Array();
            foreach ($cats as $cat) {
                if ($cat['Opisanie'] && ($cat['Opisanie']!=19644)) $datasIDs[] = $cat['Opisanie'];
                //if ($cat['Foto']) $datasIDs[] = $cat['Foto'];
                $catIDs[] = $cat['ROWID'];
            }
            $query = $db->query("SELECT ROWID, data FROM ITEMS_ WHERE ROWID IN (".implode(", ", $datasIDs).")");
            $datas = Array();
            while (($rec = $db->fetch_assoc( $query ))) {
                $datas[$rec['ROWID']] = $rec['data'];
            }

            $q = "SELECT NomenklaturaSkla FROM SKLAD WHERE NomerSklada = 9 AND NomenklaturaSkla IN (".implode(",", $catIDs).")";
            $result = $db->fetch_array($q);
            $onSklad = Array();
            foreach ($result as $r) {$onSklad[] = $r['NomenklaturaSkla'];}

            $xml .= '<childs>';
            $n = 1;
            foreach ($cats as $cat) {
                $cat['sROWID'] = $toSelect[$cat['ROWID']];
                if (!in_array($cat['ROWID'], $onSklad)) continue;
                if (!isset($displayCats[$cat['Liczo_']])) {
                    $displayCats[$cat['Liczo_']] = $displayCats[$parentLizco];
                }
                if ($displayCats[$cat['Liczo_']] !== 0) {
                    $xml .= '<category>';
                    $xml .= '<PorNomer>'.((100 * $displayCats[$cat['Liczo_']]) + ($n++)).'</PorNomer> ';
                    $xml .= '<ID>'.$cat['ROWID'].'</ID> ';
                    $xml .= '<sID>'.$cat['sROWID'].'</sID> ';
                    $xml .= '<name>'.iconv("windows-1251","utf-8",$cat['Name']).'</name> ';
                    if ($cat['Foto']) $xml .= '<image>'.$cat['Foto'].'</image> ';
                    //if ($cat['Foto']) $xml .= '<imageHash>'.md5($datas[$cat['Foto']]).'</imageHash> ';
                    if ($datas[$cat['Opisanie']]) {
                        $desc = iconv("windows-1251","utf-8",str_replace("
 ","",$datas[$cat['Opisanie']]));
                        if ($desc) $xml .= '<desc>'.htmlspecialchars(str_replace("
 ","",$desc)).'</desc>';
                    }
                    get_cats($cat['sROWID'], $cat['Liczo_']);
                    $xml .= '</category>';
                }
            }
            $xml .= '</childs>';
        }
    }
}

$root = 0xFFFFFFF6;
$hier = Array();
$i = 1;
$results = $db->fetch_array("SELECT i.Naimenovanie Name, i.Liczo_ Liczo_, i.ROWID ROWID, s.ROWID sROWID FROM ITEMS i, SKLAD s WHERE s.NomenklaturaSkla = i.ROWID AND s.NomerSklada=$skladID AND s.Razdel=$skladRoot");
if ($_GET['SITE_ID']) {
    foreach ($results as $result) {
        if ($displayCats[$result['Liczo_']]) $hier[$displayCats[$result['Liczo_']]] = $result;
    }
    ksort($hier);
} else {
    foreach ($results as $result) {
        if ($displayCats[$result['Liczo_']]) $hier[$i++] = $result;
    }
}
$xml = "<categories>";
foreach($hier as $i => $cat) {
    $xml .= '<category>';
    $xml .= '<PorNomer>'.($displayCats[$cat['Liczo_']]*10).'</PorNomer> ';
    $xml .= '<price_id>'.$i.'</price_id>';
    $xml .= '<ID>'.$cat['ROWID'].'</ID>';
    $xml .= '<sID>'.$cat['sROWID'].'</sID>';
    $xml .= '<name>'.trim(iconv("windows-1251","utf-8",$cat['Name'])).'</name>';
    get_cats($cat['sROWID'], $cat['Liczo_']);
    $xml .= '</category>';
}
$xml .= "</categories>";
//print htmlspecialchars($xml);
print ($xml);
$db->close();