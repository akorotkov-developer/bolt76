<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");


$arFilter = Array('IBLOCK_ID' => 1, "UF_PRODUCT"=>false);
$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
while ($ar_result = $db_list->GetNext()) {
	if ($ar_result['PICTURE']) continue;
    echo $ar_result["NAME"].'<br>';
    $pic = findFirstPic($ar_result["ID"]);
	if (!$pic) continue;
    echo "<img src='".$pic."' width='120'>";
    if($pic){
        $pic = CFile::MakeFileArray('http://'.$_SERVER["SERVER_NAME"].$pic);
        $bs = new CIBlockSection;
        $bs->Update($ar_result["ID"], Array("PICTURE"=>$pic));
    }
}



function findFirstPic($sectionID){
    $picture = false;
    $arFilter = Array('IBLOCK_ID' => 1, 'SECTION_ID' => $sectionID);
    $db_list = CIBlockSection::GetList(Array("left_margin"=>"asc"), $arFilter, false);
    while (($ar_result = $db_list->GetNext()) && (!$picture)) {
	    if (!$ar_result['PICTURE']) continue;
	    print_r($ar_result);
	    print "<hr/>";
        $picture  = CFile::GetPath($ar_result["PICTURE"]);
    }
    return $picture;
}

