<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ($arResult["ORDER_SUCCESSFULLY_CREATED"] == "Y") {
    echo GetMessage("ORDER_SUCCESSFULLY_CREATED");
    return;
}
?>

<link href="<?= $this->GetFolder(); ?>/suggestions.min.css" rel="stylesheet" />
<script src="<?= $this->GetFolder(); ?>/jquery.suggestions.min.js"></script>
<script src="<?= $this->GetFolder(); ?>/jquery.maskedinput.min.js" type="text/javascript"></script>

<script type="text/javascript">
    function submitForm(val) {
        BX('<? echo $arParams["ENABLE_VALIDATION_INPUT_ID"]; ?>').value = (val !== 'Y') ? "N" : "Y";
        var orderForm = BX('<? echo $arParams["FORM_ID"]; ?>');

        // Если нужно просто перезагрузить страницу, то убираем все валидации
        if (val == 'NO_VALID') {
            $("input").each(function (index, el){
                $(el).prop('required', false);
            });
        }

        BX.submit(orderForm);
        return true;
    }
</script>

<div class="order-simple">
    <form method="post"
          id="<? echo $arParams["FORM_ID"]; ?>"
          name="<? echo $arParams["FORM_NAME"]; ?>"
          action="<? echo $arParams["FORM_ACTION"]; ?>">

        <div class="row">
            <div class="col-sm-9">
                <?= bitrix_sessid_post() ?>

                <input type="hidden"
                       name="<? echo $arParams["ENABLE_VALIDATION_INPUT_NAME"]; ?>"
                       id="<? echo $arParams["ENABLE_VALIDATION_INPUT_ID"]; ?>"
                       value="Y">

                <? if (is_array($arResult["ERRORS"]) && $arResult["HIDE_ERRORS"] != "Y") { ?>
                    <div class="order-simple__block">
                        <? foreach ($arResult["ERRORS"] as $error) { ?>
                            <div class="order-simple__error">
                                <? echo $error; ?>
                            </div>
                        <? } ?>
                    </div>
                <? } ?>

                <div class="order-simple__block">
                    <div class="order-simple__block__title">1. <? echo GetMessage("PAYMANT_TYPES"); ?></div>
                    <?php
                    $dbResult = CSalePersonType::GetList(['SORT' => 'ASC'], ['LID' => SITE_ID]);
                    $bFirst = True;

                    if (!empty($_REQUEST['PERSON_TYPE'])) {
                        $iPersonTypeId = $_REQUEST['PERSON_TYPE'];
                        $bFirst = false;
                    }
                    while ($ptype = $dbResult->Fetch())
                    {?>
                        <div class="order-simple__field">
                            <label for="person_type_<?= $ptype['ID']?>">
                                <?php
                                if (!(empty($iPersonTypeId)) && $ptype['ID'] == $iPersonTypeId) {
                                    $isChecked = true;
                                } else {
                                    $isChecked = false;
                                }
                                ?>
                                <input onchange="submitForm('NO_VALID'); return false;"
                                       id="person_type_<?= $ptype["ID"]?>"
                                       type="radio" name="PERSON_TYPE"
                                       value="<?echo $ptype["ID"] ?>"<?if ($bFirst || $isChecked) echo " checked";?>>&nbsp;<?echo $ptype["NAME"] ?>
                            </label>
                        </div>
                        <?php
                        $bFirst = false;
                    }
                    ?>
                </div>

                <? if (!empty($arResult["ORDER_PROPS"])) { ?>
                    <div class="order-simple__block order-simple__block-width50 order-simple__block__noborder order-simple__block__marginminus15">
                        <div class="order-simple__block__title">2. <? echo GetMessage("ORDER_PROPS"); ?></div>
                        <div class="order-props-in-two-columns">
                            <?php
                            $arNoRequired = ['COMPANY_ADR', 'INN', 'KPP'];
                            // Исключенные для показа свойства
                            $arExcludedProps = ['LOCATION', 'ZIP', 'CITY', 'ADDRESS'];
                            foreach ($arResult["ORDER_PROPS"] as $arProp) {
                                if (in_array($arProp['CODE'], $arExcludedProps)) {
                                    continue;
                                }

                                if (!in_array($arProp['CODE'], $arNoRequired)) {
                                    $sRequired = 'required';
                                } else {
                                    $sRequired = '';
                                }
                                ?>
                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="<? echo $arParams["FORM_NAME"] ?>_<?= $arProp["CODE"] ?>">
                                    <span class="order-simple__field__title">
                                        <?= $arProp["NAME"] ?>
                                        <? if (in_array($arProp["ID"], $arParams["REQUIRED_ORDER_PROPS"])) { ?>*<? } ?>
                                    </span>
                                        <? if (
                                            $arParams["USE_DATE_CALCULATION"] == "Y" &&
                                            $arProp["ID"] == $arParams["DATE_PROPERTY"]
                                        ) { ?>
                                            <select class="form-control" name="<? echo $arParams["FORM_NAME"] ?>[<?= $arProp["CODE"] ?>]"
                                                    id="date"
                                                    autocomplete="off">
                                                <? foreach ($arResult['AVAILABLE_DATES'] as $date) { ?>
                                                    <option
                                                        <? if ($arResult["CURRENT_VALUES"]["ORDER_PROPS"]["DATE"] == $date){ ?>selected<? } ?>
                                                        value="<?= $date["DATE_FORMATTED"] ?>">
                                                        <?= $date["DATE_FORMATTED"] ?>
                                                    </option>
                                                <? } ?>
                                            </select>
                                        <? } else { ?>
                                            <input class="form-control" id="<? echo $arParams["FORM_NAME"] ?>_<?= $arProp["CODE"] ?>"
                                                   value="<? echo $arResult["CURRENT_VALUES"]["ORDER_PROPS"][$arProp["CODE"]]; ?>"
                                                   name="<? echo $arParams["FORM_NAME"] ?>[<?= $arProp["CODE"] ?>]"
                                                   type="text" <?= $sRequired?>/>
                                        <? } ?>
                                    </label>
                                </div>
                            <? } ?>
                        </div>
                    </div>
                    <div class="order-simple__block"></div>
                <? } ?>

                <? if (!empty($arResult["DELIVERY"])) { ?>
                    <div class="order-simple__block">
                        <div class="order-simple__block__title">3. <? echo GetMessage("DELIVERY"); ?></div>

                        <div class="delivery_logos">
                            <?php
                            foreach ($arResult["DELIVERY"] as $arDelivery) { ?>
                                <div class="order-simple__field order-simple__field_delivery">
                                    <div class="form-check form-check-delivery">
                                        <input class="input_delivery"
                                               type="radio"
                                               <? if ($arDelivery["CHECKED"] == "Y"){ ?>checked<? } ?>
                                               id="delivery_<?= $arDelivery["ID"] ?>"
                                               name="<? echo $arParams["FORM_NAME"] ?>[DELIVERY]"
                                               value="<?= $arDelivery["ID"] ?>"
                                               autocomplete="off"
                                        />
                                        <label class="form-check-label form-check-label-delivery" for="delivery_<?= $arDelivery["ID"] ?>">
                                            <div class="delivery-name">
                                                <?= $arDelivery["NAME"] ?>
                                            </div>
                                            <div class="delivery-img">
                                                <img class="delivery_image" src="<?= CFile::GetPath($arDelivery['LOGOTIP']);?>" alt="<?= $arDelivery["NAME"] ?>">
                                            </div>
                                        </label>

                                    </div>
                                </div>
                            <? } ?>
                        </div>

                        <?php foreach ($arResult["DELIVERY"] as $arDelivery) {
                            if ($arDelivery['ID'] == 3 && $arDelivery['CHECKED'] == "Y") {
                                $sDisplay = '';
                                break;
                            } else {
                                $sDisplay = "style='display: none'";
                            }
                        } ?>
                        <label for="simple_order_form_ADDRESS" <?= $sDisplay?> class="b-label-address">
                                <span class="order-simple__field__title">
                                    <b>Адрес доставки</b>
                                </span>
                            <input class="form-control form-control-address" id="simple_order_form_ADDRESS" value="<?= $_REQUEST['simple_order_form']['ADDRESS']?>" name="simple_order_form[ADDRESS]" type="text" placeholder="Введите адрес доставки">
                        </label>
                    </div>
                <? } ?>

                <? if ($arResult["PAY_SYSTEM"]) { ?>
                    <div class="order-simple__block">
                        <div class="order-simple__block__title">4. <? echo GetMessage("PAY_SYSTEM"); ?></div>
                        <?
                        foreach ($arResult["PAY_SYSTEM"] as $arPaySystem) { ?>
                            <div class="order-simple__field">
                                <label for="pay_system_<?= $arPaySystem["ID"] ?>">
                                    <input type="radio"
                                           onchange="submitForm('NO_VALID'); return false;"
                                           <? if ($arPaySystem["CHECKED"] == "Y"){ ?>checked<? } ?>
                                           id="pay_system_<?= $arPaySystem["ID"] ?>"
                                           name="<? echo $arParams["FORM_NAME"] ?>[PAY_SYSTEM]"
                                           value="<?= $arPaySystem["ID"] ?>"
                                           autocomplete="off"
                                    />
                                    <?= $arPaySystem["NAME"] ?>
                                </label>
                            </div>
                        <? } ?>
                    </div>
                <? } ?>

                <div class="order-simple__block">
                    <div class="order-simple__block__title"><? echo GetMessage("COMMENT"); ?></div>
                    <textarea
                            name="<? echo $arParams["FORM_NAME"] ?>[USER_COMMENT]"
                            id="comment"
                            class="form-control form-control-comment"
                            rows="5"
                            cols="20"
                    ><? echo $arResult["CURRENT_VALUES"]["ORDER_PROPS"]["USER_COMMENT"]; ?></textarea>
                </div>

                <?php
                /* Таблица с итоговыми суммами заказа и жоставки
                ?>
                <div class="order-simple__block">
                    <table class="order-simple__price-table">
                        <tr>
                            <td>
                                <? echo GetMessage("ORDER_PRICE"); ?>
                            </td>
                            <td><? echo $arResult["PRICES"]["PRODUCTS_PRICE_FORMATTED"]; ?></td>
                        </tr>
                        <tr>
                            <td>
                                <? echo GetMessage("DELIVERY_PRICE"); ?>
                            </td>
                            <td><? echo $arResult["PRICES"]["DELIVERY_PRICE_FORMATTED"]; ?></td>
                        </tr>
                        <tr>
                            <td>
                                <? echo GetMessage("TOTAL_PRICE"); ?>
                            </td>
                            <td><? echo $arResult["PRICES"]["TOTAL_PRICE_FORMATTED"]; ?></td>
                        </tr>
                    </table>
                </div>
                <?php */ ?>
                <div class="order-simple__block order-simple__block__noborder order-simple__block__marginbottom10">
                    <span class="total-price">Итого: <b><? echo $arResult["PRICES"]["TOTAL_PRICE_FORMATTED"]; ?></b></span>
                </div>

                <div class="order-simple__block order-simple__block__noborder">
                    <? if ($arParams['USER_CONSENT'] == 'Y' && $arParams["AJAX_MODE"] != "Y") {
                        $APPLICATION->IncludeComponent(
                            "bitrix:main.userconsent.request",
                            "",
                            array(
                                "ID" => $arParams["USER_CONSENT_ID"],
                                "IS_CHECKED" => $arParams["USER_CONSENT_IS_CHECKED"],
                                "AUTO_SAVE" => "N",
                                "IS_LOADED" => $arParams["USER_CONSENT_IS_LOADED"],
                                "INPUT_NAME" => "order_userconsent",
                                "INPUT_ID" => "order_userconsent",
                                "REPLACE" => array(
                                    'button_caption' => "�������� �����",
                                    'fields' => $arResult['USER_CONSENT_FIELDS']
                                )
                            )
                        );
                    } ?>
                    <button class="btn btn-lg btn-default basket-btn-checkout" id="submitbtn" onclick="submitForm('Y'); return false;"><? echo GetMessage("SUBMIT_BUTTON"); ?></button>
                </div>
            </div>
            <div class="col-sm-3 width-etalon">
                <div class="fixed-box">
                    <div class="fixed-div">
                        <div class="left-total-title">Товары в заказе:</div>
                        <div class="gray-border-bottom"></div>
                        <div class="left-total-items">
                            <?php foreach ($arResult['BASKET_ITEMS'] as $basketItem) {?>
                                <div class="total-item">
                                    <div class="left-total-item_title"><?= $basketItem['NAME']?></div>
                                    <div class="left-total-item_description"><?= round((float) $basketItem['QUANTITY'] * (float) $basketItem['PRICE'])?> ₽ - <?= $basketItem['QUANTITY']?> <?= $basketItem['MEASURE_NAME']?></div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="gray-border-bottom margintop10"></div>
                        <div class="left-total-title left-total-title-nomargin">Итого: <? echo $arResult["PRICES"]["TOTAL_PRICE_FORMATTED"]; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    objOrderForm.init();
</script>



