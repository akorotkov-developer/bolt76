<?php
//die("Пока что нельзя");



set_time_limit();
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if ($_GET['start'] != "gogo") die("Идут работы");
CModule::IncludeModule("iblock");
set_time_limit();
$startTime = date("d.m.Y H:i:s");
global $iblock, $productSections;
$iblock = 1;
global $host;

/**Новый метод определения хоста*/
if ($_SERVER['SERVER_PORT'] == '443') {
    $sHttp = 'https://';
} else {
    $sHttp = 'http://';
}
$host = $sHttp . $_SERVER['SERVER_NAME'] . '/';
/************************************/

$url = $host."import/export/";
$productSections = Array();
$tempArray = Array();
$badLines = Array();

$log = $_GET['log']=="Y"?true:false;
$log = true;

$good = 0;
$bad = 0;

$upload_pix = true;
#$filename = '/tmp/bdimport.log';

/*
+++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++
++++ START OF FUNCTIONS AREA ++++
+++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++
*/




function e($string){
//функиця для записи в файл строки $string а также экстренного останова (вызывается достаточно часто, чтоб обрабатывать триггер прерывания обработки, 
//только если не зависло на какой-то внещней операции 
	if (file_exists("/tmp/break.php"))
	{
			echo 'breakpoint exists at /tmp/break.php. remove file then start again';
			die;
	}
	file_put_contents("/tmp/file.log",$string."\n",FILE_APPEND);
	echo $string.'<br>';
}

function getDesc($string){	
	return str_replace('<br>',"\n",str_replace('<br><br>',"\n",$string));
}

function getLine($string){
	$order   = array("\r\n", "\n", "\r");
	$replace = '';
	return str_replace($order,$replace,$string);
}


file_put_contents("/tmp/file.log","");



e("######################################");
e("######################################");
e("######################################");
e("######################################");
e("######################################");
e("d sbis 2 bitrix import ");
e("version 302");
e("Импорт начат ".date("d.m.Y H:i:s"));


//function loaditems()
//НЕ функция для загрузки файлов из csv в память
{
	e('loading items from file to memory');
	$lines = file('catalog.txt');
	$items = [];
	$i = 0;

	if($lines===FALSE)
	{
		die('!! file open error !!');
	}
	else
	{
	    $iTempLineCount = 0;
		foreach($lines as $line)
		{
				$line = iconv("windows-1251","utf-8",$line);
				$a = explode(';',$line);
				//старый бесполезный механизм для 21 столбца
				if(false) //-выключенф
				if(count($a)>=21)
				{
					echo ('total size: '.count($a));
									
					$items[$a[2]][$i]['ID'] = $a[1];
					$items[$a[2]][$i]['NomNomer'] = $a[6];
					$items[$a[2]][$i]['NomenklaturaGeog'] = '';
					$items[$a[2]][$i]['KodOKEI'] = '';
					$items[$a[2]][$i]['Ves'] = $a[8];
					$items[$a[2]][$i]['VUpakovke'] = $a[12];
					$items[$a[2]][$i]['VUpakovke2'] = $a[13];
					$items[$a[2]][$i]['Ostatok'] = $a[14];
					$items[$a[2]][$i]['VRezerve'] = $a[15];
					$items[$a[2]][$i]['KodUpakovki'] = '';
					$items[$a[2]][$i]['KratnostOtpuska'] = '';
					$items[$a[2]][$i]['PorNomer'] = $i;
					$items[$a[2]][$i]['Artikul'] = $a[5];
					$items[$a[2]][$i]['Svertka'] = $a[4];
					$items[$a[2]][$i]['Naimenovanie'] = $a[3];
					$items[$a[2]][$i]['Foto'] = $a[18]; #$host.'/import/img/'.$a[18].'.jpg';
					$items[$a[2]][$i]['CZena1'] = $a[9];
					$items[$a[2]][$i]['CZena2'] = $a[10];
					$items[$a[2]][$i]['CZena3'] = $a[11];
					$items[$a[2]][$i]['EdIzmereniya'] = $a[7];
					$items[$a[2]][$i]['desc'] = str_replace('<br>',"\n",str_replace('<br><br>',"\n",$a[19]));

					//Если забыли заполнить свёртку - пусть там будет наименование
					if($items[$a[2]][$i]['Svertka']=='') $items[$a[2]][$i]['Svertka'] = trim(strval($items[$a[2]][$i]['Naimenovanie']));
					
					//echo '<pre>';
					//var_dump($items[$a[2]][$i]);
					//echo '</pre>';			
					

				}

            $iTempLineCount++;

				//новый механизм
				if(count($a)==3)
				{	
					//некоторая фильтрация
					if ($a[1]!=19) {
                        $tempArray[$a[0]][$a[1]] = getLine($a[2]);
                    } else {
                        $tempArray[$a[0]][$a[1]] = getDesc($a[2]);
                    }
					$good++;
				}
				else 
				{
				    $acount = 0;
				    foreach($a as $akey => $arRes) {
				        if ($acount > 2){
				            unset($a[$akey]);
                        }

                        $acount ++;
                    }

                    //некоторая фильтрация
                    if ($a[1]!=19) {
                        $tempArray[$a[0]][$a[1]] = getLine($a[2]);
                    } else {
                        $tempArray[$a[0]][$a[1]] = getDesc($a[2]);
                    }
                    $good++;

					$badLines[$i] = $line;
					$bad++;
				}
				$i++;
		}
	}
}

