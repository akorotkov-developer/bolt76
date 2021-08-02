<?php
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?>';

require 'prices.php';
require 'foto.php';


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