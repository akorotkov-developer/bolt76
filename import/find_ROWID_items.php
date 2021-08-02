<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

require 'export/config.php';
require 'export/db.php';

$db = new DB();
if ($db->connect()) {

	$arSelect = Array("ID", "NAME", "PROPERTY_ROW_ID", "PROPERTY_ROWID");
	$arFilter = Array("IBLOCK_ID" => "1", "ACTIVE" => "Y", "PROPERTY_ROWID" => false);
	$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
	while ($ob = $res->GetNext()) {
		//$cat = $db->fetch_array('SELECT NomenklaturaCZeni FROM COSTS WHERE ROWID='.$ob['PROPERTY_ROW_ID_VALUE']);
		//$cat = $cat[0];
		print_r($ob);
		//CIBlockElement::SetPropertyValuesEx($ob["ID"], 1, Array("ROWID" => $cat['NomenklaturaCZeni']));
		//die();
	}

}
