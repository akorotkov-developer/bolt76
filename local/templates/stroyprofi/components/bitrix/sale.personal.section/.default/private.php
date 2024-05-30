<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_PRIVATE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PRIVATE"));
if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_PRIVATE"));
}

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
        <?php $APPLICATION->IncludeComponent(
            "bitrix:main.profile",
            "",
            Array(
                "SET_TITLE" =>$arParams["SET_TITLE"],
                "AJAX_MODE" => $arParams['AJAX_MODE_PRIVATE'],
                "SEND_INFO" => $arParams["SEND_INFO_PRIVATE"],
                "CHECK_RIGHTS" => $arParams['CHECK_RIGHTS_PRIVATE'],
                "EDITABLE_EXTERNAL_AUTH_ID" => $arParams['EDITABLE_EXTERNAL_AUTH_ID'],
                "USER_PROPERTY" => ['PHONE_NUMBER', 'UF_COMPANY_NAME', 'UF_YUR_ADDRESS', 'UF_INN', 'UF_KPP']
            ),
            $component
        );?>
    </div>
</div>
