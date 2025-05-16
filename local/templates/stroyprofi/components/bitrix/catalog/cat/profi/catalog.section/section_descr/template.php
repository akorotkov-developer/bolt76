<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
    <?= $arResult["NAV_STRING"] ?><br/>
<? endif; ?>
<?
if (sizeof($arResult["ITEMS"]) > 0) {?>
    <?php
    /** Сортировка */
    @require $_SERVER['DOCUMENT_ROOT'] . '/local/include_file/sort.php';
    ?>

    <div class="catalog_element 123">
        <form action="/cart/add_to_cart.php" method="post" class="order_form">
            <table class="full element_table">
                <thead>
                <tr>
                    <td class="first-td first-td-withoutimage"></td>
                    <td class="art">Арт</td>
                    <td class="nopadding-i"></td>
                    <td class="name">Наименование</td>
                    <td class="opt">Опт</td>

                    <?php if (!$arResult['IS_OPT_2'] && !$arResult['IS_OPT_3']) { ?>
                        <td class="roz">Розница</td>
                    <?php } ?>

                    <td class="upak upak-head"><span>В упаковке</span></td>
                    <td class="avail">Наличие</td>
                    <td class="buy">Купить</td>
                    <td class="mera">Ед</td>
                    <td></td>
                    <td class="td-favorite"></td>
                </tr>
                </thead>
                <tbody>
                <?php
                $isSectionNameWrited = false;
                foreach ($arResult["ITEMS"] as $cell => $arElement):

                $saleClass = (in_array($arElement['ID'], $arResult['SALE_ITEMS'])) ? 'sale' : '';

                if ($arResult['IS_FILTER']) {
                    $sCurSectionNameForFilter = $arElement['FILTER_SECTION_NAME'];
                }

                $this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
                $this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

                $up1 = (float)$arElement["PROPERTIES"]["UPAKOVKA"]["VALUE"];
                $up2 = (float)$arElement["PROPERTIES"]["UPAKOVKA2"]["VALUE"];
                $countTips = array();
                if ($up1 && !$up2) {
                    $countTips[] = $up1;
                    $countTips[] = $up1 * 10;
                    $countTips[] = $up1 * 100;
                } elseif ($up2 < $up1) {
                    $countTips[] = $up2;
                    $countTips[] = $up1;
                } elseif (($up1 < $up2) && ($up2 <= 10 * $up1)) {
                    $countTips[] = $up1;
                    $countTips[] = $up2;
                } elseif (($up1 < $up2) && ($up2 >= 10 * $up1) && ($up2 <= 100 * $up1)) {
                    $countTips[] = $up1;
                    $countTips[] = $up1 * 10;
                    $countTips[] = $up2;
                } elseif (($up1 < $up2) && ($up2 >= 100 * $up1)) {
                    $countTips[] = $up1;
                    $countTips[] = $up1 * 10;
                    $countTips[] = $up1 * 100;
                    $countTips[] = $up2;
                }

                $arElement["NAME"] = ($arElement["NAME"] == "-" ? $arElement["PROPERTIES"]["NAIMENOVANIE"]["VALUE"] : $arElement["NAME"]);
                ?>

                <?php
                if ($arResult['IS_FILTER']) {

                // Поставим заголовок для товаров в случае фильтрации, если он еще не был записан
                if ($arResult['IS_FILTER'] && !$isSectionNameWrited) { ?>
                </tbody>
            </table>
            <table class="full element_table">
                <tbody>
                <tr>
                    <td rowspan="0" class="section_description">

                        <?php
                        if ($arElement['PICTURE']) { ?>

                            <a href="<?= $arElement["PICTURE_BIG"]['src'] ?>" class="fancybox"><img
                                        src="<?= $arElement["PICTURE"]['src'] ?>"
                                        alt=""></a>

                        <?php } ?>
                        <div class="description"><?/*= $arResult["DESCRIPTION"]*/ ?></div>
                        <div class="clear"></div>
                    </td>

                    <td colspan="11">
                        <span class="filter_section_title"><?= $sCurSectionNameForFilter ?></span>
                    </td>
                </tr>
                <?php
                $isSectionNameWrited = true;
                }

                // Проверяем, если у следующего элемента отличается заголовок, то зададим переменной
                // заголовка новое значение
                if (!empty($arResult['ITEMS'][$cell + 1]) &&
                    $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'] != $sCurSectionNameForFilter &&
                    $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'] != '') {

                    $sCurSectionNameForFilter = $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'];
                    $isSectionNameWrited = false;
                }
                ?>

                <tr id="<?= $this->GetEditAreaId($arElement['ID']); ?>" data-elementid="<?= $arElement['ID'] ?>"
                    class="element_product_tr <?= ((float)$arElement["PROPERTIES"]["Ostatok"]["VALUE"] > 0 ? 'available' : 'not-available') ?> row<?= ($cell % 2); ?>">

                    <td class="art <?= $cell ?>">
                        <div class="name-holder">
                            <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>" class="no_underline">
                                <span><?= $arElement["DISPLAY_PROPERTIES"]["ARTICUL"]["VALUE"] ?></span>
                            </a>
                        </div>

                    </td>
                    <?
                    $mera = $arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"];
                    $ves = (float)$arElement["PROPERTIES"]["VES1000PS"]["VALUE"];
                    if ($mera == "кг") {
                        $k = round(1000 / $ves, 5);
                        $k_val = "шт";
                        $e = 'руб/шт';
                    } elseif ($mera == "шт") {
                        $k = round($ves / 1000, 5);
                        $k_val = "кг";
                        $e = 'руб/кг';
                    } elseif ($mera == "тыс. шт") {
                        $k = round($ves, 2);

                        $k_val = "кг";
                        $e = 'руб/кг';
                    }

                    if ($arResult['IS_OPT_2']) {
                        $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                    } elseif ($arResult['IS_OPT_3']) {
                        $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT2"]["VALUE"];
                    }

                    $ok = number_format(round($sPrice / $k, 2), 2, ', ', ' ');
                    $rk = number_format(round($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] / $k, 2), 2, ', ', ' ');
                    ?>
                    <td class="nopadding-i">
                        <? if ($arElement["DETAIL_TEXT"]) { ?><a href="#" class="">
                            <img src="/images/i.png" alt="Информация"/></a><? } ?>
                    </td>
                    <td class="name t-name-relative <?= $saleClass?>" <?php if ($arResult['IS_FILTER']) {
                        echo 'style="width: 100%"';
                    } ?>>
                        <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>" class="no_underline">
                            <div class="name_wrapper">

                                <div class="name-holder">
                                    <span><?= $arElement["NAME"] ?></span>
                                </div>
                                <div class="description_holder" id="detailed_<?= $arElement["ID"] ?>">
                                    <h3><?= $arElement["PROPERTIES"]["NAIMENOVANIE"]["VALUE"] ?></h3>
                                    <table class="full">
                                        <tr>
                                            <? if ($arElement["PREVIEW_PICTURE"] || $arResult["PICTURE"]) { ?>
                                                <td class="picture">
                                                    <?
                                                    if ($arElement["PREVIEW_PICTURE"]) {
                                                        $file = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 250, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                        $file_big = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                    } else {
                                                        $file = CFile::ResizeImageGet($arResult["PICTURE"], array('width' => 250, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                        $file_big = CFile::ResizeImageGet($arElement["PICTURE"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                    }

                                                    ?>
                                                    <a href="<?= $file_big["src"] ?>" class="fancybox"><img
                                                                src="<?= $file["src"] ?>"
                                                                alt="<?= $arElement['NAME']; ?>"></a>
                                                </td>
                                            <? } ?>
                                            <td class="properties">
                                                <div class="buy">

                                                    <div class="buy_helper_holder">
                                                        <div class="buy_helper">
                                                            <? if ($ves) { ?>
                                                                <div class="vesHelperHolder">
                                                                <div class="vesHelper" data-val='<?= $k_val; ?>'
                                                                     data-k="<?= $k; ?>">
                                                                    0 <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                                                </div></div><? } ?>
                                                            <div class="input_holder">
                                                                <input type="text" class="quantity_input"
                                                                       name="ITEM[<?= $arElement["ID"] ?>]"
                                                                       data-price="<?= $arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] ?>"
                                                                       data-ratio="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 'NaN' ?>"
                                                                       value="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? (float) $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?>"
                                                                >
                                                                <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                                                    <span class="hint_min">мин. <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?></span>
                                                                <?php } ?>
                                                            </div>
                                                            <? if (count($countTips)) { ?>
                                                                <div class="help_values">
                                                                    <? foreach ($countTips as $c) { ?>
                                                                        <a href="#"
                                                                           data-val="<?= $c ?>">+<span><?= $c ?> <?= $mera; ?></span></a>
                                                                    <? } ?>
                                                                    <div class="clear"></div>
                                                                </div>
                                                            <? } ?>

                                                            <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                                                <div class="kratnostHelperHolder">
                                                                    <div class="kratnostHelper">
                                                                        Данная продукция отпускается
                                                                        кртано <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?> <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                                                    </div>
                                                                </div>
                                                            <?php } ?>
                                                        </div>
                                                        <a href="#" class="add_to_cart_one one"><img
                                                                    src="/img/cart_buttton.png"
                                                                    alt=""></a>
                                                    </div>
                                                </div>

                                                <table class="full">
                                                    <tr>
                                                        <td class="prop_name">Артикул:</td>
                                                        <td class="prop_value"><?= $arElement["PROPERTIES"]["ARTICUL"]["VALUE"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="prop_name">Единицы:</td>
                                                        <td class="prop_value"><?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="prop_name">Наличие:</td>
                                                        <td class="prop_value"><?= (float)$arElement["PROPERTIES"]["Ostatok"]["VALUE"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="prop_name">Розничная цена:</td>
                                                        <td class="prop_value"><?= $arElement["PROPERTIES"]["PRICE"]["VALUE"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="prop_name">Оптовая цена:</td>
                                                        <td class="prop_value"><?= $arElement["PROPERTIES"]["PRICE_OPT"]["VALUE"] ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td class="prop_name">В упаковке:</td>
                                                        <td class="prop_value"><?= $up1; ?><?= ($up2 ? '/' . $up2 : '') ?></td>
                                                    </tr>
                                                    <? if ($arElement["PROPERTIES"]["VES"]["VALUE"]) {
                                                        ?>
                                                        <tr>
                                                            <td class="prop_name">Вес 1000 шт.:</td>
                                                            <td class="prop_value"><?= $arElement["PROPERTIES"]["VES"]["VALUE"]; ?>
                                                                кг.
                                                            </td>
                                                        </tr>
                                                    <? } ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <div class="text">
                                                    <? if ($arElement["PREVIEW_TEXT"] != '') { ?>
                                                        <?= ($arElement["PREVIEW_TEXT"]) ?>
                                                    <? } elseif ($arResult["DESCRIPTION"] != '') { ?>
                                                        <!--noindex--> <?= ($arResult["DESCRIPTION"]) ?><!--/noindex-->
                                                    <? } ?>
                                                </div>
                                                <? if ($arElement['DETAIL_TEXT']) { ?>
                                                    <div class="detail_link"><a
                                                                href="<?= $arElement['DETAIL_PAGE_URL'] ?>">подробнее</a>
                                                    </div>
                                                    <div class="description_holder"
                                                         id="detailed_text_<?= $arElement["ID"] ?>">
                                                        <?= $arElement['DETAIL_TEXT']; ?>
                                                    </div>
                                                <? } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </a>

                        <?php

                            if ($arElement['PROPERTIES']['SALE']['VALUE'] != '') {?>
                                <img class="i-sale-shildik" src="<?=SITE_TEMPLATE_PATH?>/images/m_sale.png">
                                <?php
                            }
                        ?>
                    </td>
                    <td class="opt">
                        <?php
                        if ($arResult['IS_OPT_2']) {
                            $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                        } elseif ($arResult['IS_OPT_3']) {
                            $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT2"]["VALUE"];
                        } else {
                            $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                        }
                        ?>
                        <div class="buy_helper_holder price">
                            <? if ($ves) { ?>
                                <div class="vesHelperHolder">
                                    <div class="vesHelper"
                                         data-price="<?= coolPrice($sPrice) ?>">
                                        ~ <?= $ok ?> <?= $e ?></div>
                                </div>
                            <? } ?>
                            <?= coolPrice($sPrice) ?>
                        </div>
                    </td>
                    <?php if (!$arResult['IS_OPT_2'] && !$arResult['IS_OPT_3']) { ?>
                        <td class="roz">
                            <div class="buy_helper_holder price">
                                <? if ($ves) { ?>
                                    <div class="vesHelperHolder">
                                        <div class="vesHelper"
                                             data-price="<?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]) ?>">
                                            ~ <?= $rk ?> <?= $e ?></div>
                                    </div>
                                <? } ?>
                                <?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]) ?>
                        </td>
                    <?php } ?>

                    <td class="upak"><?= $up1; ?><?= ($up2 ? '/' . $up2 : '') ?></td>
                    <td class="avail"><? if ((float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] > 0) {
                            echo 'В наличии';
                        } else {
                            if ($arElement["PROPERTIES"]["TipSkladskogoZapasa"]["VALUE"] == 'Обязательный ассортимент' &&
                                (float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] <= 0) {
                                echo 'Временно отсутствует';
                            } else {
                                echo 'Под заказ';
                            }
                        } ?></td>
                    <td class="buy">
                        <div class="buy_helper_holder">

                            <div class="buy_helper">
                                <? if ($ves) { ?>
                                    <div class="vesHelperHolder">
                                    <div class="vesHelper" data-val='<?= $k_val; ?>' data-k="<?= $k; ?>"
                                         data-num="0">0 <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?></div>
                                    </div><? } ?>
                                <div class="input_holder">
                                    <input type="text" class="quantity_input" name="ITEM[<?= $arElement["ID"] ?>]"
                                           data-price="<?= $arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] ?>"
                                           data-ratio="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 'NaN' ?>"
                                           value="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? (float) $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?>"
                                    >
                                    <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                        <span class="hint_min">мин. <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?></span>
                                    <?php } ?>
                                </div>
                                <? if (count($countTips)) { ?>
                                    <div class="help_values">
                                        <? foreach ($countTips as $c) { ?>
                                            <a href="#"
                                               data-val="<?= $c ?>">+<span><?= $c ?> <?= $mera; ?></span></a>
                                        <? } ?>
                                        <div class="clear"></div>
                                    </div>
                                <? } ?>

                                <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                    <div class="kratnostHelperHolder">
                                        <div class="kratnostHelper">
                                            Данная продукция отпускается
                                            кртано <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?> <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                    <td class="mera"><?= $mera ?></td>
                    <td class="cart_td" <?php if ($arResult['IS_FILTER']) {
                        echo 'colspan="2"';
                    } ?>><a href="#" class="add_to_cart_one"><img src="/img/cart_buttton.png" alt=""></a>
                    </td>

                    <td class="td-favorite">
                        <div class="b-control-elements-in-table">
                            <div class="b-compare">
                                <svg data-product-id="<?= $arElement['ID'] ?>" id="compare-icon"
                                     class="compare-svg-icon-element-detail compare-svg-icon-element-list" width="100%"
                                     height="100%" viewBox="0 0 18 16">
                                    <path fill-rule="evenodd"
                                          clip-rule="evenodd"
                                          d="M13.4993 12.0003V11.125C13.4993 10.5036 14.0031 9.99985 14.6245 9.99985C15.2459 9.99985 15.7497 10.5036 15.7497 11.125V12.0003H17.0004C17.5525 12.0003 18 12.4478 18 12.9999C18 13.552 17.5525 13.9995 17.0004 13.9995H15.7497V14.8748C15.7497 15.4962 15.2459 16 14.6245 16C14.0031 16 13.4993 15.4962 13.4993 14.8748V13.9995H12.25C11.6979 13.9995 11.2503 13.552 11.2503 12.9999C11.2503 12.4478 11.6979 12.0003 12.25 12.0003H13.4993ZM0 12.9999C0 12.4478 0.447548 12.0003 0.999626 12.0003H8.00037C8.55245 12.0003 9 12.4478 9 12.9999C9 13.552 8.55245 13.9995 8.00037 13.9995H0.999626C0.447548 13.9995 0 13.552 0 12.9999ZM0 0.999625C0 0.447547 0.447548 0 0.999626 0H17.0004C17.5525 0 18 0.447547 18 0.999625C18 1.5517 17.5525 1.99925 17.0004 1.99925H0.999626C0.447548 1.99925 0 1.5517 0 0.999625ZM0 6.99977C0 6.4477 0.447548 6.00015 0.999626 6.00015H17.0004C17.5525 6.00015 18 6.4477 18 6.99977C18 7.55185 17.5525 7.9994 17.0004 7.9994H0.999626C0.447548 7.9994 0 7.55185 0 6.99977Z"
                                    />
                                </svg>
                            </div>

                            <div class="b-favorite">
                                <svg data-product-id="<?= $arElement['ID'] ?>"
                                     class="favorite-svg-icon <?= $arElement['IS_FAVORITE'] ? 'active' : '' ?>"
                                     title="Добавить в избранное" width="31" height="31" viewBox="0 0 24 24" fill="none"
                                     stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                </svg>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php } else { ?>
                    <?php
                    // Проверяем, если у следующего элемента отличается заголовок, то зададим переменной
                    // заголовка новое значение
                    if (!empty($arResult['ITEMS'][$cell + 1]) &&
                        $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'] != $sCurSectionNameForFilter &&
                        $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'] != '') {

                        $sCurSectionNameForFilter = $arResult['ITEMS'][$cell + 1]['FILTER_SECTION_NAME'];
                    }
                    ?>

                    <tr id="<?= $this->GetEditAreaId($arElement['ID']); ?>" data-elementid="<?= $arElement['ID'] ?>"
                        class="element_product_tr <?= ((float)$arElement["PROPERTIES"]["Ostatok"]["VALUE"] > 0 ? 'available' : 'not-available') ?> row<?= ($cell % 2); ?>">
                        <?php

                        if ($cell == 0) { ?>
                            <td rowspan="<?= (sizeof($arResult["ITEMS"]) + 1 + sizeof($arResult['SECTIONS_COUNT']) + 1) ?>"
                                class="section_description">

                                <?
                                if ($arResult["PICTURE"]["ID"]) {
                                    $file = CFile::ResizeImageGet($arResult["PICTURE"]["ID"], array('width' => 128, 'height' => 130), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                    $file_big = CFile::ResizeImageGet($arResult["PICTURE"]["ID"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                    ?>

                                    <a href="<?= $file["src"] ?>" class="fancybox" data-fancybox><img src="<?= $file["src"] ?>"
                                                                                        alt=""></a>

                                <? } ?>
                                <div class="description"><? /*= $arResult["DESCRIPTION"]*/ ?></div>
                                <div class="clear"></div>
                            </td>
                        <? } ?>
                        <td class="art <?= $cell ?>">
                            <div class="name-holder">
                                <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>" class="no_underline">
                                    <span><?= $arElement["DISPLAY_PROPERTIES"]["ARTICUL"]["VALUE"] ?></span>
                                </a>
                            </div>

                        </td>
                        <?
                        $mera = $arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"];
                        $ves = (float)$arElement["PROPERTIES"]["VES1000PS"]["VALUE"];
                        if ($mera == "кг") {
                            $k = round(1000 / $ves, 5);
                            $k_val = "шт";
                            $e = 'руб/шт';
                        } elseif ($mera == "шт") {
                            $k = round($ves / 1000, 5);
                            $k_val = "кг";
                            $e = 'руб/кг';
                        } elseif ($mera == "тыс. шт") {
                            $k = round($ves, 2);

                            $k_val = "кг";
                            $e = 'руб/кг';
                        }

                        if ($arResult['IS_OPT_2']) {
                            $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                        } elseif ($arResult['IS_OPT_3']) {
                            $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT2"]["VALUE"];
                        }

                        $ok = number_format(round($sPrice / $k, 2), 2, ', ', ' ');
                        $rk = number_format(round($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] / $k, 2), 2, ', ', ' ');
                        ?>
                        <td class="nopadding-i">
                            <? if ($arElement["DETAIL_TEXT"]) { ?><a href="#" class="">
                                <img src="/images/i.png" alt="Информация"/></a><? } ?>
                        </td>
                        <td class="name <?= $saleClass?>" <?php if ($arResult['IS_FILTER']) {
                            echo 'style="width: 100%"';
                        } ?>>
                            <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>" class="no_underline">
                                <div class="name_wrapper">

                                    <div class="name-holder">
                                        <span><?= $arElement["NAME"] ?></span>
                                    </div>
                                    <div class="description_holder" id="detailed_<?= $arElement["ID"] ?>">
                                        <h3><?= $arElement["PROPERTIES"]["NAIMENOVANIE"]["VALUE"] ?></h3>
                                        <table class="full">
                                            <tr>
                                                <? if ($arElement["PREVIEW_PICTURE"] || $arResult["PICTURE"]) { ?>
                                                    <td class="picture">
                                                        <?
                                                        if ($arElement["PREVIEW_PICTURE"]) {
                                                            $file = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 250, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                            $file_big = CFile::ResizeImageGet($arElement["PREVIEW_PICTURE"]["ID"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                        } else {
                                                            $file = CFile::ResizeImageGet($arResult["PICTURE"], array('width' => 250, 'height' => 250), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                            $file_big = CFile::ResizeImageGet($arElement["PICTURE"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                                        }

                                                        ?>
                                                        <a href="<?= $file_big["src"] ?>" class="fancybox"><img
                                                                    src="<?= $file["src"] ?>"
                                                                    alt="<?= $arElement['NAME']; ?>"></a>
                                                    </td>
                                                <? } ?>
                                                <td class="properties">
                                                    <div class="buy">

                                                        <div class="buy_helper_holder">
                                                            <div class="buy_helper">
                                                                <? if ($ves) { ?>
                                                                    <div class="vesHelperHolder">
                                                                    <div class="vesHelper" data-val='<?= $k_val; ?>'
                                                                         data-k="<?= $k; ?>">
                                                                        0 <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                                                    </div></div><? } ?>
                                                                <div class="input_holder">
                                                                    <input type="text" class="quantity_input"
                                                                           name="ITEM[<?= $arElement["ID"] ?>]"
                                                                           data-price="<?= $arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] ?>"
                                                                           data-ratio="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 'NaN' ?>"
                                                                           value="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? (float) $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?>"
                                                                    >
                                                                    <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                                                        <span class="hint_min">мин. <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?></span>
                                                                    <?php } ?>
                                                                </div>
                                                                <? if (count($countTips)) { ?>
                                                                    <div class="help_values">
                                                                        <? foreach ($countTips as $c) { ?>
                                                                            <a href="#"
                                                                               data-val="<?= $c ?>">+<span><?= $c ?> <?= $mera; ?></span></a>
                                                                        <? } ?>
                                                                        <div class="clear"></div>
                                                                    </div>
                                                                <? } ?>

                                                                <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                                                    <div class="kratnostHelperHolder">
                                                                        <div class="kratnostHelper">
                                                                            Данная продукция отпускается
                                                                            кртано <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?> <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                                                        </div>
                                                                    </div>
                                                                <?php } ?>
                                                            </div>
                                                            <a href="#" class="add_to_cart_one one"><img
                                                                        src="/img/cart_buttton.png"
                                                                        alt=""></a>
                                                        </div>
                                                    </div>

                                                    <table class="full">
                                                        <tr>
                                                            <td class="prop_name">Артикул:</td>
                                                            <td class="prop_value"><?= $arElement["PROPERTIES"]["ARTICUL"]["VALUE"] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="prop_name">Единицы:</td>
                                                            <td class="prop_value"><?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="prop_name">Наличие:</td>
                                                            <td class="prop_value"><?= (float)$arElement["PROPERTIES"]["Ostatok"]["VALUE"] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="prop_name">Розничная цена:</td>
                                                            <td class="prop_value"><?= $arElement["PROPERTIES"]["PRICE"]["VALUE"] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="prop_name">Оптовая цена:</td>
                                                            <td class="prop_value"><?= $arElement["PROPERTIES"]["PRICE_OPT"]["VALUE"] ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="prop_name">В упаковке:</td>
                                                            <td class="prop_value"><?= $up1; ?><?= ($up2 ? '/' . $up2 : '') ?></td>
                                                        </tr>
                                                        <? if ($arElement["PROPERTIES"]["VES"]["VALUE"]) {
                                                            ?>
                                                            <tr>
                                                                <td class="prop_name">Вес 1000 шт.:</td>
                                                                <td class="prop_value"><?= $arElement["PROPERTIES"]["VES"]["VALUE"]; ?>
                                                                    кг.
                                                                </td>
                                                            </tr>
                                                        <? } ?>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="text">
                                                        <? if ($arElement["PREVIEW_TEXT"] != '') { ?>
                                                            <?= ($arElement["PREVIEW_TEXT"]) ?>
                                                        <? } elseif ($arResult["DESCRIPTION"] != '') { ?>
                                                            <!--noindex--> <?= ($arResult["DESCRIPTION"]) ?><!--/noindex-->
                                                        <? } ?>
                                                    </div>
                                                    <? if ($arElement['DETAIL_TEXT']) { ?>
                                                        <div class="detail_link"><a
                                                                    href="<?= $arElement['DETAIL_PAGE_URL'] ?>">подробнее</a>
                                                        </div>
                                                        <div class="description_holder"
                                                             id="detailed_text_<?= $arElement["ID"] ?>">
                                                            <?= $arElement['DETAIL_TEXT']; ?>
                                                        </div>
                                                    <? } ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </a>

                            <?php

                                if ($arElement['PROPERTIES']['SALE']['VALUE'] != '') {?>
                                    <img class="i-sale-shildik" src="<?=SITE_TEMPLATE_PATH?>/images/m_sale.png">
                                    <?php
                                }
                            ?>
                        </td>
                        <td class="opt">
                            <?php
                            if ($arResult['IS_OPT_2']) {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                            } elseif ($arResult['IS_OPT_3']) {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT2"]["VALUE"];
                            } else {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                            }
                            ?>
                            <div class="buy_helper_holder price">
                                <? if ($ves) { ?>
                                    <div class="vesHelperHolder">
                                        <div class="vesHelper"
                                             data-price="<?= coolPrice($sPrice) ?>">
                                            ~ <?= $ok ?> <?= $e ?></div>
                                    </div>
                                <? } ?>
                                <?= coolPrice($sPrice) ?>
                            </div>
                        </td>

                        <?php if (!$arResult['IS_OPT_2'] && !$arResult['IS_OPT_3']) { ?>
                            <td class="roz">
                                <div class="buy_helper_holder price">
                                    <? if ($ves) { ?>
                                        <div class="vesHelperHolder">
                                            <div class="vesHelper"
                                                 data-price="<?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]) ?>">
                                                ~ <?= $rk ?> <?= $e ?></div>
                                        </div>
                                    <? } ?>
                                    <?= coolPrice($arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"]) ?>
                            </td>
                        <?php } ?>

                        <td class="upak"><?= $up1; ?><?= ($up2 ? '/' . $up2 : '') ?></td>
                        <td class="avail"><? if ((float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] > 0) {
                                echo 'В наличии';
                            } else {
                                if ($arElement["PROPERTIES"]["TipSkladskogoZapasa"]["VALUE"] == 'Обязательный ассортимент' &&
                                    (float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] <= 0) {
                                    echo 'Временно отсутствует';
                                } else {
                                    echo 'Под заказ';
                                }
                            } ?></td>
                        <td class="buy">
                            <div class="buy_helper_holder">

                                <div class="buy_helper buy_helper-flex">
                                    <? if ($ves) { ?>
                                        <div class="vesHelperHolder">
                                        <div class="vesHelper" data-val='<?= $k_val; ?>' data-k="<?= $k; ?>"
                                             data-num="0">0 <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?></div>
                                        </div>
                                    <? } ?>

                                    <span class="product-item-amount-field-btn-minus no-select product-item-amount-count s-span-minus-button"></span>
                                    <div class="input_holder">
                                        <input type="text" class="quantity_input" name="ITEM[<?= $arElement["ID"] ?>]"
                                               data-price="<?= $arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] ?>"
                                               data-ratio="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 'NaN' ?>"
                                               value="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? (float) $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?>"
                                        >
                                        <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                            <span class="hint_min">мин. <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?></span>
                                        <?php } ?>
                                    </div>
                                    <span class="product-item-amount-field-btn-plus no-select product-item-amount-count s-span-plus-button"></span>


                                    <? if (count($countTips)) { ?>
                                        <div class="help_values">
                                            <? foreach ($countTips as $c) { ?>
                                                <a href="#"
                                                   data-val="<?= $c ?>">+<span><?= $c ?> <?= $mera; ?></span></a>
                                            <? } ?>
                                            <div class="clear"></div>
                                        </div>
                                    <? } ?>

                                    <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                        <div class="kratnostHelperHolder">
                                            <div class="kratnostHelper">
                                                Данная продукция отпускается
                                                кртано <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?> <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </td>

                        <td class="mera"><?= $mera ?></td>

                        <td class="cart_td" <?php if ($arResult['IS_FILTER']) {
                                echo 'colspan="2"';
                            } ?>>

                            <input class="btn btn-themes btn-list-add-to-cart" type="button" name="add_to_basket" value="В корзину" data-elementid="<?= $arElement['ID']?>">
                        </td>

                        <td class="td-favorite">
                            <div class="b-control-elements-in-table">
                                <div class="b-compare">
                                    <svg data-product-id="<?= $arElement['ID'] ?>" id="compare-icon"
                                         class="compare-svg-icon-element-detail compare-svg-icon-element-list" width="100%"
                                         height="100%" viewBox="0 0 18 16">
                                        <path fill-rule="evenodd"
                                              clip-rule="evenodd"
                                              d="M13.4993 12.0003V11.125C13.4993 10.5036 14.0031 9.99985 14.6245 9.99985C15.2459 9.99985 15.7497 10.5036 15.7497 11.125V12.0003H17.0004C17.5525 12.0003 18 12.4478 18 12.9999C18 13.552 17.5525 13.9995 17.0004 13.9995H15.7497V14.8748C15.7497 15.4962 15.2459 16 14.6245 16C14.0031 16 13.4993 15.4962 13.4993 14.8748V13.9995H12.25C11.6979 13.9995 11.2503 13.552 11.2503 12.9999C11.2503 12.4478 11.6979 12.0003 12.25 12.0003H13.4993ZM0 12.9999C0 12.4478 0.447548 12.0003 0.999626 12.0003H8.00037C8.55245 12.0003 9 12.4478 9 12.9999C9 13.552 8.55245 13.9995 8.00037 13.9995H0.999626C0.447548 13.9995 0 13.552 0 12.9999ZM0 0.999625C0 0.447547 0.447548 0 0.999626 0H17.0004C17.5525 0 18 0.447547 18 0.999625C18 1.5517 17.5525 1.99925 17.0004 1.99925H0.999626C0.447548 1.99925 0 1.5517 0 0.999625ZM0 6.99977C0 6.4477 0.447548 6.00015 0.999626 6.00015H17.0004C17.5525 6.00015 18 6.4477 18 6.99977C18 7.55185 17.5525 7.9994 17.0004 7.9994H0.999626C0.447548 7.9994 0 7.55185 0 6.99977Z"
                                        />
                                    </svg>
                                </div>

                                <div class="b-favorite">
                                    <svg data-product-id="<?= $arElement['ID'] ?>"
                                         class="favorite-svg-icon <?= $arElement['IS_FAVORITE'] ? 'active' : '' ?>"
                                         title="Добавить в избранное" width="31" height="31" viewBox="0 0 24 24" fill="none"
                                         stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php }
                endforeach; // foreach($arResult["ITEMS"] as $arElement):?>
                <tr></tr>
                </tbody>
            </table>

            <div class="order">
                <!--<div class="order_button">
                    <a href="/personal/cart/" onclick="$(this).closest('form').submit(); return false; " class="link_like_button">В
                        корзину</a>
                </div>-->
                <div class="order_precount"></div>
                <div class="clear"></div>
            </div>
        </form>
    </div>
    <?
    if (sizeof($arResult["UF_SEE_ALSO"]) > 0 && is_array($arResult["UF_SEE_ALSO"])) {
        global $arrFilter;
        $arrFilter = array("!UF_PRODUCT" => false, "SECTION_ID" => $arResult["UF_SEE_ALSO"], "!PICTURE" => false);
        ?>
        <? $APPLICATION->IncludeComponent(
            "profi:catalog.section.list",
            "see_also",
            array(
                "IBLOCK_TYPE" => "content",
                "IBLOCK_ID" => "1",
                "SECTION_ID" => "",
                "SECTION_CODE" => "",
                "SECTION_URL" => "",
                "COUNT_ELEMENTS" => "N",
                "TOP_DEPTH" => "5",
                "SECTION_FIELDS" => array("UF_PRICE_FROM", "PICTURE"),
                "SECTION_USER_FIELDS" => array("UF_PRODUCT", "UF_PRICE_FROM"),
                "ADD_SECTIONS_CHAIN" => "N",
                "CACHE_TYPE" => "N",
                "CACHE_TIME" => "0",
                "CACHE_GROUPS" => "N",
                "FILTER_NAME" => "arrFilter",
                "NAV_COUNT" => 4,
                "SORT_BY" => "rand",
                "SORT_ORDER" => "desc"
            ),
            false
        ); ?><?
    } ?>
    <div class="clear"></div>
<? } ?>
<? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?><?= $arResult["NAV_STRING"] ?><br/><br/>
<? endif; ?>
