<?php
//die("Пока что нельзя");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_GET['start'] != "gogo") die("Идут работы");
CModule::IncludeModule("iblock");
set_time_limit(0);
$startTime = date("d.m.Y H:i:s");
global $iblock, $productSections;
$iblock = 1;
$host = "http://strprofi.ru/";
$url = $host."import/export/";
$productSections = Array();

$log = $_GET['log']=="Y"?true:false;

function write2log($str) {
	global $startTime;
	$fp = fopen("logs/log-".$startTime.".txt", "a+");
	fputs($fp, $str."
");
	fclose($fp);
}

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
	global $iblock, $url, $host, $log;
	$sc = new CIBlockSection;

	$new_name = preg_replace("/^([0-9]{1,2}\. ?[0-9]?\.? ?[0-9]* ?)/", "", trim($name));
	$code = rus2lat($new_name);

	//$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_INTERNAL_ID' => $internal, "SECTION_ID" => $parent);
	$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_ROWID' => $internal, "SECTION_ID" => $parent);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_*"));
	if ($ar_result = $db_list->GetNext())
	{
		$ID = $ar_result["ID"];
		$arUpdate = Array(
			"NAME" => $new_name,
			"UF_ORIGINAL_NAME" => $name,
			"UF_PHOTO_ID" => $photo,
			"UF_PRICE_ID" => $price_id,
			"DESCRIPTION" => $descr,
			"SORT" => $sort,
			"CODE" => $code,
			//   "UF_TEMPLATE"=>0
		);

        $arUpdate["PICTURE"] = false;
        $arUpdate["UF_TEMPLATE"] = 0;
        if ($photo) {
            $pic = file_get_contents($url . "photos.php?id=" . $photo);
            if ($pic!="-") {
                $fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
                fwrite($fp, $pic);
                $pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
                if ($pic["size"] > 0) {
                    $arUpdate["PICTURE"] = $pic;
                    $arUpdate["UF_TEMPLATE"] = 1;
                }
            }
        }

        /*
        if (($ar_result["UF_PHOTO_ID"] != $photo) && (!$ar_result['PICTURE'])) {
			$arUpdate["UF_PHOTO_ID"] = $photo;
			if ($photo) {
				$pic = file_get_contents($url . "photos.php?id=" . $photo);
				if ($pic!="-") {
					$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
					fwrite($fp, $pic);
					$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
					if ($pic["size"] > 0) {
						$arUpdate["PICTURE"] = $pic;
						$arUpdate["UF_TEMPLATE"] = 1;
					}
				}
			} else {
				$arUpdate["PICTURE"] = false;
				$arUpdate["UF_TEMPLATE"] = 0;
			}
		} else {
			if ($ar_result["UF_PHOTO_ID"]) {
				$arUpdate["UF_TEMPLATE"] = 1;
			} else {
				$arUpdate["UF_TEMPLATE"] = 0;
			}
		}
        */
		$sc->Update($ID, $arUpdate);
		if ($log) {write2log("Обновили раздел: ".print_r($arUpdate,true));}
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
			"UF_ROWID" => $internal,
			"UF_TEMPLATE" => 0
		);
		$pic = file_get_contents($url . "photos.php?id=" . $photo);
		if ($pic!="-") {
			$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
			fwrite($fp, $pic);
			$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
			if ($pic["size"] > 0) {
				$arAdd["PICTURE"] = $pic;
				$arAdd["UF_TEMPLATE"] = 1;
			}
		}
		$ID = $sc->Add($arAdd);
		if ($log) {write2log("Новый раздел $ID : ".print_r($arAdd, true));}
	}
	return $ID;
}

function removeSection($category, $presentedCats)
{
	global $log;
	if ($log) {write2log("<hr/>Проверяем не удаление #".$category.". Остануться: ".print_r($presentedCats, true));}
	global $iblock;
	$sc = new CIBlockSection;
	$arFilter = Array("IBLOCK_ID" => $iblock, "!ID" => $presentedCats, "SECTION_ID" => $category);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
	while ($ar_result = $db_list->GetNext()) {
		if ($log) {write2log("Удаляем раздел: ".print_r($ar_result, true));}
		$sc->Delete($ar_result["ID"]);
	}
	write2log("");
}

//print $url . "cats2.php";die();
$xml = simplexml_load_file($url . "cats2.php");
//$xml = simplexml_load_file("./export/cats2.xml");

