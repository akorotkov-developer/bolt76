<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("iblock");

require 'export/config.php';
require 'export/db.php';

$db = new DB();
if ($db->connect()) {

	$arFilter = Array('IBLOCK_ID' => 1, "UF_ROWID" => false);
	$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_ROWID", "UF_INTERNAL_ID"));
	$i = 1;
	while ($ar_result = $db_list->GetNext()) {
		/*
		ini_set('display_errors', 'on');
		error_reporting(E_ALL);
		set_time_limit(15);
		//*/
		$cat = $db->fetch_array('SELECT NomenklaturaCZeni FROM COSTS WHERE ROWID='.$ar_result['UF_INTERNAL_ID']);
		$cat = $cat[0];
		if ($cat['NomenklaturaCZeni']) {
			$bs = new CIBlockSection;
			$bs->Update($ar_result["ID"], Array("UF_ROWID"=>$cat['NomenklaturaCZeni']));
		} else {
			print $i++."<br/>";
			print_r($ar_result);
			print "<hr/>";
		}
	}
}
