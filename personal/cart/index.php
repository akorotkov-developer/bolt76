<?
define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
?>

<h1>Моя корзина</h1>
<?php

/** Определяем, является ли пользователь покупателем киоска */
global $USER;
$arGroups = [];

$rsGroups = \CUser::GetUserGroupEx($USER->GetID());
while($arGroup = $rsGroups->GetNext()) {
    $arGroups[] = $arGroup['STRING_ID'];
}

$isKioskBuyer = false;
if (in_array('KIOSK_BUYER', $arGroups) || strpos($_SERVER['HTTP_USER_AGENT'], 'KioskBrowser') !== false) {
    $isKioskBuyer = true;
    ?>
    <link rel="stylesheet" type="text/css" href="<?= SITE_TEMPLATE_PATH ?>/css_templates/print_styles.css"
          media="print">
        <?php
}?>

<?php


$APPLICATION->IncludeComponent(
	"bitrix:sale.basket.basket",
	"",
	[
		"ACTION_VARIABLE" => "basketAction",
		"ADDITIONAL_PICT_PROP_2" => "-",
		"ADDITIONAL_PICT_PROP_3" => "-",
		"ADDITIONAL_PICT_PROP_6" => "-",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AUTO_CALCULATION" => "Y",
		"BASKET_IMAGES_SCALING" => "adaptive",
		"COLUMNS_LIST" => array(0=>"NAME",1=>"DISCOUNT",2=>"PRICE",3=>"QUANTITY",4=>"SUM",5=>"PROPS",6=>"DELETE",7=>"DELAY",),
		"COLUMNS_LIST_EXT" => array("PREVIEW_PICTURE","DISCOUNT","DELETE","DELAY","TYPE","SUM", "PROPERTY_ARTICUL", "PROPERTY_Naimenovanie"),
		"COLUMNS_LIST_MOBILE" => array("PREVIEW_PICTURE","DISCOUNT","DELETE","DELAY","TYPE","SUM", "PROPERTY_ARTICUL", "PROPERTY_Naimenovanie"),
		"COMPATIBLE_MODE" => "Y",
		"CORRECT_RATIO" => "Y",
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => "N",
		"DEFERRED_REFRESH" => "N",
		"DISCOUNT_PERCENT_POSITION" => "bottom-right",
		"DISPLAY_MODE" => "extended",
		"EMPTY_BASKET_HINT_PATH" => "/",
		"GIFTS_BLOCK_TITLE" => "Выберите один из подарков",
		"GIFTS_CONVERT_CURRENCY" => "N",
		"GIFTS_HIDE_BLOCK_TITLE" => "N",
		"GIFTS_HIDE_NOT_AVAILABLE" => "N",
		"GIFTS_MESS_BTN_BUY" => "Выбрать",
		"GIFTS_MESS_BTN_DETAIL" => "Подробнее",
		"GIFTS_PAGE_ELEMENT_COUNT" => "4",
		"GIFTS_PLACE" => "BOTTOM",
		"GIFTS_PRODUCT_PROPS_VARIABLE" => "prop",
		"GIFTS_PRODUCT_QUANTITY_VARIABLE" => "quantity",
		"GIFTS_SHOW_DISCOUNT_PERCENT" => "Y",
		"GIFTS_SHOW_OLD_PRICE" => "N",
		"GIFTS_TEXT_LABEL_GIFT" => "Подарок",
		"HIDE_COUPON" => "N",
		"LABEL_PROP" => array(),
		"OFFERS_PROPS" => array(0=>"SIZES_SHOES",1=>"SIZES_CLOTHES",2=>"COLOR_REF",),
		"PATH_TO_ORDER" => "/personal/order/make/",
		"PRICE_DISPLAY_MODE" => "Y",
		"PRICE_VAT_SHOW_VALUE" => "Y",
		"PRODUCT_BLOCKS_ORDER" => "props,sku,columns",
		"QUANTITY_FLOAT" => "N",
		"SET_TITLE" => "N",
		"SHOW_DISCOUNT_PERCENT" => "Y",
		"SHOW_FILTER" => "Y",
		"SHOW_RESTORE" => "Y",
		"TEMPLATE_THEME" => "site",
		"TOTAL_BLOCK_DISPLAY" => array("bottom"),
		"USE_DYNAMIC_SCROLL" => "Y",
		"USE_ENHANCED_ECOMMERCE" => "N",
		"USE_GIFTS" => "N",
		"USE_PREPAYMENT" => "N",
		"USE_PRICE_ANIMATION" => "Y",
        "IS_KIOSK_BUYER" => $isKioskBuyer
	]
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>