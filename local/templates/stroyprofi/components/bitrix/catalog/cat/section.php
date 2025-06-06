<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php

use \Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;

if ($arParams["USE_COMPARE"] === "Y") {
    $APPLICATION->IncludeComponent(
        "bitrix:catalog.compare.list",
        ".default",
        array(
            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
            "NAME" => $arParams["COMPARE_NAME"],
            "DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
            "COMPARE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["compare"],
            "ACTION_VARIABLE" => (!empty($arParams["ACTION_VARIABLE"]) ? $arParams["ACTION_VARIABLE"] : "action"),
            "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
            'POSITION_FIXED' => isset($arParams['COMPARE_POSITION_FIXED']) ? $arParams['COMPARE_POSITION_FIXED'] : '',
            'POSITION' => isset($arParams['COMPARE_POSITION']) ? $arParams['COMPARE_POSITION'] : ''
        ),
        $component,
        array("HIDE_ICONS" => "Y")
    );
}


$arFilter = array(
    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
    "ACTIVE" => "Y",
    "GLOBAL_ACTIVE" => "Y",
);
if (0 < intval($arResult["VARIABLES"]["SECTION_ID"]))
    $arFilter["ID"] = $arResult["VARIABLES"]["SECTION_ID"];
elseif ('' != $arResult["VARIABLES"]["SECTION_CODE"])
    $arFilter["=CODE"] = $arResult["VARIABLES"]["SECTION_CODE"];

$obCache = new CPHPCache();
if ($obCache->InitCache(36000, serialize($arFilter), "/iblock/catalog")) {
    $arCurSection = $obCache->GetVars();
} elseif ($obCache->StartDataCache()) {
    $arCurSection = array();
    if (Loader::includeModule("iblock")) {
        $dbRes = CIBlockSection::GetList(array(), $arFilter, false, array("ID"));

        if (defined("BX_COMP_MANAGED_CACHE")) {
            global $CACHE_MANAGER;
            $CACHE_MANAGER->StartTagCache("/iblock/catalog");

            if ($arCurSection = $dbRes->Fetch())
                $CACHE_MANAGER->RegisterTag("iblock_id_" . $arParams["IBLOCK_ID"]);

            $CACHE_MANAGER->EndTagCache();
        } else {
            if (!$arCurSection = $dbRes->Fetch())
                $arCurSection = array();
        }
    }
    $obCache->EndDataCache($arCurSection);
}
if (!isset($arCurSection))
    $arCurSection = array();
?>

<?
//$isProduct = false;
CModule::IncludeModule("iblock");
$arFilter = array('IBLOCK_ID' => $arParams["IBLOCK_ID"], 'CODE' => $arResult["VARIABLES"]["SECTION_CODE"], "ID" => $arResult["VARIABLES"]["SECTION_ID"]);
$db_list = CIBlockSection::GetList(array(), $arFilter, false, array("UF_TEMPLATE", "UF_TABS"));
$tabsID = array();
if ($ar_result = $db_list->GetNext()) {
    $template = ($ar_result["UF_TEMPLATE"] == 1);
    $tabsID = $ar_result["UF_TABS"];
}
?>

<?php if ($_GET['tst'] == 'tst') {?>
    <div class="b-tags">
        <div class="tags">
            <a href="#">
                4.8
                <span class="tags__hide">Класс прочности</span>
            </a>
            <a href="#">Анкер</a>
            <a href="#">Болт</a>
            <a href="#" class="active">Винт</a>
            <a href="#">Гвоздь</a>
        </div>
    </div>
<?php } ?>

<!-- Оверлей (фон затемнения) -->
<div class="filter-overlay"></div>

