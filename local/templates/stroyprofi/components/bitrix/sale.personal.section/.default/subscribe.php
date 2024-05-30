<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;

if ($arParams['SHOW_SUBSCRIBE_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_SUBSCRIBE_NEW"));?>

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

    <?php
    $APPLICATION->IncludeComponent(
        'bitrix:catalog.product.subscribe.list',
        '',
        array(
            'SET_TITLE' => $arParams['SET_TITLE'],
            'DETAIL_URL' => $arParams['SUBSCRIBE_DETAIL_URL']
        ),
        $component
    );
    ?>
</div>
