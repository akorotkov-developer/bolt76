<?php
die("Нет, не работает");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_GET['start'] != "gogo") die("Идут работы");
CModule::IncludeModule("iblock");
set_time_limit(0);
$startTime = date("d.m.Y H:i:s");
global $iblock, $productSections;
$iblock = 1;
//$url = "http://strprofi.ru/export/";
//$url = "http://stroyprofi.prominado.ru/import/export/";
$host = "http://strprofi.ru/";
$url = $host."import/export/";
$productSections = Array();
function  rus2lat($text)
{
	$tr = array(
		"Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I",
		"і" => "i", "ґ" => "g", "ё" => "yo", "№" => "#", "є" => "e",
		"ї" => "yi", "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
		"Д" => "D", "Е" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I",
		"Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
		"О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
		"У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
		"Ш" => "SH", "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "",
		"Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
		"в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "zh",
		"з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
		"м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
		"с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
		"ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "",
		"ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "  " => " ", " - " => "_", "- " => "_", " -" => "_", "." => "_", "," => "_", " " => "_", "-" => "_", " " => "_"
	);
	$text = preg_replace("/[^- _a-zA-Z0-9абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ]+/i", '', trim($text));
	$text = strtr($text, $tr);
	$text = str_replace(" ", '_', $text);
	$text = str_replace("__", '_', $text);
	return substr(mb_strtolower($text), 0, 50);
}

function getSectionID($name, $internal, $parent, $photo, $sort, $price_id, $descr, $isItem = false)
{
	global $iblock, $url, $host;
	$sc = new CIBlockSection;

	$new_name = preg_replace("/^([0-9]{1,2}\. ?[0-9]{1,2}\.? ?[0-9]* ?)/", "", $name);
	$code = rus2lat($new_name);

	$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_INTERNAL_ID' => $internal, "SECTION_ID" => $parent);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_*"));
	if ($ar_result = $db_list->GetNext())
	{
		$ID = $ar_result["ID"];
		$arUpdate = Array(
			"NAME" => $new_name,
			"UF_ORIGINAL_NAME" => $name,
			"UF_PRICE_ID" => $price_id,
			"DESCRIPTION" => $descr,
			"SORT" => $sort,
			"CODE" => $code,
			//   "UF_TEMPLATE"=>0
		);
		if (($ar_result["UF_PHOTO_ID"] != $photo)/* && (!$ar_result['PICTURE'])*/) {
			$arUpdate["UF_PHOTO_ID"] = $photo;

			$pic = file_get_contents($url . "photos.php?id=" . $photo);
			$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
			fwrite($fp, $pic);
			$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
			if ($pic["size"] > 0) {
				$arUpdate["PICTURE"] = $pic;
				$arUpdate["UF_TEMPLATE"] = 1;
			}


		}
		$sc->Update($ID, $arUpdate);
//        die();
	}
	else
	{
		$arAdd = Array(
			"IBLOCK_ID" => $iblock,
			"IBLOCK_SECTION_ID" => $parent,
			"SORT" => $sort,
			"NAME" => $new_name,
			"UF_ORIGINAL_NAME" => $name,
			"CODE" => $code,
			"DESCRIPTION" => $descr,
			"UF_PHOTO_ID" => $photo,
			"UF_PRODUCT" => ($isItem ? '1' : '0'),
			"UF_PRICE_ID" => $price_id,
			"UF_INTERNAL_ID" => $internal,
			"UF_TEMPLATE" => 0
		);

		$pic = file_get_contents($url . "photos.php?id=" . $photo);
		$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
		fwrite($fp, $pic);
		$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
		if ($pic["size"] > 0) {
			$arAdd["PICTURE"] = $pic;
			$arAdd["UF_TEMPLATE"] = 1;
		}
		$ID = $sc->Add($arAdd);
	}
	return $ID;
}

function removeSection($category, $presentedCats)
{
	global $iblock;
	$sc = new CIBlockSection;
	$arFilter = Array("IBLOCK_ID" => $iblock, "!ID" => $presentedCats, "SECTION_ID" => $category);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
	while ($ar_result = $db_list->GetNext()) {
		//$sc->Update($ar_result["ID"], Array("ACTIVE"=>"N"));
		$sc->Delete($ar_result["ID"]);
	}
}

$xml = simplexml_load_file($url . "cats.php");
function sectionWalker($xml, $top)
{
	global $productSections;
	$present = Array();
	foreach ($xml->category as $topContent) {
		if (sizeof($topContent->childs->category) > 0) {
			$ID = getSectionID($topContent->name, $topContent->ID, $top, $topContent->image, $topContent->PorNomer, $topContent->price_id, $topContent->desc);
			$present[] = $ID;
			sectionWalker($topContent->childs, $ID);
		} else {
			$ID = getSectionID($topContent->name, $topContent->ID, $top, $topContent->image, $topContent->PorNomer, $topContent->price_id, $topContent->desc, true);
			$present[] = $ID;
		}
		$productSections[$ID] = (int)$topContent->ID;
	}
	removeSection($top, $present);
}

