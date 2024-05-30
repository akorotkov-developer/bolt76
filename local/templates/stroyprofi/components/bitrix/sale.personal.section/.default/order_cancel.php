<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_ORDER_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}
elseif ($arParams['ORDER_DISALLOW_CANCEL'] === 'Y')
{
	LocalRedirect($arResult['PATH_TO_ORDERS']);
}
if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDERS"), $arResult['PATH_TO_ORDERS']);
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDER_DETAIL", array("#ID#" => $arResult["VARIABLES"]["ID"])));?>

<div class="profile-wrapper">
	<div class="left_block">
		<div class="main_navigation">
			<?php
			$APPLICATION->IncludeComponent(
				"bitrix:menu",
				"left",
				Array(
					"ROOT_MENU_TYPE" => "left",
					"MAX_LEVEL" => "3",
					"CHILD_MENU_TYPE" => "left",
					"USE_EXT" => "N",
					"DELAY" => "N",
					"ALLOW_MULTI_SELECT" => "Y",
					"MENU_CACHE_TYPE" => "N",
					"MENU_CACHE_TIME" => "3600",
					"MENU_CACHE_USE_GROUPS" => "Y",
					"MENU_CACHE_GET_VARS" => array()
				),
				false
			);?>
		</div>
	</div>

    <div class="right_block">
		<?php
		$APPLICATION->IncludeComponent(
			"bitrix:sale.personal.order.cancel",
			"",
			array(
				"PATH_TO_LIST" => $arResult["PATH_TO_ORDERS"],
				"PATH_TO_DETAIL" => $arResult["PATH_TO_ORDER_DETAIL"],
				"SET_TITLE" =>$arParams["SET_TITLE"],
				"ID" => $arResult["VARIABLES"]["ID"],
			),
			$component
		);
		?>
    </div>
</div>
