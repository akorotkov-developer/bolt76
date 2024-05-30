<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_ORDER_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}	

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ORDERS"), $arResult['PATH_TO_ORDERS']);
?>
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
			"bitrix:sale.personal.order.list",
			"",
			array(
				"PATH_TO_DETAIL" => $arResult["PATH_TO_ORDER_DETAIL"],
				"PATH_TO_CANCEL" => $arResult["PATH_TO_ORDER_CANCEL"],
				"PATH_TO_CATALOG" => $arParams["PATH_TO_CATALOG"],
				"PATH_TO_COPY" => $arResult["PATH_TO_ORDER_COPY"],
				"PATH_TO_BASKET" => $arParams["PATH_TO_BASKET"],
				"PATH_TO_PAYMENT" => $arParams["PATH_TO_PAYMENT"],
				"SAVE_IN_SESSION" => $arParams["SAVE_IN_SESSION"],
				"ORDERS_PER_PAGE" => $arParams["ORDERS_PER_PAGE"],
				"SET_TITLE" =>$arParams["SET_TITLE"],
				"ID" => $arResult["VARIABLES"]["ID"],
				"NAV_TEMPLATE" => $arParams["NAV_TEMPLATE"],
				"ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
				"HISTORIC_STATUSES" => $arParams["ORDER_HISTORIC_STATUSES"],
				"ALLOW_INNER" => $arParams["ALLOW_INNER"],
				"ONLY_INNER_FULL" => $arParams["ONLY_INNER_FULL"],
				"CACHE_TYPE" => $arParams["CACHE_TYPE"],
				"CACHE_TIME" => $arParams["CACHE_TIME"],
				"CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
				"DISALLOW_CANCEL" => $arParams["ORDER_DISALLOW_CANCEL"],
				"DEFAULT_SORT" => $arParams["ORDER_DEFAULT_SORT"],
				"RESTRICT_CHANGE_PAYSYSTEM" => $arParams["ORDER_RESTRICT_CHANGE_PAYSYSTEM"],
				"REFRESH_PRICES" => $arParams["ORDER_REFRESH_PRICES"],
			),
			$component
		);
		?>
    </div>
</div>

