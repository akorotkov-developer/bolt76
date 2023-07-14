<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//global $arrSectionFilter;
//$arrSectionFilter = Array("UF_PRODUCT"=>false)
?>
<?$APPLICATION->IncludeComponent(
	"profi:catalog.section.list",
	"main",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
     //   "FILTER_NAME" =>"arrSectionFilter",
        "TOP_DEPTH"=>1
	),
	$component
);
?>
<div class="clear"></div>