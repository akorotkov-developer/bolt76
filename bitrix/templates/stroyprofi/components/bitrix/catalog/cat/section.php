<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//$isProduct = false;
CModule::IncludeModule("iblock");
$arFilter = Array('IBLOCK_ID' => $arParams["IBLOCK_ID"], 'CODE' => $arResult["VARIABLES"]["SECTION_CODE"], "ID"=>$arResult["VARIABLES"]["SECTION_ID"]);
$db_list = CIBlockSection::GetList(Array(), $arFilter, false, Array("UF_TEMPLATE", "UF_TABS"));
$tabsID = Array();
if ($ar_result = $db_list->GetNext()) {
    $template = ($ar_result["UF_TEMPLATE"]==1);
	$tabsID = $ar_result["UF_TABS"];
}
?>

<?$APPLICATION->IncludeComponent(
	"profi:catalog.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
  //      "FILTER_NAME" =>"arrSectionFilter",
        "TOP_DEPTH"=>1
	),
	$component
);?>
<?
if (count($tabsID) > 0 && $tabsID !== false) {
    $isTabs = true;
} else {
    $isTabs = false;
}
if ($isTabs) {
//    print_r($tabsID);
	$arSelect = Array("ID", "CODE", "PROPERTY_TITLE", "PREVIEW_TEXT", "PROPERTY_PDF");
	$arFilter = Array("IBLOCK_ID" => "4", "ACTIVE" => "Y", "ID" => $tabsID);
	$res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);
	$tabs = Array();
	?><ul class="section-tabs"><?
	?><li><a id="tab-prices" href="#prices">Цены</a></li><?
	while ($ob = $res->GetNext()) {
		$tabs[] = $ob;
		?><li><a id="tab-<?=$ob['CODE'];?>" href="#<?=$ob['CODE'];?>"><?=$ob['PROPERTY_TITLE_VALUE'];?></a></li><?
	}
	?><div class="clear"></div></ul><?
}
?>
<?if($isTabs){?><div class="section-panes"><div class="section-pane"><?}?>
<?$APPLICATION->IncludeComponent(
	"profi:catalog.section",
	($template?'section_descr':''),
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
 		"PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
		"META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
		"META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
		"BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
		"INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
		"FILTER_NAME" => $arParams["FILTER_NAME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_FILTER" => $arParams["CACHE_FILTER"],
		"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SET_STATUS_404" => $arParams["SET_STATUS_404"],
		"DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
		"PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
		"LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

		"PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
		"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],

		"DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
		"DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
		"PAGER_TITLE" => $arParams["PAGER_TITLE"],
		"PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
		"PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
		"PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
		"PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
		"PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],

		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		"OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
		"OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
		"OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
		"OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
		"OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"SECTION_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["section"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
		'CURRENCY_ID' => $arParams['CURRENCY_ID'],
        "SECTION_USER_FIELDS"=>Array("UF_COUNTS", "UF_SEE_ALSO")
	),
	$component
);
?>
<?if($isTabs) { ?></div><?
	foreach($tabs as $tab) {
		?><div class="section-pane"><div class="section-pane-inner"><?
		print $tab['PREVIEW_TEXT'];
		if ($tab['PROPERTY_PDF_VALUE']) {
$pdf = CFile::GetPath($tab['PROPERTY_PDF_VALUE']);
?><iframe src="https://docs.google.com/viewer?embedded=true&amp;url=strprofi.ru<?=urlencode($pdf);?>" width="830" height="1150" style="border: none;"></iframe><?
}
		?></div></div><?
	}
} ?>