//приведение массивов
foreach($tempArray as $i=>$a)
{
	$items[$a[2]][$i]['ID'] = $a[1];
	$items[$a[2]][$i]['NomNomer'] = $a[6];
	$items[$a[2]][$i]['NomenklaturaGeog'] = '';
	$items[$a[2]][$i]['KodOKEI'] = '';
	$items[$a[2]][$i]['Ves'] = $a[8];
	$items[$a[2]][$i]['VUpakovke'] = $a[12];
	$items[$a[2]][$i]['VUpakovke2'] = $a[13];
	$items[$a[2]][$i]['Ostatok'] = $a[14];
	$items[$a[2]][$i]['VRezerve'] = $a[15];
	$items[$a[2]][$i]['KodUpakovki'] = '';
	$items[$a[2]][$i]['KratnostOtpuska'] = '';
	$items[$a[2]][$i]['PorNomer'] = $i;
	$items[$a[2]][$i]['Artikul'] = $a[5];
	$items[$a[2]][$i]['Svertka'] = $a[4];
	$items[$a[2]][$i]['Naimenovanie'] = $a[3];
	$items[$a[2]][$i]['Foto'] = $a[18]; #$host.'/import/img/'.$a[18].'.jpg';
	$items[$a[2]][$i]['CZena1'] = $a[9];
	$items[$a[2]][$i]['CZena2'] = $a[10];
	$items[$a[2]][$i]['CZena3'] = $a[11];
	$items[$a[2]][$i]['EdIzmereniya'] = $a[7];
    $items[$a[2]][$i]['show_in_price'] = $a[17];
	$items[$a[2]][$i]['desc'] = str_replace('<br>',"\n",str_replace('<br><br>',"\n",$a[19]));

	//Если забыли заполнить свёртку - пусть там будет наименование
	if($items[$a[2]][$i]['Svertka']=='') $items[$a[2]][$i]['Svertka'] = trim(strval($items[$a[2]][$i]['Naimenovanie']));
	if (strlen($items[$a[2]][$i]['desc'])<4) $items[$a[2]][$i]['desc'] = '';
	
}
	
//var_dump($tempArray);	


//++++++++++++ POST - LOAD ITEMS DEBUG ++++++++++++++++++

/*foreach($items as $item) { 

	echo('<pre>');
	var_dump($item);
	echo('</pre>');
	
	if ($item[5] == '11479') {
		echo '<pre>';
		var_dump($item);
		echo '</pre>';
	}
}

die;*/

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++

	
e($i.' items loaded from catalog.txt lines: good='.$good.' bad='.$bad);

echo ('badlines');
foreach($items as $item) { 

/*echo '<pre>';
var_dump($item);
echo '</pre>';*/
}


//die;

function testphile($url){
//функция для проверки наличия файла на сервере по адресу $url
	$Headers = @get_headers($url);
	// проверяем ли ответ от сервера с кодом 200 - ОК
	//echo $Headers[0];
	//echo (preg_match("|200|", $Headers[0]));
	if(preg_match("|200|", $Headers[0])) { // - немного дольше :)

		//echo 'find a photo!'.$url;die;
		echo 'true';
		return true;
	} else {
		echo 'false';
		return false;
	}


}


function write2log($str) {
//заглушка для писульки в лог строки $str
	e($str);
}

