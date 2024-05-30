<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Localization\Loc;
LocalRedirect('/personal/private/');

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}

$theme = Bitrix\Main\Config\Option::get("main", "wizard_eshop_bootstrap_theme_id", "blue", SITE_ID);

$availablePages = array();

if ($arParams['SHOW_ORDER_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ORDERS'],
		"name" => Loc::getMessage("SPS_ORDER_PAGE_NAME"),
		"icon" => '<i class="fa fa-calculator"></i>'
	);
}

if ($arParams['SHOW_ACCOUNT_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ACCOUNT'],
		"name" => Loc::getMessage("SPS_ACCOUNT_PAGE_NAME"),
		"icon" => '<i class="fa fa-credit-card"></i>'
	);
}

if ($arParams['SHOW_PRIVATE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_PRIVATE'],
		"name" => Loc::getMessage("SPS_PERSONAL_PAGE_NAME"),
		"icon" => '<i class="fa fa-user-secret"></i>'
	);
}

if ($arParams['SHOW_ORDER_PAGE'] === 'Y')
{

	$delimeter = ($arParams['SEF_MODE'] === 'Y') ? "?" : "&";
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_ORDERS'].$delimeter."filter_history=Y",
		"name" => Loc::getMessage("SPS_ORDER_PAGE_HISTORY"),
		"icon" => '<i class="fa fa-list-alt"></i>'
	);
}

if ($arParams['SHOW_PROFILE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_PROFILE'],
		"name" => Loc::getMessage("SPS_PROFILE_PAGE_NAME"),
		"icon" => '<i class="fa fa-list-ol"></i>'
	);
}

if ($arParams['SHOW_BASKET_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arParams['PATH_TO_BASKET'],
		"name" => Loc::getMessage("SPS_BASKET_PAGE_NAME"),
		"icon" => '<i class="fa fa-shopping-cart"></i>'
	);
}

if ($arParams['SHOW_SUBSCRIBE_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arResult['PATH_TO_SUBSCRIBE'],
		"name" => Loc::getMessage("SPS_SUBSCRIBE_PAGE_NAME"),
		"icon" => '<i class="fa fa-envelope"></i>'
	);
}

if ($arParams['SHOW_CONTACT_PAGE'] === 'Y')
{
	$availablePages[] = array(
		"path" => $arParams['PATH_TO_CONTACT'],
		"name" => Loc::getMessage("SPS_CONTACT_PAGE_NAME"),
		"icon" => '<i class="fa fa-info-circle"></i>'
	);
}
$availablePages[] = array(
    "path" => '/personal/wishlist/',
    "name" => 'Избранное',
    "icon" => '<i class="fa fa-info-circle"></i>'
);

$customPagesList = CUtil::JsObjectToPhp($arParams['~CUSTOM_PAGES']);
if ($customPagesList)
{
	foreach ($customPagesList as $page)
	{
		$availablePages[] = array(
			"path" => $page[0],
			"name" => $page[1],
			"icon" => (mb_strlen($page[2])) ? '<i class="fa '.htmlspecialcharsbx($page[2]).'"></i>' : ""
		);
	}
}

if (empty($availablePages))
{
	ShowError(Loc::getMessage("SPS_ERROR_NOT_CHOSEN_ELEMENT"));
}
else
{
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
                        "MAX_LEVEL" => "1",
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
            <div class="row">
                <div class="col-md-12 sale-personal-section-index">
                    <div class="row sale-personal-section-row-flex">
                        <?
                        foreach ($availablePages as $blockElement)
                        {
                            ?>
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12">
                                <div class="sale-personal-section-index-block bx-theme-<?=$theme?>">
                                    <a class="sale-personal-section-index-block-link" href="<?=htmlspecialcharsbx($blockElement['path'])?>">
                                        <span class="sale-personal-section-index-block-ico">
                                            <?=$blockElement['icon']?>
                                        </span>
                                        <h2 class="sale-personal-section-index-block-name">
                                            <?=htmlspecialcharsbx($blockElement['name'])?>
                                        </h2>
                                    </a>
                                </div>
                            </div>
                            <?
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
	<?php
}
?>
