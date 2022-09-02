<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
    <?= $arResult["NAV_STRING"] ?><br/>
<? endif; ?>
<?
if (sizeof($arResult["ITEMS"]) > 0) {
    ?>
    <div class="catalog_element">
        <form action="/cart/add_to_cart.php" method="post" class="order_form">
            <table class="full element_table">
                <thead>
                <tr>
                    <td class="first-td"></td>
                    <td class="art">Арт</td>
                    <td class="nopadding-i"></td>
                    <td class="name">Наименование</td>
                    <td class="opt">Опт</td>

                    <?php if (!$arResult['IS_OPT_2'] && !$arResult['IS_OPT_3']) { ?>
                        <td class="roz">Розница</td>
                    <?php }?>

                    <td class="upak">В упаковке</td>
                    <td class="avail">Наличие</td>
                    <td class="buy">Купить</td>
                    <td class="mera">Ед</td>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                <?php
                $isSectionNameWrited = false;
                foreach ($arResult["ITEMS"] as $cell => $arElement):

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
                    // Поставим заголовок для товаров в случае фильтрации, если он еще не был записан
                    if ($arResult['IS_FILTER'] && !$isSectionNameWrited) { ?>
                        <tr>
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

                    <tr id="<?= $this->GetEditAreaId($arElement['ID']); ?>"
                        class="<?= ((float)$arElement["PROPERTIES"]["Ostatok"]["VALUE"] > 0 ? 'available' : 'not-available') ?> row<?= ($cell % 2); ?>">
                        <?php

                        if ($cell == 0) { ?>
                        <td rowspan="<?= (sizeof($arResult["ITEMS"]) + 1 + sizeof($arResult['SECTIONS_COUNT']) + 1) ?>" class="section_description">

                            <? if ($arResult["PICTURE"]["ID"]) {
                                $file = CFile::ResizeImageGet($arResult["PICTURE"]["ID"], array('width' => 128, 'height' => 130), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                $file_big = CFile::ResizeImageGet($arResult["PICTURE"]["ID"], array('width' => 900, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
                                ?>

                                <a href="<?= $file["src"] ?>" class="fancybox"><img src="<?= $file["src"] ?>"
                                                                                        alt=""></a>

                            <? }?>
                            <div class="description"><?/*= $arResult["DESCRIPTION"]*/ ?></div>
                            <div class="clear"></div>
                        </td>
                        <? }  ?>
                        <td class="art <?= $cell?>">
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
                        <td class="name" <?php if ($arResult['IS_FILTER']) {echo 'style="width: 100%"';}?>>
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
                        </td>
                        <td class="opt">
                            <?php
                            if ($arResult['IS_OPT_2']) {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                            } elseif  ($arResult['IS_OPT_3']) {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT2"]["VALUE"];
                            } else {
                                $sPrice = $arElement["DISPLAY_PROPERTIES"]["PRICE_OPT"]["VALUE"];
                            }
                            ?>
                            <div class="buy_helper_holder price">
                                <? if ($ves) {?>
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
                        <?php }?>

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
                            <span class="section-product-minus">
                                -
                            </span>
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
                            <span class="section-product-plus">
                                +
                            </span>
                        </td>
                        <td class="mera"><?= $mera ?></td>
                        <td class="cart_td" <?php if ($arResult['IS_FILTER']) {echo 'colspan="2"';}?>><a href="#" class="add_to_cart_one"><img src="/img/cart_buttton.png" alt=""></a>
                        </td>

                    </tr>
                <? endforeach; // foreach($arResult["ITEMS"] as $arElement):?>
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