function  rus2lat($text)
//функция для какого то проеобраазования символов
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
//функция для обновления или создания разделов
{
	e('getSectionID: started for '.$name.' photo= '.$photo);

	global $iblock, $url, $host, $log;
	$sc = new CIBlockSection;
	$new_name = preg_replace("/^([0-9]{1,2}\. ?[0-9]?\.? ?[0-9]* ?)/", "", trim($name));
	$code = rus2lat($new_name);
	//$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_INTERNAL_ID' => $internal, "SECTION_ID" => $parent);
	$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_ROWID' => $internal, "SECTION_ID" => $parent);
	###############3
	#$arFilter = Array('IBLOCK_ID' => $iblock, 'UF_ORIGINAL_NAME' => $name);
	###############33
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_*"));
  $picfile = $host.'import/img/'.$photo.'.jpg';
	if ($ar_result = $db_list->GetNext())
	{
		e('getSectionID: section found, updating');

		$ID = $ar_result["ID"];
		$arUpdate = Array(
			"NAME" => $new_name,
			"UF_ORIGINAL_NAME" => $name,
			//"UF_PHOTO_ID" => $photo,
			"UF_PRICE_ID" => $price_id,
			"DESCRIPTION" => $descr,
			"SORT" => $sort,
			"CODE" => $code,
			//   "UF_TEMPLATE"=>0
		);

        $arUpdate["PICTURE"] = false;
        $arUpdate["UF_TEMPLATE"] = 0;

	if ($photo!=0)
	if(testphile($picfile))
	{
				e('getSectionID: loading a picture with addr..  '.$picfile.' existance = '.testphile($picfile));
				$pic = CFile::MakeFileArray($picfile);
                if ($pic["size"] > 0)
								{
                    $arUpdate["PICTURE"] = $pic;
                    $arUpdate["UF_TEMPLATE"] = 1;
                }
  }
	else e('getSectionID: pic with addr '.$picfile.' not found');

		$sc->Update($ID, $arUpdate);
	}
	else
	{
		e('getSectionID: section not found, creating');

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
			if($photo != 0)
			if(testphile($picfile))
			{
				$pic = CFile::MakeFileArray($picfile);
				if ($pic["size"] > 0) {
				echo 'OK';
				$arAdd["PICTURE"] = $pic;
				$arAdd["UF_TEMPLATE"] = 1;
			}
		}
		$ID = $sc->Add($arAdd);
		e("Новый раздел $ID : ".print_r($arAdd, true));
	}
	return $ID;
}

function removeSection($category, $presentedCats)
//фукция для удаления разделов из категории $category кроме $presentedCats)
{
	e("removeSection: started $category");
	global $log;
	global $iblock;
	$sc = new CIBlockSection;
	$arFilter = Array("IBLOCK_ID" => $iblock, "!ID" => $presentedCats, "SECTION_ID" => $category);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false);
	while ($ar_result = $db_list->GetNext()) {
		if ($log) {e("Удаляем раздел: ".print_r($ar_result, true));}
		$sc->Delete($ar_result["ID"]);
	}
}


function sectionWalker($xml, $top)
//рекурсивная функция для обхода разделов
{
	e("sectionWalker started");
	global $productSections;
	$present = Array();
	foreach ($xml->category as $topContent)
	{
		$image = $topContent->sID;
		if (sizeof($topContent->childs->category) > 0) {
			e("sectionWalker: $topContent->ID got childs, section-walking it");
			$desc = str_replace('[BR]',"\n",str_replace('[BR][BR]',"\n",$topContent->desc));
			$ID = getSectionID(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), 	$desc);
			$present[] = $ID;
			sectionWalker($topContent->childs, $ID);
		}
		else
		{
			e("sectionWalker: $topContent->ID have no children, removin' it");
			$desc = str_replace('[BR]',"\n",str_replace('[BR][BR]',"\n",$topContent->desc));
			$ID = getSectionID(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), 	$desc, true);
			$present[] = $ID;
			removeSection($ID, Array());
		}
		$productSections[$ID] = (int)$topContent->ID;
	}
	removeSection($top, $present);
}

