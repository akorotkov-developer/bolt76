<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php if(sizeof($arResult["ITEMS"])>0) {?>
    <div class="product-filter">
        <div class="display hidden-xs">
            <button type="button" id="list-view" class="btn-list active" data-toggle="tooltip" title="List"><i class="fa fa-th-list"></i>
            </button>
            <button type="button" id="grid-view" class="btn-grid" data-toggle="tooltip" title="Grid"><i class="fa fa-th-large"></i>
            </button>
        </div>
    </div>

    <div class="row">
        <?php foreach ($arResult["ITEMS"] as $cell => $arElement) {
            $this->AddEditAction($arElement['ID'], $arElement['EDIT_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_EDIT'));
            $this->AddDeleteAction($arElement['ID'], $arElement['DELETE_LINK'], CIBlock::GetArrayByID($arParams['IBLOCK_ID'], 'ELEMENT_DELETE'), ['CONFIRM' => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')]);

            $arElement['NAME'] = ($arElement['NAME'] == '-' ? $arElement['PROPERTIES']['NAIMENOVANIE']['VALUE'] : $arElement['NAME']);

            $mera = $arElement['DISPLAY_PROPERTIES']['UNITS']['VALUE'];
            $ves = (float)$arElement['PROPERTIES']['VES1000PS']['VALUE'];
            if ($mera == 'кг') {
                $k = round(1000 / $ves, 5);

                $k_val = 'шт';
                $e = 'руб/шт';
            } elseif ($mera == 'шт') {
                $k = round($ves / 1000, 5);

                $k_val = 'кг';
                $e = 'руб/кг';
            } elseif ($mera == 'тыс. шт') {
                $k = round($ves, 2);

                $k_val = 'кг';
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
            <div class="product-layout product-list col-xs-12" id="<?= $this->GetEditAreaId($arElement['ID']); ?>">
                <div class="product-thumb">
                    <div class="b-favorite">
                        <svg data-product-id="<?= $arElement['ID']?>" class="favorite-svg-icon <?=$arElement['IS_FAVORITE'] ? 'active' : ''?>" title="Добавить в избранное" width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>
                    <div class="image">
                        <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>">
                            <?php
                            $file = CFile::ResizeImageGet(
                                $arElement['PREVIEW_PICTURE']['ID'],
                                ['width' => 268, 'height' => 268],
                                BX_RESIZE_IMAGE_PROPORTIONAL,
                                true
                            );
                            ?>
                            <img src="<?= $file['src']?>" alt="<?= $arElement['NAME']?>" title="<?= $arElement['NAME']?>" class="img-responsive" />
                        </a>
                    </div>
                    <div class="caption">
                        <div class="name">
                            <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>"><?= $arElement['NAME'] ?></a>
                        </div>
                        <div class="price"> 122.00 ₽</div>

                        <div class="description">
                            <?php if (!empty($arElement['DISPLAY_PROPERTIES'])) {?>
                                <dl class="product-item-detail-properties">
                                    <?php
                                    $arExcludedProps = [
                                        'PRICE_OPT2', 'PHOTO_ID', 'V_REZERVE', 'NAIMENOVANIE',
                                        'ROWID', 'NOMNOMER', 'SHOW_IN_PRICE', 'SORT_IN_PRICE', 'PHOTOS',
                                        'VES1000PS', 'MaksZapas', 'MinZapas', 'Otobrajat_v_prayse',
                                        'Svertka', 'SHOW_IN_PRICE', 'Otobrajat_na_sayte', 'SALE',
                                        'Svobodno', 'Nomenklaturniy_nomer', 'Naimenovanie',
                                        'TipSkladskogoZapasa', 'Kratnost', 'AVAILABLE', 'ADDITIONAL_PRODUCT_INFORMATION'
                                    ];

                                    // Отсортируем свойства в нужном порядке
                                    $arPrepareProps['ARTICUL'] = $arElement['DISPLAY_PROPERTIES']['ARTICUL'];
                                    unset($arElement['DISPLAY_PROPERTIES']['ARTICUL']);
                                    $arPrepareProps['UNITS'] = $arElement['DISPLAY_PROPERTIES']['UNITS'];
                                    unset($arElement['DISPLAY_PROPERTIES']['UNITS']);
                                    $arPrepareProps['PRICE_OPT2'] = $arElement['DISPLAY_PROPERTIES']['PRICE_OPT2'];
                                    unset($arElement['DISPLAY_PROPERTIES']['PRICE_OPT2']);
                                    $arPrepareProps['PRICE_OPT'] = $arElement['DISPLAY_PROPERTIES']['PRICE_OPT'];
                                    unset($arElement['DISPLAY_PROPERTIES']['PRICE_OPT']);
                                    $arPrepareProps['PRICE'] = $arElement['DISPLAY_PROPERTIES']['PRICE'];
                                    unset($arElement['DISPLAY_PROPERTIES']['PRICE']);
                                    $arPrepareProps['Ostatok'] = $arElement['DISPLAY_PROPERTIES']['Ostatok'];
                                    unset($arElement['DISPLAY_PROPERTIES']['Ostatok']);
                                    $arPrepareProps['UPAKOVKA'] = $arElement['DISPLAY_PROPERTIES']['UPAKOVKA'];
                                    unset($arElement['DISPLAY_PROPERTIES']['UPAKOVKA']);

                                    $arElement['DISPLAY_PROPERTIES'] = array_merge($arPrepareProps, $arElement['DISPLAY_PROPERTIES']);

                                    foreach ($arElement['DISPLAY_PROPERTIES'] as $property) {
                                        if ($property['CODE'] == 'UPAKOVKA2') {
                                            continue;
                                        }

                                        if (in_array($property['CODE'], $arExcludedProps)) {
                                            continue;
                                        }

                                        if ($property['VALUE'] === 0) {
                                            continue;
                                        }

                                        if ($property['NAME'] == 'Оптовая цена 2') {
                                            continue;
                                        }

                                        if ($property['NAME'] == 'Розничная цена') {
                                            continue;
                                        }
                                        if ($property['NAME'] == 'Оптовая цена') {
                                            continue;
                                        }
                                        if ($property['NAME'] == 'Длина' || $property['NAME'] == 'Диаметр') {
                                            if ($property['VALUE'] != '') {
                                                $property['VALUE'] = $property['VALUE'] . ' мм';
                                            }
                                        }

                                        if ($property['NAME'] == 'Остаток') {
                                            $property['NAME'] = 'Наличие';

                                            $property['VALUE'] = ($arElement['PROPERTIES']['Svobodno']['VALUE'] > 0) ? $arElement['PROPERTIES']['Svobodno']['VALUE'] : '0';

                                            $property['VALUE'] = $property['VALUE'] . ' ' . $arElement['PROPERTIES']['UNITS']['VALUE'];
                                        }

                                        if ($property['NAME'] == 'Единицы') {
                                            $property['NAME'] = 'Единицы измерения';
                                        }

                                        if ($property['CODE'] == 'UPAKOVKA' && $property['VALUE'] != '') {?>
                                            <div class="prop-item">
                                                <dt class="prop-item-title"><?=$property['NAME']?></dt>
                                                <?php
                                                if ( $arElement['PROPERTIES']['UPAKOVKA2']['VALUE'] != '') {
                                                    $sValue = (
                                                        is_array($property['VALUE'])
                                                            ? implode(' / ', $property['VALUE'])
                                                            : $property['VALUE']
                                                        ) . ' / ' . $arElement['PROPERTIES']['UPAKOVKA2']['VALUE'];
                                                } else {
                                                    $sValue = (
                                                    is_array($property['VALUE'])
                                                        ? implode(' / ', $property['VALUE'])
                                                        : $property['VALUE']
                                                    );
                                                }
                                                ?>
                                                <dd>
                                                    <?=$sValue . ' ' . $arElement['PROPERTIES']['UNITS']['VALUE']?>
                                                </dd>
                                            </div>
                                        <?php } else {
                                            if ($property['VALUE'] != '') {
                                                ?>
                                                <div class="prop-item">
                                                    <dt class="prop-item-title"><?=$property['NAME']?></dt>
                                                    <dd><?=(
                                                        is_array($property['VALUE'])
                                                            ? implode(' / ', $property['VALUE'])
                                                            : $property['VALUE']
                                                        )?>
                                                    </dd>
                                                </div>
                                            <?php }?>
                                        <?php }?>
                                        <?
                                    }
                                    unset($property);
                                    ?>
                                </dl>
                            <?php } ?>
                        </div>

                        <div class="avail">
                            <b>
                                <? if ((float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] > 0) {
                                    echo 'В наличии';
                                } else {
                                    if ($arElement["PROPERTIES"]["TipSkladskogoZapasa"]["VALUE"] == 'Обязательный ассортимент' &&
                                        (float)$arElement["PROPERTIES"]["Svobodno"]["VALUE"] <= 0) {
                                        echo 'Временно отсутствует';
                                    } else {
                                        echo 'Под заказ';
                                    }
                                } ?>
                            </b>
                        </div>

                        <div class="in-pack">
                            <?php
                            $up1 = (float)$arElement["PROPERTIES"]["UPAKOVKA"]["VALUE"];
                            $up2 = (float)$arElement["PROPERTIES"]["UPAKOVKA2"]["VALUE"];
                            ?>
                            <b>
                                В упаковке: <?= $up1; ?><?= ($up2 ? '/' . $up2 : '') ?>&nbsp;<?=$arElement['PROPERTIES']['UNITS']['VALUE']?>
                            </b>
                        </div>

                        <div class="button-group">
                            <button class="btn btn-cart" type="button">Добавить в корзину</button>
                        </div>
                    </div>
                    <div class="button-group">
                        <div class="b-prices">
                            <h3>Цены:</h3>

                            <?php
                            if ($arResult['IS_OPT_2']) {
                                $sPrice = $arElement['DISPLAY_PROPERTIES']['PRICE_OPT']['VALUE'];
                            } elseif  ($arResult['IS_OPT_3']) {
                                $sPrice = $arElement['DISPLAY_PROPERTIES']['PRICE_OPT2']['VALUE'];
                            } else {
                                $sPrice = $arElement['DISPLAY_PROPERTIES']['PRICE_OPT']['VALUE'];
                            }
                            ?>
                            <table class="price">
                                <tr>
                                    <td><b>Опт: </b></td>
                                    <td><?= coolPrice($sPrice) ?> ₽</td>
                                </tr>
                                <?php if (!$arResult['IS_OPT_2'] && !$arResult['IS_OPT_3']) { ?>
                                    <tr>
                                        <td><b>Розница: </b></td>
                                        <td><?= coolPrice($arElement['DISPLAY_PROPERTIES']['PRICE']['VALUE']) ?> ₽</td>
                                    </tr>
                                <?php }?>
                            </table>
                        </div>

                        <div class="b-bottom-addcart">
                            <div class="buy_helper">
                                <? if ($ves) {
                                    ?>
                                    <div class="vesHelperHolder">
                                    <div class="vesHelper" data-val='<?= $k_val; ?>' data-k="<?= $k; ?>">
                                        0 <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?></div></div><? } ?>
                                <div class="input_holder">
                                    <input type="text" class="quantity_input" name="ITEM[<?= $arElement["ID"] ?>]"
                                           data-price="<?= $arElement["DISPLAY_PROPERTIES"]["PRICE"]["VALUE"] ?>"
                                           data-ratio="<?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 'NaN' ?>"
                                    >
                                    <?= $arElement["DISPLAY_PROPERTIES"]["UNITS"]["VALUE"] ?>
                                    <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                        <span class="hint_min">мин. <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?></span>
                                    <?php } ?>
                                </div>

                                <?php if ($arElement['PROPERTIES']['Kratnost']['VALUE'] != '') { ?>
                                    <div class="kratnostHelperHolder">
                                        <div class="kratnostHelper">
                                            Данная продукция отпускается
                                            кратно <?= ($arElement['PROPERTIES']['Kratnost']['VALUE'] > 0) ? $arElement['PROPERTIES']['Kratnost']['VALUE'] : 1 ?> <?= $arElement["PROPERTIES"]["UNITS"]["VALUE"] ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>

                            <button class="btn btn-cart" type="button">В корзину</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php
}
?>