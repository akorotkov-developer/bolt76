<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => "Корзина",
	"DESCRIPTION" => "Корзина для сайта СтройПрофи",
	"ICON" => "/images/include.gif",
	"PATH" => array(
		"ID" => "utility",
		"CHILD" => array(
			"ID" => "include_area",
			"NAME" => GetMessage("MAIN_INCLUDE_GROUP_NAME"),
		),
	),
);
?>