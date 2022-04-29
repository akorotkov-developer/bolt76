<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Каталог");
?>

<?php
// Показываем товары из подразделов в случае если применен фильтр, в противном случае показываем только списко разделов
// Если фильтр очищен (clear/apply), то также показываем только список разделов, а свойство INCLUDE_SUBSECTIONS присваиваем N
$arPageElement = explode('/', $APPLICATION->GetCurPage());

$isApplyFilter = false;
foreach ($arPageElement as $key => $element) {
    if ($element == 'apply' && $arPageElement[$key - 1] != 'clear') {
        $isApplyFilter = true;
    }
}

// Получим все свойства для отображения
// TODO надо закешировать этот метод
$dbResult = CIBlockProperty::GetList(
    [],
    [
        'IBLOCK_ID' => 1
    ]
);

// Получаем все текущие свойства инфоблока каталог
$arAvailableProps = [];
$arExcludedProps = [
    'PRICE_OPT', 'PRICE_OPT2', 'PRICE', 'SHOW_IN_PRICE', 'SORT_IN_PRICE', 'ROW_ID', 'PHOTO_ID', 'OSTATOK',
    'V_REZERVE', 'NAIMENOVANIE', 'ROWID', 'FOTOHASH', 'NOMNOMER', 'NomenklaturaGeog', 'PHOTOS'];
while($arResult = $dbResult->Fetch()) {
    if (!in_array($arResult['CODE'], $arExcludedProps)) {
        $arAvailableProps[] = $arResult['CODE'];
    }
}
?>

<?php
/** Определяем цену для текущего пользователя */
$priceGroup = UserHelper::getPriceUserGroup();

if ($priceGroup == 'OPT_2') {
    $sPriceCode = 'OPT';
} elseif ($priceGroup == 'OPT_3') {
    $sPriceCode = 'OPT2';
} else {
    $sPriceCode = 'OPT';
}

