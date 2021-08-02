<?php
header('Content-Type: text/xml');
print '<?xml version="1.0" encoding="utf-8"?> ';
require 'config.php';
require 'db.php';
require 'prices.php';
require 'foto.php';

set_time_limit(3600);

$db = new DB();
$image_name = '+no-image';
if ($db->connect() )
{
    $Razdel = (int)$_GET['cat'];
    if (!$Razdel) die("Не задан раздел");

	if ($_GET['SITE_ID']) {
        $results = $db->fetch_array("SELECT * FROM PARAMHST WHERE ParametrIstoriya=".$_GET['SITE_ID']);
        global $displayItems;
        $displayItems = Array(
            "0" => 1
        );
        foreach($results as $result) {
            if ((int)$result['Znachenie'] == 0) $displayItems[$result['LiczoIstoriya']] = (int)$result['Znachenie'];
        }
        asort($displayItems);

        $NoDisplayItems = array_keys($displayItems);

        $results = $db->fetch_array("SELECT ROWID FROM ITEMS WHERE Liczo_ IN (".implode(", ", $NoDisplayItems).")");
        $NoDisplayItems = Array();
        foreach ($results as $result) {
            $NoDisplayItems[] = $result['ROWID'];
        }
    }
    $catOnSklad = $db->fetch_array("SELECT ROWID FROM SKLAD WHERE NomerSklada=$skladID AND NomenklaturaSkla=$Razdel AND Razdel_f=0");
    $catOnSklad = $catOnSklad[0]['ROWID'];
    $itemsOnSklad = $db->fetch_array("SELECT NomenklaturaSkla, ROWID FROM SKLAD WHERE NomerSklada=$skladID AND Razdel=$catOnSklad AND Razdel_f=255");
    $toSelect = Array();
    foreach($itemsOnSklad as $itemOnSklad) {
        if (!in_array($itemOnSklad['NomenklaturaSkla'], $NoDisplayItems))
        {
            $toSelect[$itemOnSklad['NomenklaturaSkla']] = $itemOnSklad['ROWID'];
        }
    }
//    $q = "SELECT ROWID FROM ITEMS WHERE NomenklaturaGeog = 3";
	$q = "SELECT NomenklaturaSkla FROM SKLAD WHERE NomerSklada = $skladID AND TipZapasa = 0";
    $result = $db->fetch_array($q);
//    print_r($result);die();
    $NomenklaturaGeog = Array();
    foreach($result as $item) {
        $NomenklaturaGeog[] = $item['NomenklaturaSkla'];
    }
    $q = "SELECT Ves, NomNomer, ROWID, KratnostOtpuska, KodUpakovki, KodOKEI, Artikul, Svertka, Naimenovanie, Foto, CZena1, CZena2, CZena3, EdIzmereniya, Opisanie FROM ITEMS WHERE ROWID IN (".implode(",", array_keys($toSelect)).")";// ORDER BY Artikul";
    $result = $db->fetch_array($q);
    $IDs = Array();
    $descIDs = Array();
    foreach($result as $item) {
        $IDs[] = $item['ROWID'];
        $descIDs[] = $item['Opisanie'];
    }
    $q = "SELECT NomenklaturaSkla FROM SKLAD WHERE NomerSklada = 9 AND NomenklaturaSkla IN (".implode(",", $IDs).")";
    $resultOnSklad = $db->fetch_array($q);
    $onSklad = Array();
    foreach ($resultOnSklad as $r) {$onSklad[] = $r['NomenklaturaSkla'];}

    //Получим все упаковки
    $q = "SELECT NomenklaturaUpak, Kod, VUpakovke, VesUpakovki, SHtrihKod, VidUpakovki, ROWID, VARSTRING FROM PACKAGES WHERE NomenklaturaUpak IN (".implode(", ", $IDs).") ORDER BY Kod";
    $packages = $db->fetch_array($q);
    $package = Array();
    foreach($packages as $p) {
        $package[$p['NomenklaturaUpak']][] = $p;
    }
    //Полчим все остатки
    $q = "SELECT NomenklaturaSkla, Ostatok, VRezerve from SKLAD WHERE NomenklaturaSkla IN (".implode(", ", $IDs).")";
    $sklads = $db->fetch_array($q);
    $ostatok = Array();
    foreach($sklads as $sklad) {
        $ostatok[$sklad['NomenklaturaSkla']] += (float)$sklad['Ostatok'];
        $ostatok[$sklad['NomenklaturaSkla']] -= (float)round($sklad['VRezerve'], 3);
    }
    //Полчим всем описания
    $query = $db->query("SELECT ROWID, data FROM ITEMS_ WHERE ROWID IN (".implode(", ", $descIDs).")");
    $descs = Array();
    while (($rec = $db->fetch_assoc( $query ))) {
        $descs[$rec['ROWID']] = $rec['data'];
    }
    ?><items><?
	foreach($result as $item) {
        if (!in_array($item['ROWID'], $onSklad)) continue;
        if (!$item['Artikul']) continue;
        //if (!$item['KodOKEI'] && !$item['KodUpakovki']) continue;

        $item['Svertka'] = $item['Svertka']?$item['Svertka']:"-";
        $item['Naimenovanie'] = $item['Naimenovanie']?$item['Naimenovanie']:$item['Svertka'];
        $item['NomenklaturaGeog'] = in_array($item['ROWID'], $NomenklaturaGeog)?3:0;

        $toUtf8 = array("Svertka", "Naimenovanie", "EdIzmereniya");
        foreach ($toUtf8 as $name) {
	        $item[$name] = str_replace("
","",$item[$name]);
	        $item[$name] = str_replace("
 ","",$item[$name]);
	        $item[$name] = str_replace(" ","",$item[$name]);
	        $item[$name] = trim($item[$name]);
	        $item[$name] = htmlspecialchars($item[$name]);
            $item[$name] = iconv("windows-1251","utf-8",$item[$name]);
        }
        $n = 1;
        ?><item><?
        ?><ID><?=$item['ROWID'];?></ID><?
        ?><NomNomer><?=$item['NomNomer'];?></NomNomer><?
        ?><NomenklaturaGeog><?=$item['NomenklaturaGeog'];?></NomenklaturaGeog><?
        ?><KodOKEI><?=$item['KodOKEI'];?></KodOKEI><?
        ?><Ves><?=$item['Ves'];?></Ves><?
        ?><VUpakovke><?=$package[$item['ROWID']][0]['VUpakovke'];?></VUpakovke><?
        ?><VUpakovke2><?=$package[$item['ROWID']][1]['VUpakovke'];?></VUpakovke2><?
        ?><Ostatok><?=$ostatok[$item['ROWID']];?></Ostatok><?
        ?><VRezerve>0</VRezerve><?
        ?><KodUpakovki><?=$item['KodUpakovki'];?></KodUpakovki><?
        ?><KratnostOtpuska><?=$item['KratnostOtpuska'];?></KratnostOtpuska><?
        ?><PorNomer><?=(10 * $n++);?></PorNomer><?
        ?><Artikul><?=$item['Artikul'];?></Artikul><?
	    ?><Svertka><?=$item['Svertka'];?></Svertka><?
	    ?><Naimenovanie><?=$item['Naimenovanie'];?></Naimenovanie><?
        ?><Foto><?=$item['Foto'];?></Foto><?
        ?><CZena1><?=$item['CZena1'];?></CZena1><?
        ?><CZena2><?=$item['CZena2'];?></CZena2><?
        ?><CZena3><?=$item['CZena3'];?></CZena3><?
        ?><EdIzmereniya><?=$item['EdIzmereniya'];?></EdIzmereniya><?
        if ($item['Opisanie']) {
            ?><Opisanie><?=$item['Opisanie'];?></Opisanie><?
            if ($descs[$item['Opisanie']]) {
                $desc = $descs[$item['Opisanie']];
                //$desc = utf8_encode($desc);
                //*
                $desc = str_replace("
","\n",$desc);
                $desc = str_replace("
 ","",$desc);
                $desc = str_replace(" ","",$desc);
                $desc = trim($desc);
                $desc = iconv("windows-1251","utf-8",$desc);
                //*/
                //$desc = utf8_decode($desc);
                //$desc = preg_replace('/[^(\w\s\d)]*/','', $desc);
	            $desc = htmlspecialchars($desc);
                if ($desc) print '<desc>'.htmlspecialchars($desc).'</desc> ';
            }
        }
        ?></item><?
    }
    ?></items><?
}
