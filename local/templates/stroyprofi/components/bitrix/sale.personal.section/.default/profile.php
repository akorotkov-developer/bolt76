<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
Extension::load('ui.bootstrap4');

if ($arParams['SHOW_PROFILE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}


if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PROFILE"));?>

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
			"bitrix:sale.personal.profile.list",
			"",
			array(
				"PATH_TO_DETAIL" => $arResult['PATH_TO_PROFILE_DETAIL'],
				"PATH_TO_DELETE" => $arResult['PATH_TO_PROFILE_DELETE'],
				"PER_PAGE" => $arParams["PROFILES_PER_PAGE"],
				"SET_TITLE" =>$arParams["SET_TITLE"],
			),
			$component
		);
		?>
    </div>
</div>