function addUpdateElement($item, $siteCatID)
//функция для добавления $item в раздел $siteCatId (товаров в раздел)
{
	$id_var = $item['ID'];
	$id_name = $item['Svertka'];
	e("addUpdateElement started with $id_var ($id_name) siteCatID = $siteCatID");
	//echo('<pre>');
	//var_dump($item);
	//echo('</pre>');

	//var_dump($item);

	global $iblock, $url, $host;
	$el = new CIBlockElement;
	$arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_PHOTO_ID", "PREVIEW_PICTURE");
	$arFilter = Array("IBLOCK_ID" => $iblock, "PROPERTY_ROWID" => intval($item['ID']), "SECTION_ID" => $siteCatID);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	if ($ob = $res->GetNext())
	{
		e('addUpdateElement: object found, updating');
		$propsToUpdate = Array(
			"ARTICUL" => $item['Artikul'],
			"PRICE" => (float)str_replace(",", ".", $item['CZena1']),
			"PRICE_OPT" => (float)str_replace(",", ".", $item['CZena2']),
			"PRICE_OPT2" => (float)str_replace(",", ".", $item['CZena3']),
			"UNITS" => $item['EdIzmereniya'],
			"NOMNOMER" => trim(strval($item['NomNomer'])),
			"UPAKOVKA" => $item['VUpakovke'],
			"UPAKOVKA2" => $item['VUpakovke2'],
			"VES" => $item['Ves'],
			"OSTATOK" => $item['Ostatok'],
			"NAIMENOVANIE" => $item['Naimenovanie'],
			"NomenklaturaGeog" => $item['NomenklaturaGeog'],
			"V_REZERVE" => $item['VRezerve'],
            "SHOW_IN_PRICE" => ($item['show_in_price'] > 0) ? 1: 0,
            "SORT_IN_PRICE" => $item['show_in_price']
		);
		$arUpdate = Array(
			"NAME" => trim(strval($item['Svertka'])),
			"PREVIEW_TEXT" => trim(strval($item['desc'])),
			"SORT" => intval($item['PorNomer'])
		);
		
		/*if($item['NomNomer']=='4607120134699') {			
		echo 'xxxwxxxwxxxwxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';			
					/*echo ("item\r\n");
					
					var_dump($item);
					$picfile = $host.'import/img/'.$photo.'.jpg';
					echo ("picfile\r\n");
					var_dump($picfile);
					$pic = CFile::MakeFileArray($picfile);
					echo("pic\r\n");
					var_dump($pic);
						/*if ($pic["size"] > 0) {
							e('addUpdateElement: picsize>0, updating');
							$arUpdate['PREVIEW_PICTURE'] = $pic;
						}*/						
		/*echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';echo 'xxxxxxxxxxxxxxxxx<br>';						
		}*/
		//var_dump($ob["PROPERTY_PHOTO_ID_VALUE"]);
		//var_dump($ob);
		//echo ('<br>');
		
        $rsFile = CFile::GetByID($ob["PREVIEW_PICTURE"]);
		//var_dump($rsFile);
		//echo '<br>';
		
		//var_dump($item['Foto']);
		//echo ('<br>');		
		//var_dump($photo);
		//echo ('<br>');		
		//echo 'xxxxxxxxxxxxxxxxx<br>';

		
		$photo = $item['Foto'];
		
		
		$picfile = $host.'import/img/'.$photo.'.jpg';

		$flag_update_pic = false;
		if ($ob["PROPERTY_PHOTO_ID_VALUE"] != $item['Foto']) $flag_update_pic = true;
		if ($ob["PREVIEW_PICTURE"] == NULL)	$flag_update_pic = true;
		if (testphile($picfile)) $flag_update_pic = true;
		
		e('addUpdateElement: i want to update photo with $flag_update_pic='.$flag_update_pic.' and item photo = "'.testphile($picfile).'"');
		
		if(($flag_update_pic) && (testphile($picfile)))
		{
			e('addUpdateElement: tryin to update pic');
			$photo = $item['Foto'];

			
			//		echo '<br> pic file exists ('.$picfile.')? :'.(file_exists($picfile) );
			//		if (file_exists($picfile))

			//echo '$picfile='.$picfile.' '.testphile($picfile).'<br>';
			//var_dump(testphile($picfile));
			

			//if($upload_pix)
			{
				//e('addUpdateElement: $upload_pix = true');
				if($photo != 0)
				{
					e('addUpdateElement: $photo!=0');
					{						
						e('addUpdateElement: testphile($picfile)');
						$propsToUpdate["PHOTO_ID"] = $item['Foto'];
						$pic = CFile::MakeFileArray($picfile);
						if ($pic["size"] > 0) {
							e('addUpdateElement: picsize>0, updating');
							$arUpdate['PREVIEW_PICTURE'] = $pic;
						}
					}
				}
			}
		}
		else 
		{
			e('addUpdateElement: tryin to DELETE le photograpy');
			$arUpdate['PREVIEW_PICTURE'] = array('del' => 'Y');
		}
		
		$el->Update($ob["ID"], $arUpdate);
		CIBlockElement::SetPropertyValuesEx($ob["ID"], $iblock, $propsToUpdate);

		$ID = $ob["ID"];
	}
	else
	{

		e('addUpdateElement: object not found, tryin to create em');

		$arLoad = Array(
			"IBLOCK_ID" => $iblock,
			"IBLOCK_SECTION_ID" => $siteCatID,
			"NAME" => trim(strval($item['Svertka'])),
			"PREVIEW_TEXT" => trim(strval($item['desc'])),
			"SORT" => intval($item['PorNomer']),
			"PROPERTY_VALUES" => array(
				"ARTICUL" => $item['Artikul'],
				"ROWID" => $item['ID'],
				"PRICE" => (float)str_replace(",", ".", $item['CZena1']),
				"PRICE_OPT" => (float)str_replace(",", ".", $item['CZena2']),
				"PRICE_OPT2" => (float)str_replace(",", ".", $item['CZena3']),
				"NAIMENOVANIE" => trim(strval($item['Naimenovanie'])),
				"PHOTO_ID" => $item['Foto'],
				"VES" => $item['Ves'],
				"NOMNOMER" => trim(strval($item['NomNomer'])),
				"UNITS" => $item['EdIzmereniya'],
				"UPAKOVKA" => $item['VUpakovke'],
				"NomenklaturaGeog" => $item['NomenklaturaGeog'],
				"UPAKOVKA2" => $item['VUpakovke2'],
				"OSTATOK" => $item['Ostatok'],
				"V_REZERVE" => $item['VRezerve'],
                "SHOW_IN_PRICE" => ($item['show_in_price'] > 0) ? 1: 0,
                "SORT_IN_PRICE" => $item['show_in_price']
			),
		);

		$photo = $item['Foto'];
		$picfile = $host.'import/img/'.$photo.'.jpg';
		
		e("addUpdateElement: lurking for a picfile $picfile");		
		//	echo 'testphile:'+testphile($picfile);die;
		
		//var_dump($upload_pix);
		//var_dump($photo);
		//var_dump(testphile($picfile));
		
		//if($upload_pix)
		if($photo != 0)
		if(testphile($picfile))
		{
			e("addUpdateElement: updating photo ... $photo");
			$pic = CFile::MakeFileArray($picfile);

			if ($pic["size"] > 0) {
				$arLoad["PREVIEW_PICTURE"] = $pic;
			}
		}		
		$ID = $el->Add($arLoad);
		echo('****');
		if((isset($el->LAST_ERROR))) echo($el->LAST_ERROR);
		echo('****');
	}
	return $ID;
}


