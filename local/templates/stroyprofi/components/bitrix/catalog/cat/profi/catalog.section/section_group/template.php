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

    <div class="row row-100">
        <?php
        foreach ($arResult["GROUP_ITEMS"] as $groupCode => $elements) {
            $groupValueMap = [];
            foreach ($elements as $elementItem) {
                $groupValueMap[] = [
                    'ID' => $elementItem['ELEMENT']['ID'],
                    'VALUE' => $elementItem['GROUP'],
                ];
            }

            $display = true;
            foreach ($elements as $elementItem) {
                $arElement = $elementItem['ELEMENT'];

                $arElement['NAME'] = ($arElement['NAME'] == '-' ? $arElement['PROPERTY_NAIMENOVANIE_VALUE'] : $arElement['NAME']);

                $mera = $arElement['PROPERTY_UNITS_VALUE'];
                $ves = (float)$arElement['PROPERTY_VES1000PS_VALUE'];
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
                    $sPrice = $arElement["PROPERTY_PRICE_OPT_VALUE"];
                } elseif ($arResult['IS_OPT_3']) {
                    $sPrice = $arElement["PROPERTY_PRICE_OPT2_VALUE"];
                }

                $ok = number_format(round($sPrice / $k, 2), 2, ', ', ' ');
                $rk = number_format(round($arElement["PROPERTY_PRICE_VALUE"] / $k, 2), 2, ', ', ' ');

                // Скрываем все торговые предложения, кроме первого
                if (!$display) {?>
                    <style>
                        #element_<?= $arElement['ID']?> {
                            display: none;
                        }
                    </style>
                <?php } ?>

                <div class="product-layout product-list col-xs-12" id="element_<?= $arElement['ID']?>" data-group-code="<?= $groupCode?>">
                    <div class="product-thumb">
                        <div class="b-favorite">
                            <svg data-product-id="<?= $arElement['ID']?>" class="favorite-svg-icon <?=$arElement['IS_FAVORITE'] ? 'active' : ''?>" title="Добавить в избранное" width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#8899a4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <div class="image image-grid">
                            <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>">
                                <?php
                                $pictureId = ((int)$arElement['PREVIEW_PICTURE']['ID'] > 0) ? $arElement['PREVIEW_PICTURE']['ID'] : $arResult['PICTURE']['ID'];
                                $file = CFile::ResizeImageGet(
                                    $pictureId,
                                    ['width' => 268, 'height' => 268],
                                    BX_RESIZE_IMAGE_PROPORTIONAL,
                                    true
                                );
                                ?>
                                <img src="<?= $file['src']?>" alt="<?= $arElement['NAME']?>" title="<?= $arElement['NAME']?>" class="img-responsive" />
                            </a>
                        </div>
                        <div class="image image-list">
                            <?php
                            $pictureId = ((int)$arElement['PREVIEW_PICTURE']['ID'] > 0) ? $arElement['PREVIEW_PICTURE']['ID'] : $arResult['PICTURE']['ID'];
                            $file = CFile::ResizeImageGet(
                                $pictureId,
                                ['width' => 268, 'height' => 268],
                                BX_RESIZE_IMAGE_PROPORTIONAL,
                                true
                            );
                            ?>
                            <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>" class="image-item" style="
                                    background: url('<?= $file['src'] ?>');
                                    background-repeat: no-repeat;
                                    background-size: contain;
                                    background-position: center center;
                                    ">

                            </a>
                        </div>
                        <div class="b-product-info">
                            <div class="caption">
                                <div class="name">
                                    <a href="<?= $arElement['DETAIL_PAGE_URL'] ?>"><?= $arElement['NAME'] ?></a>
                                </div>

                                <div class="description">

                                        <dl class="product-item-detail-properties">
                                            <?php
                                            $arExcludedProps = [
                                                'PRICE_OPT2', 'PHOTO_ID', 'V_REZERVE', 'NAIMENOVANIE',
                                                'ROWID', 'NOMNOMER', 'SHOW_IN_PRICE', 'SORT_IN_PRICE', 'PHOTOS',
                                                'VES1000PS', 'MAKSZAPAS', 'MINZAPAS', 'OTOBRAJAT_V_PRAYSE',
                                                'SVERTKA', 'SHOW_IN_PRICE', 'OTOBRAJAT_NA_SAYTE', 'SALE',
                                                'SVOBODNO', 'NOMENKLATURNIY_NOMER', 'NAIMENOVANIE',
                                                'TIPSKLADSKOGOZAPASA', 'KRATNOST', 'AVAILABLE', 'ADDITIONAL_PRODUCT_INFORMATION',
                                                'GROUP_PROPERTIES'
                                            ];

                                            // Отсортируем свойства в нужном порядке
                                            $arPrepareProps['ARTICUL'] = $arElement['PROPERTY_ARTICUL_VALUE'];
                                            $arPrepareProps['UNITS'] = $arElement['PROPERTY_UNITS_VALUE'];
                                            $arPrepareProps['PRICE_OPT2'] = $arElement['PROPERTY_PRICE_OPT2_VALUE'];
                                            $arPrepareProps['PRICE_OPT'] = $arElement['PROPERTY_PRICE_OPT_VALUE'];
                                            $arPrepareProps['PRICE'] = $arElement['PROPERTY_PRICE_VALUE'];
                                            $arPrepareProps['Ostatok'] = $arElement['PROPERTY_Ostatok_VALUE'];
                                            $arPrepareProps['UPAKOVKA'] = $arElement['PROPERTY_UPAKOVKA_VALUE'];
                                            $arPrepareProps['DIAMETER'] = $arElement['PROPERTY_DIAMETER_VALUE'];
                                            $arPrepareProps['LENGTH'] = $arElement['PROPERTY_LENGTH_VALUE'];

                                            $arElement['DISPLAY_PROPERTIES'] = $arPrepareProps;

                                            $propNamesMap = [
                                                'ARTICUL' => 'Артикул',
                                                'UNITS' => 'Единицы измерения',
                                                'PRICE_OPT2' => 'Оптовая цена 2',
                                                'PRICE_OPT' => 'Оптовая цена',
                                                'PRICE' => 'Розничная цена',
                                                'Ostatok' => 'Остаток',
                                                'UPAKOVKA' => 'Упаковка',
                                                'DIAMETER' => 'Диаметр',
                                                'LENGTH' => 'Длина',
                                            ];
                                            foreach ($arPrepareProps as $propCode => $propValue) {
                                                $propName = $propNamesMap[$propCode];

                                                if ($propCode == 'UPAKOVKA2') {
                                                    continue;
                                                }

                                                if (in_array($propCode, $arExcludedProps)) {
                                                    continue;
                                                }

                                                if ($propValue === 0) {
                                                    continue;
                                                }

                                                if ($propNamesMap[$propCode] == 'Оптовая цена 2') {
                                                    continue;
                                                }

                                                if ($propNamesMap[$propCode] == 'Розничная цена') {
                                                    continue;
                                                }
                                                if ($propNamesMap[$propCode] == 'Оптовая цена') {
                                                    continue;
                                                }
                                                if ($propNamesMap[$propCode] == 'Длина' || $propNamesMap[$propCode] == 'Диаметр') {
                                                    if ($propValue != '') {
                                                        $propValue = $propValue . ' мм';
                                                    }
                                                }

                                                if ($propNamesMap[$propCode] == 'Остаток') {
                                                    $propName = 'Наличие';

                                                    $propValue = ($arElement['PROPERTY_SVOBODNO_VALUE'] > 0) ? $arElement['PROPERTY_SVOBODNO_VALUE'] : '0';

                                                    $propValue = $propValue . ' ' . $arElement['PROPERTY_SVOBODNO_VALUE'];
                                                }

                                                if ($propNamesMap[$propCode] == 'Единицы') {
                                                    $propName = 'Единицы измерения';
                                                }

                                                if ($propCode == 'UPAKOVKA' && $propValue != '') {?>
                                                    <div class="prop-item">
                                                        <dt class="prop-item-title"><?=$propName?></dt>
                                                        <?php
                                                        if ( $arElement['PROPERTY_UPAKOVKA2_VALUE'] != '') {
                                                            $sValue = (
                                                                is_array($propValue)
                                                                    ? implode(' / ', $propValue)
                                                                    : $propValue
                                                                ) . ' / ' . $arElement['PROPERTY_UPAKOVKA2_VALUE'];
                                                        } else {
                                                            $sValue = (
                                                            is_array($propValue)
                                                                ? implode(' / ', $propValue)
                                                                : $propValue
                                                            );
                                                        }
                                                        ?>
                                                        <dd>
                                                            <?=$sValue . ' ' . $arElement['PROPERTY_UNITS_VALUE']?>
                                                        </dd>
                                                    </div>
                                                <?php } else {
                                                    if ($propValue != '') {
                                                        ?>
                                                        <div class="prop-item">
                                                            <dt class="prop-item-title"><?=$propName?></dt>
                                                            <dd><?=(
                                                                is_array($propValue)
                                                                    ? implode(' / ', $propValue)
                                                                    : $propValue
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

                                </div>

                                <div class="avail">
                                    <b>
                                        <?php

                                        if ((float)$arElement["PROPERTY_SVOBODNO_VALUE"] > 0) {
                                            echo 'В наличии';
                                        } else {
                                            if ($arElement["PROPERTY_TIPSKLADSKOGOZAPASA_VALUE"] == 'Обязательный ассортимент' &&
                                                (float)$arElement["PROPERTY_SVOBODNO_VALUE"] <= 0) {
                                                echo 'Временно отсутствует';
                                            } else {
                                                echo 'Под заказ';
                                            }
                                        } ?>
                                    </b>
                                </div>

                                <div class="in-pack">
                                    <?php
                                    $up1 = (float)$arElement["PROPERTY_UPAKOVKA_VALUE"];
                                    $up2 = (float)$arElement["PROPERTY_UPAKOVKA2_VALUE"];
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
                                        $sPrice = $arElement['PROPERTY_PRICE_OPT_VALUE'];
                                    } elseif  ($arResult['IS_OPT_3']) {
                                        $sPrice = $arElement['PROPERTY_PRICE_OPT2_VALUE'];
                                    } else {
                                        $sPrice = $arElement['PROPERTY_PRICE_OPT_VALUE'];
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
                                                <td><?= coolPrice($arElement['PROPERTY_PRICE_VALUE']) ?> ₽</td>
                                            </tr>
                                        <?php }?>
                                    </table>
                                </div>

                                <div class="b-group-props">
                                    <label class="group_select" for="<?= key($arResult['GROUP_MAP']) . '_' . $arElement['ID']?>"><?= current($arResult['GROUP_MAP'])?>:</label>
                                    <br>
                                    <select class="group-select" id="group-select_<?= $arElement['ID']?>">
                                        <?php foreach ($groupValueMap as $groupValue) {
                                            if ($groupValue['VALUE']) {
                                            ?>
                                                <option value="<?= $groupValue['VALUE']?>"
                                                        data-element="<?=$groupValue['ID']?>"
                                                        data-group-code="<?= $groupCode?>"
                                                >
                                                    <?= $groupValue['VALUE']?>
                                                </option>
                                            <?php }?>
                                        <?php }?>
                                    </select>
                                </div>

                                <div class="b-bottom-addcart">
                                    <div class="buy_helper">
                                        <? if ($ves) {
                                            ?>
                                            <div class="vesHelperHolder">
                                            <div class="vesHelper" data-val='<?= $k_val; ?>' data-k="<?= $k; ?>">
                                                0 <?= $arElement["PROPERTY_UNITS_VALUE"] ?></div></div><? } ?>
                                        <div class="input_holder">
                                            <input type="text" class="quantity_input" name="ITEM[<?= $arElement["ID"] ?>]"
                                                   data-price="<?= $arElement["PROPERTY_PRICE_VALUE"] ?>"
                                                   data-ratio="<?= ($arElement['PROPERTY_KRATNOST_VALUE'] != '') ? $arElement['PROPERTY_KRATNOST_VALUE'] : 'NaN' ?>"
                                            >
                                            <?= $arElement['PROPERTY_UNITS_VALUE'] ?>
                                            <?php if ($arElement['PROPERTY_KRATNOST_VALUE'] != '') { ?>
                                                <span class="hint_min">мин. <?= ($arElement['PROPERTY_KRATNOST_VALUE'] > 0) ? $arElement['PROPERTY_KRATNOST_VALUE'] : 1 ?></span>
                                            <?php } ?>
                                        </div>

                                        <?php if ($arElement['PROPERTIES']['KRATNOST']['VALUE'] != '') { ?>
                                            <div class="kratnostHelperHolder">
                                                <div class="kratnostHelper">
                                                    Данная продукция отпускается
                                                    кратно <?= ($arElement['PROPERTY_KRATNOST_VALUE'] > 0) ? $arElement['PROPERTY_KRATNOST_VALUE'] : 1 ?> <?= $arElement["PROPERTY_UNITS_VALUE"] ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>

                                    <button class="btn btn-cart" type="button">В корзину</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php
                $display = false;
            }?>

        <?php } ?>
    </div>
<?php
}
?>