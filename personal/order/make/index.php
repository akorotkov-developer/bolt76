<?
define("HIDE_SIDEBAR", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Оформление заказа");

use Bitrix\Main\UI\Extension;
Extension::load('ui.bootstrap4');
?>

<?php
if ($_REQUEST['PERSON_TYPE'] == 2) {
    $arOrderProps = [
        0 => "9",
        1 => "10",
        2 => "11",
        3 => "12",
        4 => "13",
        5 => "14",
        6 => "15",
    ];
} else {
    $arOrderProps = [
        0 => "1",
        1 => "2",
        2 => "3",
        3 => "4",
        4 => "5",
        5 => "6",
        6 => "7",
    ];
}

$APPLICATION->IncludeComponent(
	"tega:order.simple", 
	".default", 
	array(
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"ANONYMOUS_USER_ID" => "56",
		"BASKET_PAGE" => "/personal/cart/",
		"EMAIL_PROPERTY" => "2",
		"ENABLE_VALIDATION_INPUT_ID" => "simple_order_form_validation",
		"ENABLE_VALIDATION_INPUT_NAME" => "validation",
		"EVENT_TYPES" => array(
			0 => "SALE_NEW_ORDER",
		),
		"FIO_PROPERTY" => "1",
		"FORM_ID" => "simple_order_form",
		"FORM_NAME" => "simple_order_form",
		"ORDER_PROPS" => $arOrderProps,
		"ORDER_RESULT_PAGE" => "/personal/order/make/success/",
		"PERSON_TYPE_ID" => (!empty($_REQUEST['PERSON_TYPE'])) ? $_REQUEST['PERSON_TYPE'] : "1",
		"PHONE_PROPERTY" => "3",
		"REQUIRED_ORDER_PROPS" => array(
		),
		"SET_DEFAULT_PROPERTIES_VALUES" => "Y",
		"SITE_ID" => "s1",
		"USER_CONSENT" => "Y",
		"USER_CONSENT_ID" => "1",
		"USER_CONSENT_IS_CHECKED" => "Y",
		"USER_CONSENT_IS_LOADED" => "Y",
		"USE_DATE_CALCULATION" => "N",
		"COMPONENT_TEMPLATE" => ".default"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>