function addUpdateElement($item, $siteCatID)
{
	global $iblock, $url, $host;
	$el = new CIBlockElement;
	$arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_PHOTO_ID");
	$arFilter = Array("IBLOCK_ID" => $iblock, "PROPERTY_ROW_ID" => $item->ID, "SECTION_ID" => $siteCatID);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	if ($ob = $res->GetNext())
	{
		$propsToUpdate = Array(
			"PRICE" => $item->CZena1,
			"PRICE_OPT" => $item->CZena2,
			"PRICE_OPT2" => $item->CZena3,
			"UNITS" => $item->EdIzmereniya,
			"UPAKOVKA" => $item->VUpakovke,
			"UPAKOVKA2" => $item->VUpakovke2,
			"VES" => $item->Ves,
			"OSTATOK" => $item->Ostatok,
			"NAIMENOVANIE" => $item->Naimenovanie,
			"V_REZERVE" => $item->VRezerve
		);
		$arUpdate = Array("PREVIEW_TEXT" => $item->desc, "SORT" => $item->PorNomer);
		if ($ob["PROPERTY_PHOTO_ID_VALUE"] != $item->Foto) {
			$propsToUpdate["PHOTO_ID"] = $item->Foto;
			$pic = file_get_contents($url . "photos.php?id=" . $item->Foto);
			$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
			fwrite($fp, $pic);
			$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
			if ($pic["size"] > 0) {
				$arUpdate['PREVIEW_PICTURE'] = $pic;
			}
		}
		$el->Update($ob["ID"], $arUpdate);
		CIBlockElement::SetPropertyValuesEx($ob["ID"], $iblock, $propsToUpdate);
		$ID = $ob["ID"];
	}
	else
	{
		$pic = file_get_contents($url . "photos.php?id=" . $item->Foto);
		$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
		fwrite($fp, $pic);
		$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
		$arLoad = Array(
			"IBLOCK_ID" => $iblock,
			"IBLOCK_SECTION_ID" => $siteCatID,
			"NAME" => $item->Svertka,
			"PREVIEW_TEXT" => $item->desc,
			"SORT" => $item->PorNomer,
			"PROPERTY_VALUES" => array(
				"ARTICUL" => $item->Artikul,
				"ROW_ID" => $item->ID,
				"PRICE" => (float)str_replace(",", ".", $item->CZena1),
				"PRICE_OPT" => (float)str_replace(",", ".", $item->CZena2),
				"PRICE_OPT2" => (float)str_replace(",", ".", $item->CZena3),
				"NAIMENOVANIE" => $item->Naimenovanie,
				"PHOTO_ID" => $item->Foto,
				"UNITS" => $item->EdIzmereniya,
				"VES" => $item->Ves,
				"UPAKOVKA" => $item->VUpakovke,
				"UPAKOVKA2" => $item->VUpakovke2,
				"OSTATOK" => $item->Ostatok,
				"V_REZERVE" => $item->VRezerve
			),
		);
		if ($pic["size"] > 0) {
			$arLoad["PREVIEW_PICTURE"] = $pic;
		}
		$ID = $el->Add($arLoad);
	}
	return $ID;
}


function parseSection($strCatID, $siteCatID, $price_id, $depth)
{
	global $iblock, $url;
	$xml = simplexml_load_file($url . "items.php?" . ($depth == 1 ? "price_id=" . $price_id : "cat=" . $strCatID));
	$present = Array();
	$minPrice = 1000000;
	foreach ($xml->item as $item) {
		$ID = addUpdateElement($item, $siteCatID);
		$present[] = $ID;
		/*должны посмотреть обе цены выводимые на сайте, иногда может не быть одной или двух цен сразу*/
		$price_roz = (float)str_replace(",", ".", $item->CZena1);
		$price_opt = (float)str_replace(",", ".", $item->CZena2);
		if ($price_opt > 0 || $price_roz > 0) {
			if ($price_opt > 0 && $price_roz > 0) {
				$price_m = ($price_opt > $price_roz ? $price_roz : $price_opt);
				if ($minPrice > $price_m) {
					$minPrice = $price_m;
				}
			} elseif ($price_opt > 0) {
				if ($minPrice > $price_opt) {
					$minPrice = $price_opt;
				}
			} elseif ($price_roz > 0) {
				if ($minPrice > $price_roz) {
					$minPrice = $price_roz;
				}
			}
		}
	}
	if ($minPrice < 1000000) {
		$bs = new CIBlockSection;
		$bs->Update($siteCatID, Array("UF_PRICE_FROM" => $minPrice));
	}

	$el = new CIBlockElement;
	$arFilter = Array("IBLOCK_ID" => $iblock, "SECTION_ID" => $siteCatID, "!ID" => $present);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID"));
	while ($ob = $res->GetNext()) {
		$el->Delete($ob["ID"]);
	}
}

if (sizeof($xml->category)) {
//	sectionWalker($xml, 0);
	$arFilter = Array('IBLOCK_ID' => $iblock, 'ID' => 4150/*array_keys($productSections)*/);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_PRICE_ID"));
	while ($ar_result = $db_list->GetNext()) {
		parseSection($productSections[$ar_result["ID"]], $ar_result["ID"], (int)$ar_result["UF_PRICE_ID"], $ar_result["DEPTH_LEVEL"]);
	}
//	getUrl('http://' . $_SERVER["SERVER_NAME"] . '/import/find_pics.php');
	$finishTime = date("d.m.Y H:i:s");
	pismo("spleaner.ru@gmail.com", "Финиш парса", $startTime." – ".$finishTime."<br/>".print_r($_SERVER, true));
}

print "Импорт завершен ".date("d.m.Y H:i:s").".";