$APPLICATION->IncludeComponent(
	"bitrix:catalog",
    'cat',
	array(
		"IBLOCK_TYPE" => "content",
		"IBLOCK_ID" => "1",
		"BASKET_URL" => "/personal/cart/",
		"ACTION_VARIABLE" => "action",
		"PRODUCT_ID_VARIABLE" => "id",
		"SECTION_ID_VARIABLE" => "SECTION_ID",
		"PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/catalog/",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "36000000",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"SET_TITLE" => "Y",
		"SET_STATUS_404" => "N",
		"USE_FILTER" => "Y",
		"FILTER_NAME" => "arrFilter",
		"USE_COMPARE" => "N",
		"PRICE_CODE" => array(
			0 => $sPriceCode,
		),
		"USE_PRICE_COUNT" => "N",
		"SHOW_PRICE_COUNT" => "1",
		"PRICE_VAT_INCLUDE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "N",
        "DETAIL_ADD_TO_BASKET_ACTION" => array("ADD"),
		"USE_PRODUCT_QUANTITY" => "Y",
		"SHOW_TOP_ELEMENTS" => "N",
		"SECTION_COUNT_ELEMENTS" => "N",
		"PAGE_ELEMENT_COUNT" => "35",
		"LINE_ELEMENT_COUNT" => "1",
		"ELEMENT_SORT_FIELD" => "PROPERTY_Naimenovanie",
		"ELEMENT_SORT_ORDER" => "asc",
        "ELEMENT_SORT_FIELD2" => "NAME",
        "ELEMENT_SORT_ORDER2ELEMENT_SORT_ORDER2" => "asc",
		"LIST_PROPERTY_CODE" => array(
			0 => "ARTICUL",
			1 => "UNITS",
			2 => "PRICE_OPT",
			3 => "PRICE_OPT2",
			4 => "PRICE",
			5 => "",
		),
		"INCLUDE_SUBSECTIONS" => $isApplyFilter ? 'Y' : 'N',
		"IS_APPLY_FILTER" => $isApplyFilter ? 'Y' : 'N',
		"LIST_META_KEYWORDS" => "-",
		"LIST_META_DESCRIPTION" => "-",
		"LIST_BROWSER_TITLE" => "-",
		"DETAIL_PROPERTY_CODE" => $arAvailableProps,
		/*"DETAIL_PROPERTY_CODE" => array(
			0 => 'ARTICUL',
			1 => 'UNITS',
            2 => 'UPAKOVKA',
            3 => 'UPAKOVKA2',
            4 => 'VES',
            5 => 'TIP_KREPEJA',
            6 => 'STANDART',
            7 => 'KLAS_PROCHNOSTI',
            8 => 'DIAMETR',
            9 => 'DLINA',
            10 => 'DLINA_POLKI',
            11 => 'SHIRINA',
            12 => 'TOLSHINA',
            13 => 'POKRITIE',
            14 => 'DIAMETER'
		),*/
		"DETAIL_META_KEYWORDS" => "-",
		"DETAIL_META_DESCRIPTION" => "-",
		"DETAIL_BROWSER_TITLE" => "-",
		"LINK_IBLOCK_TYPE" => "",
		"LINK_IBLOCK_ID" => "",
		"LINK_PROPERTY_SID" => "",
		"LINK_ELEMENTS_URL" => "link.php?PARENT_ELEMENT_ID=#ELEMENT_ID#",
		"DISPLAY_TOP_PAGER" => "Y",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"PAGER_TITLE" => "Товары",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "modern",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "Y",
		"AJAX_OPTION_ADDITIONAL" => "",
		"COMPONENT_TEMPLATE" => "cat",
		"USE_MAIN_ELEMENT_SECTION" => "N",
		"DETAIL_STRICT_SECTION_CHECK" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"ADD_SECTIONS_CHAIN" => "Y",
		"ADD_ELEMENT_CHAIN" => "N",
		"ADD_PROPERTIES_TO_BASKET" => "Y",
		"PRODUCT_PROPS_VARIABLE" => "prop",
		"PARTIAL_PRODUCT_PROPERTIES" => "N",
		"PRODUCT_PROPERTIES" => array(
		),
		"SECTION_TOP_DEPTH" => "2",
		"SECTION_BACKGROUND_IMAGE" => "-",
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DETAIL_CHECK_SECTION_ID_VARIABLE" => "N",
		"DETAIL_BACKGROUND_IMAGE" => "-",
		"SHOW_DEACTIVATED" => "N",
		"USE_STORE" => "N",
		"PAGER_BASE_LINK_ENABLE" => "N",
		"SHOW_404" => "N",
		"MESSAGE_404" => "",
		"COMPATIBLE_MODE" => "Y",
		"USE_ELEMENT_COUNTER" => "Y",
		"DISABLE_INIT_JS_IN_COMPONENT" => "N",
		"HIDE_NOT_AVAILABLE" => "N",
		"HIDE_NOT_AVAILABLE_OFFERS" => "N",
		"USER_CONSENT" => "N",
		"USER_CONSENT_ID" => "0",
		"USER_CONSENT_IS_CHECKED" => "Y",
		"USER_CONSENT_IS_LOADED" => "N",
		"CONVERT_CURRENCY" => "N",
		"USE_ALSO_BUY" => "Y",
		"USE_GIFTS_DETAIL" => "Y",
		"USE_GIFTS_SECTION" => "Y",
		"USE_GIFTS_MAIN_PR_SECTION_LIST" => "Y",
		"GIFTS_DETAIL_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_DETAIL_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_DETAIL_BLOCK_TITLE" => "Выберите один из подарков",
		"GIFTS_DETAIL_TEXT_LABEL_GIFT" => "Подарок",
		"GIFTS_SECTION_LIST_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_SECTION_LIST_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_SECTION_LIST_BLOCK_TITLE" => "Подарки к товарам этого раздела",
		"GIFTS_SECTION_LIST_TEXT_LABEL_GIFT" => "Подарок",
		"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
		"GIFTS_SHOW_OLD_PRICE" => "Y",
		"GIFTS_SHOW_NAME" => "Y",
		"GIFTS_SHOW_IMAGE" => "Y",
		"GIFTS_MESS_BTN_BUY" => "Выбрать",
		"GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_MAIN_PRODUCT_DETAIL_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE" => "Выберите один из товаров, чтобы получить подарок",
		"DETAIL_SET_VIEWED_IN_COMPONENT" => "N",
		"SEF_URL_TEMPLATES" => array(
			"sections" => "",
			"section" => "#SECTION_ID#-#SECTION_CODE#/",
			"element" => "#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#",
			"compare" => "compare.php?action=#ACTION_CODE#",
			"smart_filter" => "#SECTION_ID#-#SECTION_CODE#/filter/#SMART_FILTER_PATH#/apply/",
		),
		"VARIABLE_ALIASES" => array(
			"compare" => array(
				"ACTION_CODE" => "action",
			),
		)
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>