function parseSection($strCatID, $siteCatID, $price_id, $depth, $items)
//функция для удаления пустых разделов или что-то такое
{

	$total = 0;

	e("parseSection: started within params: $strCatID, $siteCatID, $price_id, $depth");

	global $iblock, $url, $log;

	$present = Array();

	e("parseSection: searching for id ... $strCatID");

	if(isset($items[$strCatID]))
	foreach($items[$strCatID] as $item)
	{
		
		//var_dump($item);die;
		
		e("parseSection: items for $strCatID found, loading item " + $item[5]);
		$ID = addUpdateElement($item, $siteCatID);
		e("parseSection: addUpdateElement returned $ID");
		$present[] = $ID;
		$total++;
	}

	e("parseSection: ended with count $total");

	$el = new CIBlockElement;
	$arFilter = Array("IBLOCK_ID" => $iblock, "SECTION_ID" => $siteCatID, "!ID" => $present);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, Array("ID"));
	while ($ob = $res->GetNext()) {
		e('parseSection: removing element');
		$el->Delete($ob["ID"]);
	}

}

/*
+++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++
+++++ END OF FUNCTIONS AREA +++++
+++++++++++++++++++++++++++++++++
+++++++++++++++++++++++++++++++++
*/

$xml = simplexml_load_file("./cat.xml");

if (sizeof($xml->category))
{
	sectionWalker($xml, 0);
	$arFilter = Array('IBLOCK_ID' => $iblock, 'ID' => array_keys($productSections));
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_PRICE_ID"));
	while ($ar_result = $db_list->GetNext()) {
		parseSection($productSections[$ar_result["ID"]], $ar_result["ID"], (int)$ar_result["UF_PRICE_ID"], $ar_result["DEPTH_LEVEL"], $items);
	}
	$finishTime = date("d.m.Y H:i:s");
}

e("Импорт завершен ".date("d.m.Y H:i:s"));