<div class="b-container-top">

        <div class="btn-filters">
            <a class="btn btn-default btn-sm" id="btn-filters-action" rel="nofollow">
                Фильтры
            </a>
        </div>


    <div class="col-lg-3 b-product-filter <?= (isset($arParams['FILTER_HIDE_ON_MOBILE']) && $arParams['FILTER_HIDE_ON_MOBILE'] === 'Y' ? ' hidden-xs' : '') ?>">
        <button class="close-filter-btn d-lg-none">× Закрыть</button>
        <div class="bx-sidebar-block">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:catalog.smart.filter",
                "",
                array(
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "SECTION_ID" => $arCurSection['ID'],
                    "FILTER_NAME" => $arParams["FILTER_NAME"],
                    "PRICE_CODE" => $arParams["~PRICE_CODE"],
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                    "SAVE_IN_SESSION" => "N",
                    "FILTER_VIEW_MODE" => $arParams["FILTER_VIEW_MODE"],
                    "XML_EXPORT" => "N",
                    "SECTION_TITLE" => "NAME",
                    "SECTION_DESCRIPTION" => "DESCRIPTION",
                    'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                    "TEMPLATE_THEME" => $arParams["TEMPLATE_THEME"],
                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                    "SEF_MODE" => $arParams["SEF_MODE"],
                    "SEF_RULE" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["smart_filter"],
                    "SMART_FILTER_PATH" => $arResult["VARIABLES"]["SMART_FILTER_PATH"],
                    "PAGER_PARAMS_NAME" => $arParams["PAGER_PARAMS_NAME"],
                    "INSTANT_RELOAD" => $arParams["INSTANT_RELOAD"],
                    "DISPLAY_ELEMENT_COUNT" => 'Y',
                ),
                $component,
                array('HIDE_ICONS' => 'Y')
            );
            ?>
        </div>
        <div class="hidden-xs">
            <?
            $APPLICATION->IncludeComponent(
                "bitrix:main.include",
                "",
                array(
                    "AREA_FILE_SHOW" => "file",
                    "PATH" => $arParams["SIDEBAR_PATH"],
                    "AREA_FILE_RECURSIVE" => "N",
                    "EDIT_MODE" => "html",
                ),
                false,
                array('HIDE_ICONS' => 'Y')
            );
            ?>
        </div>

        <?php
        // TODO что-то не так с блоком!!! Расширяет экран!!!
        ?>
        <!--<div class="b-sale">
                <?php
