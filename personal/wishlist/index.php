<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Избранное");
?>

<?php
/** Определяем цену для текущего пользователя */
$priceGroup = UserHelper::getPriceUserGroup();

if ($priceGroup == 'OPT_2') {
    $sPriceCode = 'OPT';
} elseif ($priceGroup == 'OPT_3') {
    $sPriceCode = 'OPT2';
} else {
    $sPriceCode = 'BASE';
}

/** Определяем избранные товары */
global $USER;
if(!$USER->IsAuthorized()) // Для неавторизованного
{
    global $APPLICATION;
    $arFavorites = unserialize($_COOKIE["favorites"]);
}
else {
    $idUser = $USER->GetID();
    $rsUser = CUser::GetByID($idUser);
    $arUser = $rsUser->Fetch();
    $arFavorites = $arUser['UF_FAVORITES'];

}
$GLOBALS['arrFilter']= ['ID' => $arFavorites];
if(count($arFavorites) > 0 && is_array($arFavorites)) {

    $APPLICATION->IncludeComponent(
        "bitrix:catalog.section",
        "favorite_list",
        array(
            'IBLOCK_TYPE' => 'content',
            'IBLOCK_ID' => '1',
            'FILTER_NAME' => 'arrFilter',
            'ELEMENT_SORT_FIELD' => 'PROPERTY_Naimenovanie',
            'ELEMENT_SORT_ORDER' => 'asc',
            'ELEMENT_SORT_FIELD2' => 'NAME',
            'ELEMENT_SORT_ORDER2' => NULL,
            'PROPERTY_CODE' =>
                array(
                    0 => 'ARTICUL',
                    1 => 'UNITS',
                    2 => 'PRICE_OPT',
                    3 => 'PRICE_OPT2',
                    4 => 'PRICE',
                    5 => '',
                ),
            'PROPERTY_CODE_MOBILE' => NULL,
            'INCLUDE_SUBSECTIONS' => 'Y',
            'BASKET_URL' => '/personal/cart/',
            'ACTION_VARIABLE' => 'action',
            'PRODUCT_ID_VARIABLE' => 'id',
            'SECTION_ID_VARIABLE' => 'SECTION_ID',
            'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
            'PRODUCT_PROPS_VARIABLE' => 'prop',
            'CACHE_TYPE' => 'A',
            'CACHE_TIME' => '36000000',
            'CACHE_FILTER' => 'N',
            'CACHE_GROUPS' => 'Y',
            'DISPLAY_COMPARE' => 'N',
            'PAGE_ELEMENT_COUNT' => 0,
            'PRICE_CODE' =>
                array(
                    0 => $sPriceCode,
                ),
            'USE_PRICE_COUNT' => 'N',
            'SHOW_PRICE_COUNT' => '1',
            'SET_BROWSER_TITLE' => 'N',
            'SET_META_KEYWORDS' => 'N',
            'SET_META_DESCRIPTION' => 'N',
            'SET_LAST_MODIFIED' => 'N',
            'ADD_SECTIONS_CHAIN' => 'N',
            'PRICE_VAT_INCLUDE' => 'Y',
            'USE_PRODUCT_QUANTITY' => 'Y',
            'ADD_PROPERTIES_TO_BASKET' => 'Y',
            'PARTIAL_PRODUCT_PROPERTIES' => 'N',
            'PRODUCT_PROPERTIES' =>
                array(),
            'OFFERS_CART_PROPERTIES' =>
                array(),
            'OFFERS_FIELD_CODE' => NULL,
            'OFFERS_PROPERTY_CODE' =>
                array(),
            'OFFERS_SORT_FIELD' => NULL,
            'OFFERS_SORT_ORDER' => NULL,
            'OFFERS_SORT_FIELD2' => NULL,
            'OFFERS_SORT_ORDER2' => NULL,
            'OFFERS_LIMIT' => 0,
            'SECTION_ID' => NULL,
            'SECTION_CODE' => '',
            'SECTION_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/',
            'DETAIL_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#',
            'USE_MAIN_ELEMENT_SECTION' => 'N',
            'CONVERT_CURRENCY' => 'N',
            'CURRENCY_ID' => NULL,
            'HIDE_NOT_AVAILABLE' => 'N',
            'HIDE_NOT_AVAILABLE_OFFERS' => 'N',
            'LABEL_PROP' => NULL,
            'LABEL_PROP_MOBILE' => NULL,
            'LABEL_PROP_POSITION' => NULL,
            'ADD_PICT_PROP' => NULL,
            'PRODUCT_DISPLAY_MODE' => NULL,
            'PRODUCT_BLOCKS_ORDER' => NULL,
            //'PRODUCT_ROW_VARIANTS' => "[{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false},{'VARIANT':'3','BIG_DATA':false}]",
            'ENLARGE_PRODUCT' => NULL,
            'ENLARGE_PROP' => '',
            'SHOW_SLIDER' => NULL,
            'SLIDER_INTERVAL' => '',
            'SLIDER_PROGRESS' => '',
            'DISPLAY_TOP_PAGER' => 'N',
            'DISPLAY_BOTTOM_PAGER' => 'N',
            'HIDE_SECTION_DESCRIPTION' => 'Y',
            'RCM_TYPE' => '',
            'SHOW_FROM_SECTION' => 'Y',
            'OFFER_ADD_PICT_PROP' => NULL,
            'OFFER_TREE_PROPS' =>
                array(),
            'PRODUCT_SUBSCRIPTION' => NULL,
            'SHOW_DISCOUNT_PERCENT' => NULL,
            'DISCOUNT_PERCENT_POSITION' => NULL,
            'SHOW_OLD_PRICE' => NULL,
            'SHOW_MAX_QUANTITY' => NULL,
            'MESS_SHOW_MAX_QUANTITY' => '',
            'RELATIVE_QUANTITY_FACTOR' => '',
            'MESS_RELATIVE_QUANTITY_MANY' => '',
            'MESS_RELATIVE_QUANTITY_FEW' => '',
            'MESS_BTN_BUY' => '',
            'MESS_BTN_ADD_TO_BASKET' => '',
            'MESS_BTN_SUBSCRIBE' => '',
            'MESS_BTN_DETAIL' => '',
            'MESS_NOT_AVAILABLE' => '',
            'MESS_BTN_COMPARE' => '',
            'USE_ENHANCED_ECOMMERCE' => '',
            'DATA_LAYER_NAME' => '',
            'BRAND_PROPERTY' => '',
            'TEMPLATE_THEME' => '',
            'ADD_TO_BASKET_ACTION' => NULL,
            'SHOW_CLOSE_POPUP' => '',
            'COMPARE_PATH' => '/catalog/compare.php?action=#ACTION_CODE#',
            'COMPARE_NAME' => NULL,
            'USE_COMPARE_LIST' => 'Y',
            'BACKGROUND_IMAGE' => '',
            'DISABLE_INIT_JS_IN_COMPONENT' => 'N',
        ),
        false

    );
} else {?>
    <h2>Нет товаров в избранном</h2>
<?php } ?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>