function sectionWalker($xml, $top)
{
	global $productSections;
	$present = Array();
	foreach ($xml->category as $topContent) {
		if (sizeof($topContent->childs->category) > 0) {
			$ID = getSectionID(strval($topContent->name), intval($topContent->ID), $top, intval($topContent->image), intval($topContent->PorNomer), intval($topContent->price_id), strval($topContent->desc));
			$present[] = $ID;
			sectionWalker($topContent->childs, $ID);
		} else {
			$ID = getSectionID(strval($topContent->name), intval($topContent->ID), $top, intval($topContent->image), intval($topContent->PorNomer), intval($topContent->price_id), strval($topContent->desc), true);
			$present[] = $ID;
			removeSection($ID, Array());
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
	$arFilter = Array("IBLOCK_ID" => $iblock, "PROPERTY_ROWID" => intval($item->ID), "SECTION_ID" => $siteCatID);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	if ($ob = $res->GetNext())
	{
		$propsToUpdate = Array(
			"ARTICUL" => $item->Artikul,
			"PRICE" => (float)str_replace(",", ".", $item->CZena1),
			"PRICE_OPT" => (float)str_replace(",", ".", $item->CZena2),
			"PRICE_OPT2" => (float)str_replace(",", ".", $item->CZena3),
			"UNITS" => $item->EdIzmereniya,
			"NOMNOMER" => trim(strval($item->NomNomer)),
			"UPAKOVKA" => $item->VUpakovke,
			"UPAKOVKA2" => $item->VUpakovke2,
			"VES" => $item->Ves,
			"OSTATOK" => $item->Ostatok,
			"NAIMENOVANIE" => $item->Naimenovanie,
			"NomenklaturaGeog" => $item->NomenklaturaGeog,
			"V_REZERVE" => $item->VRezerve
		);
		$arUpdate = Array(
			"NAME" => trim(strval($item->Svertka)),
			"PREVIEW_TEXT" => trim(strval($item->desc)),
			"SORT" => intval($item->PorNomer)
		);
		if ($ob["PROPERTY_PHOTO_ID_VALUE"] != $item->Foto) {
			$propsToUpdate["PHOTO_ID"] = $item->Foto;
			$pic = file_get_contents($url . "photos.php?id=" . $item->Foto);
			if ($pic!="-") {
				$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
				fwrite($fp, $pic);
				$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');
				if ($pic["size"] > 0) {
					$arUpdate['PREVIEW_PICTURE'] = $pic;
				}
			}
		}
		$el->Update($ob["ID"], $arUpdate);
		CIBlockElement::SetPropertyValuesEx($ob["ID"], $iblock, $propsToUpdate);
		$ID = $ob["ID"];
	}
	else
	{
		$arLoad = Array(
			"IBLOCK_ID" => $iblock,
			"IBLOCK_SECTION_ID" => $siteCatID,
			"NAME" => trim(strval($item->Svertka)),
			"PREVIEW_TEXT" => trim(strval($item->desc)),
			"SORT" => intval($item->PorNomer),
			"PROPERTY_VALUES" => array(
				"ARTICUL" => $item->Artikul,
				"ROWID" => $item->ID,
				"PRICE" => (float)str_replace(",", ".", $item->CZena1),
				"PRICE_OPT" => (float)str_replace(",", ".", $item->CZena2),
				"PRICE_OPT2" => (float)str_replace(",", ".", $item->CZena3),
				"NAIMENOVANIE" => trim(strval($item->Naimenovanie)),
				"PHOTO_ID" => $item->Foto,
				"VES" => $item->Ves,
				"NOMNOMER" => trim(strval($item->NomNomer)),
				"UNITS" => $item->EdIzmereniya,
				"UPAKOVKA" => $item->VUpakovke,
				"NomenklaturaGeog" => $item->NomenklaturaGeog,
				"UPAKOVKA2" => $item->VUpakovke2,
				"OSTATOK" => $item->Ostatok,
				"V_REZERVE" => $item->VRezerve
			),
		);
		$pic = file_get_contents($url . "photos.php?id=" . $item->Foto);
		if ($pic != "-") {
			$fp = fopen($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/temp_x.jpg', 'w');
			fwrite($fp, $pic);
			$pic = CFile::MakeFileArray($host.'upload/tmp/temp_x.jpg');

			if ($pic["size"] > 0) {
				$arLoad["PREVIEW_PICTURE"] = $pic;
			}
		}
		$ID = $el->Add($arLoad);
	}
	return $ID;
}


function parseSection($strCatID, $siteCatID, $price_id, $depth)
{
	global $iblock, $url, $log;
	$xml = simplexml_load_file($url . "items2.php?cat=" . $strCatID);
	$present = Array();
	foreach ($xml->item as $item) {
		$ID = addUpdateElement($item, $siteCatID);
		$present[] = $ID;
	}
	$el = new CIBlockElement;
	$arFilter = Array("IBLOCK_ID" => $iblock, "SECTION_ID" => $siteCatID, "!ID" => $present);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID"));
	while ($ob = $res->GetNext()) {
		if ($log) {write2log("Будет удален элемент: ".print_r($ob, true));}
		$el->Delete($ob["ID"]);
	}
}

if ($log) {
	write2log("старт");
}
if (sizeof($xml->category))
{
	sectionWalker($xml, 0);
	if ($log) {
		write2log("категории для парса:<br/>".print_r($productSections, true));
	}
	$arFilter = Array('IBLOCK_ID' => $iblock, 'ID' => array_keys($productSections));
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_PRICE_ID"));
	while ($ar_result = $db_list->GetNext()) {
		if ($log) {
			write2log("Парсим ROWID: #".$productSections[$ar_result["ID"]].", ID: ".$ar_result["ID"]);
		}
		parseSection($productSections[$ar_result["ID"]], $ar_result["ID"], (int)$ar_result["UF_PRICE_ID"], $ar_result["DEPTH_LEVEL"]);
	}
//	getUrl('http://' . $_SERVER["SERVER_NAME"] . '/import/find_pics.php');
	$finishTime = date("d.m.Y H:i:s");
//	pismo("spleaner.ru@gmail.com", "Финиш парса", $startTime." – ".$finishTime."<br/>".print_r($_SERVER, true));
}

if ($log) {write2log("Импорт завершен ".date("d.m.Y H:i:s"));}