/*                $dbResult = CIBlockElement::GetList(
                    [],
                    [
                        'IBLOCK_ID' => 1,
                        '!=PROPERTY_SALE' => false,
                        [
                            'LOGIC' => 'AND',
                            '!=PROPERTY_OSTATOK' => false,
                        ],
                        '!=PROPERTY_OSTATOK' => '0',
                    ],
                    false,
                    false,
                    [
                        'ID'
                    ]
                );

                $arItems = [];
                while($arRes = $dbResult->Fetch()){
                    $arItems[] = $arRes['ID'];
                }

                if (count($arItems) > 0) {
                    $GLOBALS['arrFilterSale'] = ['ID' => $arItems];

                    $APPLICATION->IncludeComponent(
                        "bitrix:catalog.section",
                        "sale",
                        array(
                            'IBLOCK_TYPE' => 'content',
                            'IBLOCK_ID' => '1',
                            'ELEMENT_SORT_FIELD' => 'PROPERTY_Naimenovanie',
                            'ELEMENT_SORT_ORDER' => 'asc',
                            'ELEMENT_SORT_FIELD2' => 'PROPERTY_Naimenovanie',
                            'ELEMENT_SORT_ORDER2' => 'asc',
                            'PROPERTY_CODE' =>
                                array(
                                    'SALE'
                                ),
                            'META_KEYWORDS' => 'UF_KEYWORDS',
                            'META_DESCRIPTION' => 'UF_META_DESCRIPTION',
                            'BROWSER_TITLE' => '-',
                            'INCLUDE_SUBSECTIONS' => 'Y',
                            'BASKET_URL' => '/personal/cart/',
                            'ACTION_VARIABLE' => 'action',
                            'PRODUCT_ID_VARIABLE' => 'id',
                            'SECTION_ID_VARIABLE' => 'SECTION_ID',
                            'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
                            'FILTER_NAME' => 'arrFilterSale',
                            'CACHE_TYPE' => 'Y',
                            'CACHE_TIME' => '36000000',
                            'CACHE_FILTER' => 'N',
                            'CACHE_GROUPS' => 'Y',
                            'SET_TITLE' => 'Y',
                            'SET_STATUS_404' => 'N',
                            'DISPLAY_COMPARE' => 'N',
                            'PAGE_ELEMENT_COUNT' => '35',
                            'LINE_ELEMENT_COUNT' => '1',
                            'PRICE_CODE' =>
                                array(
                                    0 => 'BASE',
                                ),
                            'USE_PRICE_COUNT' => 'N',
                            'SHOW_PRICE_COUNT' => '1',
                            'PRICE_VAT_INCLUDE' => 'Y',
                            'USE_PRODUCT_QUANTITY' => 'Y',
                            'DISPLAY_TOP_PAGER' => 'Y',
                            'DISPLAY_BOTTOM_PAGER' => 'Y',
                            'PAGER_TITLE' => 'Товары',
                            'PAGER_SHOW_ALWAYS' => 'N',
                            'PAGER_TEMPLATE' => 'modern',
                            'PAGER_DESC_NUMBERING' => 'N',
                            'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                            'PAGER_SHOW_ALL' => 'Y',
                            'OFFERS_CART_PROPERTIES' => NULL,
                            'OFFERS_FIELD_CODE' => NULL,
                            'OFFERS_PROPERTY_CODE' => NULL,
                            'OFFERS_SORT_FIELD' => NULL,
                            'OFFERS_SORT_ORDER' => NULL,
                            'OFFERS_LIMIT' => NULL,
                            'SECTION_ID' => '',
                            'SECTION_CODE' => '',
                            'SECTION_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/',
                            'DETAIL_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#',
                            'CONVERT_CURRENCY' => 'N',
                            'CURRENCY_ID' => NULL,
                            'SECTION_USER_FIELDS' =>
                                array(
                                    0 => 'UF_COUNTS',
                                    1 => 'UF_SEE_ALSO',
                                ),
                        )
                    );
                }
            */?>
        </div>-->

        <div class="b-sale">
            <?php
            /**
             * Блок распродажа
             */
            $dbResult = CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID' => 1,
                    '!PROPERTY_SALE' => false
                ],
                false,
                false,
                [
                    'ID', 'PROPERTY_SVOBODNO'
                ]
            );

            $items = [];
            while($arRes = $dbResult->Fetch()){
                if ((int)$arRes['PROPERTY_SVOBODNO_VALUE'] > 0) {
                    $items[] = $arRes['ID'];
                }
            }

                $GLOBALS['arrFilterSale'] = ['ID' => $items]; ?>


                <div class="catalog-block-header catalog-block-header-personal-recomended">
                    <b>Специальные предложения: </b>
                </div>
                <?

                $APPLICATION->IncludeComponent(
                    "bitrix:catalog.section",
                    "sale",
                    array(
                        'IBLOCK_TYPE' => 'content',
                        'IBLOCK_ID' => '1',
                        'ELEMENT_SORT_FIELD' => 'PROPERTY_Naimenovanie',
                        'ELEMENT_SORT_ORDER' => 'asc',
                        'ELEMENT_SORT_FIELD2' => 'PROPERTY_Naimenovanie',
                        'ELEMENT_SORT_ORDER2' => 'asc',
                        'PROPERTY_CODE' =>
                            array(
                                'SALE'
                            ),
                        'META_KEYWORDS' => 'UF_KEYWORDS',
                        'META_DESCRIPTION' => 'UF_META_DESCRIPTION',
                        'BROWSER_TITLE' => '-',
                        'INCLUDE_SUBSECTIONS' => 'Y',
                        'BASKET_URL' => '/personal/cart/',
                        'ACTION_VARIABLE' => 'action',
                        'PRODUCT_ID_VARIABLE' => 'id',
                        'SECTION_ID_VARIABLE' => 'SECTION_ID',
                        'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
                        'FILTER_NAME' => 'arrFilterSale',
                        'CACHE_TYPE' => 'Y',
                        'CACHE_TIME' => '36000000',
                        'CACHE_FILTER' => 'N',
                        'CACHE_GROUPS' => 'Y',
                        'SET_TITLE' => 'Y',
                        'SET_STATUS_404' => 'N',
                        'DISPLAY_COMPARE' => 'N',
                        'PAGE_ELEMENT_COUNT' => '35',
                        'LINE_ELEMENT_COUNT' => '1',
                        'PRICE_CODE' =>
                            array(
                                0 => 'BASE',
                            ),
                        'USE_PRICE_COUNT' => 'N',
                        'SHOW_PRICE_COUNT' => '1',
                        'PRICE_VAT_INCLUDE' => 'Y',
                        'USE_PRODUCT_QUANTITY' => 'Y',
                        'DISPLAY_TOP_PAGER' => 'Y',
                        'DISPLAY_BOTTOM_PAGER' => 'Y',
                        'PAGER_TITLE' => 'Товары',
                        'PAGER_SHOW_ALWAYS' => 'N',
                        'PAGER_TEMPLATE' => 'modern',
                        'PAGER_DESC_NUMBERING' => 'N',
                        'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                        'PAGER_SHOW_ALL' => 'Y',
                        'OFFERS_CART_PROPERTIES' => NULL,
                        'OFFERS_FIELD_CODE' => NULL,
                        'OFFERS_PROPERTY_CODE' => NULL,
                        'OFFERS_SORT_FIELD' => NULL,
                        'OFFERS_SORT_ORDER' => NULL,
                        'OFFERS_LIMIT' => NULL,
                        'SECTION_ID' => '',
                        'SECTION_CODE' => '',
                        'SECTION_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/',
                        'DETAIL_URL' => '/catalog/#SECTION_ID#-#SECTION_CODE#/#ELEMENT_CODE#',
                        'CONVERT_CURRENCY' => 'N',
                        'CURRENCY_ID' => NULL,
                        'SECTION_USER_FIELDS' =>
                            array(
                                0 => 'UF_COUNTS',
                                1 => 'UF_SEE_ALSO',
                            ),
                    ), $component
                );
                ?>
        </div>
    </div>
    <div class="col-md-12 col-lg-9 b-product-ctalogsection">

        <?php if ($arParams['IS_APPLY_FILTER'] != 'Y') {?>

            <?php $APPLICATION->IncludeComponent(
                "profi:catalog.section.list",
                "",
                array(
                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                    "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                    "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                    "COUNT_ELEMENTS" => $arParams["SECTION_COUNT_ELEMENTS"],
                    "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                    //      "FILTER_NAME" =>"arrSectionFilter",
                    "TOP_DEPTH" => 1
                ),
                $component
            ); ?>

        <?php }?>


        <?
        if (count($tabsID) > 0 && $tabsID !== false) {
            $isTabs = true;
        } else {
            $isTabs = false;
        }
        if ($isTabs) {
            //    print_r($tabsID);
            $arSelect = array("ID", "CODE", "PROPERTY_TITLE", "PREVIEW_TEXT", "PROPERTY_PDF");
            $arFilter = array("IBLOCK_ID" => "4", "ACTIVE" => "Y", "ID" => $tabsID);
            $res = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);
            $tabs = array();
            ?>
            <ul class="section-tabs"><?
            ?>
            <li><a id="tab-prices" href="#prices">Цены</a></li><?
            while ($ob = $res->GetNext()) {
                $tabs[] = $ob;
                ?>
                <li><a id="tab-<?= $ob['CODE']; ?>" href="#<?= $ob['CODE']; ?>"><?= $ob['PROPERTY_TITLE_VALUE']; ?></a></li><?
            }
            ?>
            <div class="clear"></div></ul><?
        }
        ?>


        <?php  ?>
        <div class="section-panes">
            <div class="section-pane b-product-items">


                <?php
                $sCurPage = $APPLICATION->GetCurPage();
                /*if (strpos($sCurPage, '/apply/') !== false) {
                    $arParams["ELEMENT_SORT_FIELD"] = 'PROPERTY_ELEMENT_SECTION_NAME';
                }*/

                $arParams["ELEMENT_SORT_FIELD2"] = 'PROPERTY_Naimenovanie';
                $arParams["ELEMENT_SORT_ORDER2"] = $arParams["ELEMENT_SORT_ORDER"];

                $sCurPage = $APPLICATION->GetCurPage();
                if (strpos($sCurPage, '/apply/') !== false) {
                    $arParams["PAGE_ELEMENT_COUNT"] = 9999;
                }

                $APPLICATION->IncludeComponent(
                    "profi:catalog.section",
                    ($template ? 'section_descr' : 'section_changes'),
                    array(
                        "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                        "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                        "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                        "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                        "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                        "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER"],
                        "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],
                        "META_KEYWORDS" => $arParams["LIST_META_KEYWORDS"],
                        "META_DESCRIPTION" => $arParams["LIST_META_DESCRIPTION"],
                        "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                        "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                        "BASKET_URL" => $arParams["BASKET_URL"],
                        "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                        "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                        "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                        "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                        "FILTER_NAME" => $arParams["FILTER_NAME"],
                        "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                        "CACHE_TIME" => $arParams["CACHE_TIME"],
                        "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                        "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                        "SET_TITLE" => $arParams["SET_TITLE"],
                        "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                        "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                        "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                        "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                        "PRICE_CODE" => $arParams["PRICE_CODE"],
                        "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                        "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],

                        "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                        "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],

                        "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                        "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                        "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                        "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                        "PAGER_TEMPLATE" => $arParams["PAGER_TEMPLATE"],
                        "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                        "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                        "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],

                        "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                        "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                        "OFFERS_PROPERTY_CODE" => $arParams["LIST_OFFERS_PROPERTY_CODE"],
                        "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                        "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                        "OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

                        "SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
                        "SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
                        "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                        "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                        'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                        'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                        "SECTION_USER_FIELDS" => array("UF_COUNTS", "UF_SEE_ALSO")
                    ),
                    $component
                );
                ?>
            </div>

            <? if ($isTabs) { ?><?
                foreach ($tabs as $tab) {
                    ?>
                    <div class="section-pane">
                    <div class="section-pane-inner"><?
                        print $tab['PREVIEW_TEXT'];
                        if ($tab['PROPERTY_PDF_VALUE']) {
                            $pdf = CFile::GetPath($tab['PROPERTY_PDF_VALUE']);
                            ?>

                            <iframe id="pdfFrame"
                                    src="https://strprofi.ru<?= $pdf; ?>"
                                    height="1150" style="border: none;"></iframe>

                            <?
                        }
                        ?>
                    </div>
                    </div><?
                }
            } ?>
        </div>


        <div class="b-container-bottom">
            <?php if (ModuleManager::isModuleInstalled("sale")) {
                $arRecomData = array();
                $recomCacheID = array('IBLOCK_ID' => $arParams['IBLOCK_ID']);
                $obCache = new CPHPCache();
                if ($obCache->InitCache(36000, serialize($recomCacheID), "/sale/bestsellers"))
                {
                    $arRecomData = $obCache->GetVars();
                }
                elseif ($obCache->StartDataCache())
                {
                    if (Loader::includeModule("catalog"))
                    {
                        $arSKU = CCatalogSku::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
                        $arRecomData['OFFER_IBLOCK_ID'] = (!empty($arSKU) ? $arSKU['IBLOCK_ID'] : 0);
                    }
                    $obCache->EndDataCache($arRecomData);
                }?>


                <?php
                if (!empty($arRecomData)) {
                    if (!isset($arParams['USE_BIG_DATA']) || $arParams['USE_BIG_DATA'] != 'N') {
                        ?>
                        <div class="col-xs-12 b-container-recomended-products" data-entity="parent-container">
                            <div class="catalog-block-header" data-entity="header" data-showed="false"
                                 style="display: none; opacity: 0;">
                                <b>Персональные рекомендации: </b>
                            </div>
                            <?

                            $APPLICATION->IncludeComponent(
                                "bitrix:catalog.section",
                                "personal_recomended",
                                array(
                                    "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                                    "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                                    "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                                    "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                                    "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                                    "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
                                    "PROPERTY_CODE" => (isset($arParams["LIST_PROPERTY_CODE"]) ? $arParams["LIST_PROPERTY_CODE"] : []),
                                    "PROPERTY_CODE_MOBILE" => $arParams["LIST_PROPERTY_CODE_MOBILE"],
                                    "BROWSER_TITLE" => $arParams["LIST_BROWSER_TITLE"],
                                    "SET_LAST_MODIFIED" => $arParams["SET_LAST_MODIFIED"],
                                    "INCLUDE_SUBSECTIONS" => $arParams["INCLUDE_SUBSECTIONS"],
                                    "BASKET_URL" => $arParams["BASKET_URL"],
                                    "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                                    "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                                    "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                                    "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                                    "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                                    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                                    "CACHE_FILTER" => $arParams["CACHE_FILTER"],
                                    "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                                    "SET_TITLE" => $arParams["SET_TITLE"],
                                    "MESSAGE_404" => $arParams["~MESSAGE_404"],
                                    "SET_STATUS_404" => $arParams["SET_STATUS_404"],
                                    "SHOW_404" => $arParams["SHOW_404"],
                                    "FILE_404" => $arParams["FILE_404"],
                                    "DISPLAY_COMPARE" => $arParams["USE_COMPARE"],
                                    "PAGE_ELEMENT_COUNT" => 0,
                                    "PRICE_CODE" => $arParams["~PRICE_CODE"],
                                    "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                                    "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                                    "HIDE_SECTION_DESCRIPTION" => 'Y',

                                    "PRICE_VAT_INCLUDE" => $arParams["PRICE_VAT_INCLUDE"],
                                    "USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
                                    "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                                    "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                                    "PRODUCT_PROPERTIES" => (isset($arParams["PRODUCT_PROPERTIES"]) ? $arParams["PRODUCT_PROPERTIES"] : []),

                                    "OFFERS_CART_PROPERTIES" => (isset($arParams["OFFERS_CART_PROPERTIES"]) ? $arParams["OFFERS_CART_PROPERTIES"] : []),
                                    "OFFERS_FIELD_CODE" => $arParams["LIST_OFFERS_FIELD_CODE"],
                                    "OFFERS_PROPERTY_CODE" => (isset($arParams["LIST_OFFERS_PROPERTY_CODE"]) ? $arParams["LIST_OFFERS_PROPERTY_CODE"] : []),
                                    "OFFERS_SORT_FIELD" => $arParams["OFFERS_SORT_FIELD"],
                                    "OFFERS_SORT_ORDER" => $arParams["OFFERS_SORT_ORDER"],
                                    "OFFERS_SORT_FIELD2" => $arParams["OFFERS_SORT_FIELD2"],
                                    "OFFERS_SORT_ORDER2" => $arParams["OFFERS_SORT_ORDER2"],
                                    "OFFERS_LIMIT" => (isset($arParams["LIST_OFFERS_LIMIT"]) ? $arParams["LIST_OFFERS_LIMIT"] : 0),

                                    "SECTION_ID" => $intSectionID,
                                    "SECTION_CODE" => '',
                                    "SECTION_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["section"],
                                    "DETAIL_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["element"],
                                    "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                                    'HIDE_NOT_AVAILABLE' => $arParams["HIDE_NOT_AVAILABLE"],
                                    'HIDE_NOT_AVAILABLE_OFFERS' => $arParams["HIDE_NOT_AVAILABLE_OFFERS"],

                                    'LABEL_PROP' => $arParams['LABEL_PROP'],
                                    'LABEL_PROP_MOBILE' => $arParams['LABEL_PROP_MOBILE'],
                                    'LABEL_PROP_POSITION' => $arParams['LABEL_PROP_POSITION'],
                                    'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                                    'PRODUCT_DISPLAY_MODE' => $arParams['PRODUCT_DISPLAY_MODE'],
                                    'PRODUCT_BLOCKS_ORDER' => $arParams['LIST_PRODUCT_BLOCKS_ORDER'],
                                    'PRODUCT_ROW_VARIANTS' => "[{'VARIANT':'3','BIG_DATA':true}]",
                                    'ENLARGE_PRODUCT' => $arParams['LIST_ENLARGE_PRODUCT'],
                                    'ENLARGE_PROP' => isset($arParams['LIST_ENLARGE_PROP']) ? $arParams['LIST_ENLARGE_PROP'] : '',
                                    'SHOW_SLIDER' => 'N',
                                    'SLIDER_INTERVAL' => isset($arParams['LIST_SLIDER_INTERVAL']) ? $arParams['LIST_SLIDER_INTERVAL'] : '',
                                    'SLIDER_PROGRESS' => isset($arParams['LIST_SLIDER_PROGRESS']) ? $arParams['LIST_SLIDER_PROGRESS'] : '',

                                    "DISPLAY_TOP_PAGER" => 'N',
                                    "DISPLAY_BOTTOM_PAGER" => 'N',

                                    "RCM_TYPE" => isset($arParams['BIG_DATA_RCM_TYPE']) ? $arParams['BIG_DATA_RCM_TYPE'] : '',
                                    "SHOW_FROM_SECTION" => 'Y',

                                    'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                                    'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : []),
                                    'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                                    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                                    'DISCOUNT_PERCENT_POSITION' => $arParams['DISCOUNT_PERCENT_POSITION'],
                                    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                                    'SHOW_MAX_QUANTITY' => $arParams['SHOW_MAX_QUANTITY'],
                                    'MESS_SHOW_MAX_QUANTITY' => (isset($arParams['~MESS_SHOW_MAX_QUANTITY']) ? $arParams['~MESS_SHOW_MAX_QUANTITY'] : ''),
                                    'RELATIVE_QUANTITY_FACTOR' => (isset($arParams['RELATIVE_QUANTITY_FACTOR']) ? $arParams['RELATIVE_QUANTITY_FACTOR'] : ''),
                                    'MESS_RELATIVE_QUANTITY_MANY' => (isset($arParams['~MESS_RELATIVE_QUANTITY_MANY']) ? $arParams['~MESS_RELATIVE_QUANTITY_MANY'] : ''),
                                    'MESS_RELATIVE_QUANTITY_FEW' => (isset($arParams['~MESS_RELATIVE_QUANTITY_FEW']) ? $arParams['~MESS_RELATIVE_QUANTITY_FEW'] : ''),
                                    'MESS_BTN_BUY' => (isset($arParams['~MESS_BTN_BUY']) ? $arParams['~MESS_BTN_BUY'] : ''),
                                    'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['~MESS_BTN_ADD_TO_BASKET']) ? $arParams['~MESS_BTN_ADD_TO_BASKET'] : ''),
                                    'MESS_BTN_SUBSCRIBE' => (isset($arParams['~MESS_BTN_SUBSCRIBE']) ? $arParams['~MESS_BTN_SUBSCRIBE'] : ''),
                                    'MESS_BTN_DETAIL' => (isset($arParams['~MESS_BTN_DETAIL']) ? $arParams['~MESS_BTN_DETAIL'] : ''),
                                    'MESS_NOT_AVAILABLE' => (isset($arParams['~MESS_NOT_AVAILABLE']) ? $arParams['~MESS_NOT_AVAILABLE'] : ''),
                                    'MESS_BTN_COMPARE' => (isset($arParams['~MESS_BTN_COMPARE']) ? $arParams['~MESS_BTN_COMPARE'] : ''),

                                    'USE_ENHANCED_ECOMMERCE' => (isset($arParams['USE_ENHANCED_ECOMMERCE']) ? $arParams['USE_ENHANCED_ECOMMERCE'] : ''),
                                    'DATA_LAYER_NAME' => (isset($arParams['DATA_LAYER_NAME']) ? $arParams['DATA_LAYER_NAME'] : ''),
                                    'BRAND_PROPERTY' => (isset($arParams['BRAND_PROPERTY']) ? $arParams['BRAND_PROPERTY'] : ''),

                                    'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
                                    'ADD_TO_BASKET_ACTION' => $basketAction,
                                    'SHOW_CLOSE_POPUP' => isset($arParams['COMMON_SHOW_CLOSE_POPUP']) ? $arParams['COMMON_SHOW_CLOSE_POPUP'] : '',
                                    'COMPARE_PATH' => $arResult['FOLDER'] . $arResult['URL_TEMPLATES']['compare'],
                                    'COMPARE_NAME' => $arParams['COMPARE_NAME'],
                                    'USE_COMPARE_LIST' => 'Y',
                                    'BACKGROUND_IMAGE' => '',
                                    'DISABLE_INIT_JS_IN_COMPONENT' => (isset($arParams['DISABLE_INIT_JS_IN_COMPONENT']) ? $arParams['DISABLE_INIT_JS_IN_COMPONENT'] : ''),
                                    'SET_META_KEYWORDS' => 'N',
                                    'SET_META_DESCRIPTION' => 'N',
                                ),
                                $component
                            );
                            ?>
                        </div>
                        <?php
                    }
                }
            } ?>
        </div>
    </div>
</div>




