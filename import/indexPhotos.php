<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");
set_time_limit(0);
$startTime = date("d.m.Y H:i:s");
$host = "http://strprofi.ru/";
$url = $host."import/export/";

$updateSections = true;
$updateElements = true;

if ($updateSections)
{
	$arFilter = Array('IBLOCK_ID' => 1, "!UF_PHOTO_ID"=>false);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_PHOTO_ID"));
	while ($ar_result = $db_list->GetNext()) {
		if($ar_result['UF_PHOTO_ID']) {
			$pic = file_get_contents($url . "photos.php?id=" . $ar_result['UF_PHOTO_ID']);
			if ($pic != "-") {
				if (strlen(trim($pic))) {
					$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
					fwrite($fp, $pic);
					fclose($fp);
					$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
				} else {
					$pic = Array();
					$pic["size"] = 0;
				}
				$arUpdate = Array();
				if ($pic["size"] > 0) {
					$arUpdate["PICTURE"] = $pic;
					$arUpdate["UF_TEMPLATE"] = 1;
				} else {
					$arUpdate["PICTURE"] = false;
					$arUpdate["UF_TEMPLATE"] = 0;
				}
				$bs = new CIBlockSection;
				$bs->Update($ar_result["ID"], $arUpdate);
			}
		}
	}
}

if ($updateElements) {
	$arSelect = Array("ID", "NAME", "PROPERTY_PHOTO_ID");
	$arFilter = Array("IBLOCK_ID" => "1", "ACTIVE" => "Y", "!PROPERTY_PHOTO_ID" => false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while ($ob = $res->GetNext()) {
		if ($ob['PROPERTY_PHOTO_ID_VALUE']) {
			$arUpdate = Array();
			$pic = file_get_contents($url . "photos.php?id=" . $ob['PROPERTY_PHOTO_ID_VALUE']);
			if ($pic != "-") {
				if (strlen(trim($pic))) {
					$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
					fwrite($fp, $pic);
					fclose($fp);
					$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
				} else {
					$pic = Array();
					$pic["size"] = 0;
				}
				if ($pic["size"] > 0) {
					$arUpdate['PREVIEW_PICTURE'] = $pic;
				} else {
					$arUpdate['PREVIEW_PICTURE'] = false;
				}
				$el = new CIBlockElement;
				$el->Update($ob["ID"], $arUpdate);
			}
		}
	}
}
$finishTime = date("d.m.Y H:i:s");
//pismo("spleaner.ru@gmail.com", "Финиш парса", $startTime." – ".$finishTime."<br/>".print_r($_SERVER, true));
