<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

\Bitrix\Main\UI\Extension::load("ui.alerts");

if ($arResult["ORDER_SUCCESSFULLY_CREATED"] == "Y") {
    echo GetMessage("ORDER_SUCCESSFULLY_CREATED");
    return;
}
?>

<link href="<?= $this->GetFolder(); ?>/suggestions.min.css" rel="stylesheet" />
<script src="<?= $this->GetFolder(); ?>/jquery.suggestions.min.js"></script>
<script src="<?= $this->GetFolder(); ?>/jquery.maskedinput.min.js" type="text/javascript"></script>

<script type="text/javascript">
    function validateSubmitForm()
    {
        var isValid = true;
        var curElement;
        var errors = [];

        $('.required-field').each(function(i, element) {
            curElement = $(element).siblings('input');
            if (curElement.val() == '' && curElement.is(":visible")) {
                isValid = false;
                if (!curElement.hasClass('error_input')) {
                    curElement.addClass('error_input');
                    errors.push($(element).text());
                }
            }
        });

        if (errors.length > 0) {
            $('.b-error-info').html('Вы не заполнили обязательные поля:<br>' + errors.join('<br>'));

            var destination = $('.b-error-info').offset().top;
            if ($.browser.safari) {
                $('body').animate({ scrollTop: destination }, 1100); //1100 - скорость
            } else {
                $('html').animate({ scrollTop: destination }, 1100);
            }
        }

        return isValid;
    }

    function submitForm(val) {
        BX('<? echo $arParams["ENABLE_VALIDATION_INPUT_ID"]; ?>').value = (val !== 'Y') ? "N" : "Y";
        var orderForm = BX('<? echo $arParams["FORM_ID"]; ?>');

        // Если нужно просто перезагрузить страницу, то убираем все валидации
        if (val == 'NO_VALID') {
            $("input").each(function (index, el){
                $(el).prop('required', false);
            });
        }

        // Валидация формы оформления заказа
        var isValid = validateSubmitForm();

        if (isValid) {
            BX.submit(orderForm);
        } else {
            return false;
        }
    }
</script>

<?php
// Проверить авторизацию пользователя
global $USER;
if (!$USER->IsAuthorized()) {?>
<!--    <div id="dialog-content" class="form-popup">-->
<!--        <form action="/action_page.php" class="form-container">-->
<!--            <h1>Вы не авторизованы</h1>-->
<!---->
<!--            <input type="text" id="login" placeholder="Введите Логин" name="USER_LOGIN" required>-->
<!---->
<!--            <input type="password" name="USER_PASSWORD" placeholder="Введите Пароль" required>-->
<!---->
<!--            <button type="submit" class="btn">Войти</button>-->
<!--            <button onclick="window.location.href='/account/register/?register=yes&backurl=%2Fpersonal%2Forder%2Fmake%2F';" type="submit" class="btn">Зарегистрироваться</button>-->
<!--            <button type="submit" class="btn cancel continue_without_registration">Продолжить без регистрации</button>-->
<!--        </form>-->
<!--    </div>-->
<div class="popup_auth">
    <?$APPLICATION->IncludeComponent(
        "bitrix:system.auth.form",
        "popup",
        Array(
            "FORGOT_PASSWORD_URL" => "/account/forgot/",
            "PROFILE_URL" => "/personal/",
            "REGISTER_URL" => "/account/register/",
            "SHOW_ERRORS" => "Y"
        )
    );?>
</div>
    <script>
        setTimeout(function(){
            Fancybox.show([{ src: "#dialog-content", type: "inline" }]);
        }, 1000);

        $('.continue_without_registration').click(function(){
            $('.carousel__button.is-close').trigger('click');
        });
    </script>
<?php } ?>

<div class="order-simple">
    <?php
    if (count($arResult['NOT_AVAIL']) > 0) {?>
        <!-- .ui-alert.ui-alert-icon-warning-->
        <div class="ui-alert ui-alert-icon-warning">
            <span class="ui-alert-message"><strong>Внимание!</strong>
                В заказе присутствуют товары, которых нет в наличии. Оплата заказа после согласования с менеджером.
                <br>
                <?= implode('<br>', $arResult['NOT_AVAIL'])?>
            </span>
        </div>

        <?
    }
    ?>
    <form method="post"
          enctype="multipart/form-data"
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

                <div class="b-error-info">

                </div>
                <div class="order-simple__block" style="display: none">
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
                        <div class="order-simple__block__title">1. <? echo GetMessage("ORDER_PROPS"); ?></div>
                        <div class="order-props-in-two-columns">
                            <?php
                            $arNoRequired = ['COMPANY_ADR', 'INN', 'KPP'];
                            // Исключенные для показа свойства
                            $arExcludedProps = [
                                'LOCATION', 'ZIP', 'CITY', 'ADDRESS', 'TRANSPORT_COMPANY', 'TERMINAL_ADDRESS',
                                'TRANSPORT_RECIPIENT_FULL_NAME', 'PASSPORT_DATA_RECIPIENT', 'RECIPIENT_PHONE',
                                'FIO_RECIPIENT', 'CONTACT_PHONE_RECIPIENT', 'DESIRED_DELIVERY_TIME', 'FILE_WITH_BANKING_DETAILS'
                            ];
                            $i = 0;
                            foreach ($arResult["ORDER_PROPS"] as $arProp) {
                                $i++;

                                if (in_array($arProp['CODE'], $arExcludedProps)) {
                                    continue;
                                }

                                if (!in_array($arProp['CODE'], $arNoRequired)) {
                                    $sRequired = 'required';
                                    $sClassRequired = 'required-field';
                                } else {
                                    $sRequired = '';
                                    $sClassRequired = '';
                                } ?>

                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="<? echo $arParams["FORM_NAME"] ?>_<?= $arProp["CODE"] ?>">
                                    <span class="order-simple__field__title <?= $sClassRequired?>"><?= $arProp["NAME"] ?></span>
                                        <?php if (
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
                                        <? } else {
                                            if ($arProp["CODE"] == 'CONTACT_PERSON') {
                                                if ($arResult["CURRENT_VALUES"]["ORDER_PROPS"]['CONTACT_PERSON'] != '') {
                                                    $value = $arResult["CURRENT_VALUES"]["ORDER_PROPS"]['CONTACT_PERSON'];
                                                } else if ($_REQUEST['simple_order_form']['FIO'] != '') {
                                                    $value = $_REQUEST['simple_order_form']['FIO'];
                                                } else {
                                                    $value = $arResult['USER_DB_FIO'];
                                                }
                                            } else if ($arProp["CODE"] == 'FIO') {
                                                if ($arResult["CURRENT_VALUES"]["ORDER_PROPS"]['FIO'] != '') {
                                                    $value = $arResult["CURRENT_VALUES"]["ORDER_PROPS"]['FIO'];
                                                } else if ($_REQUEST['simple_order_form']['CONTACT_PERSON'] != '') {
                                                    $value = $_REQUEST['simple_order_form']['CONTACT_PERSON'];
                                                } else {
                                                    $value = $arResult['USER_DB_FIO'];
                                                }
                                            } else if ($arProp["CODE"] == 'PHONE') {
                                                if ($_REQUEST['simple_order_form']['PHONE'] != '') {
                                                    $value = $_REQUEST['simple_order_form']['PHONE'];
                                                } else {
                                                    $value = $arResult['USER_DB_PERSONAL_PHONE'];
                                                }
                                            } else if ($arProp['CODE'] == 'EMAIL') {
                                                $value = $arResult['USER_DB_PERSONAL_EMAIL'];
                                            } else if ($arResult["CURRENT_VALUES"]["ORDER_PROPS"][$arProp["CODE"]] != '') {
                                                $value = $_REQUEST['simple_order_form'][$arProp["CODE"]];
                                            } else if ($arProp["CODE"] == 'COMPANY') {
                                                $value = $arResult['USER_DB_COMPANY_NAME'];
                                            } else if ($arProp["CODE"] == 'COMPANY_ADR') {
                                                $value = $arResult['USER_DB_YUR_ADDRESS'];
                                            } else if ($arProp["CODE"] == 'INN') {
                                                $value = $arResult['USER_DB_INN'];
                                            } else if ($arProp["CODE"] == 'KPP') {
                                                $value = $arResult['USER_DB_KPP'];
                                            } else {
                                                $value = '';
                                            }
                                            ?>
                                            <input class="form-control" id="<? echo $arParams["FORM_NAME"] ?>_<?= $arProp["CODE"] ?>"
                                                   value="<?= $value; ?>"
                                                   name="<? echo $arParams["FORM_NAME"] ?>[<?= $arProp["CODE"] ?>]"
                                                   type="text"
                                                   <?= $sRequired?>
                                            />
                                        <? } ?>
                                    </label>
                                </div>

                            <?php if ($i == 3) { ?>
                                </div>
                                <div class="order-props-in-two-columns">
                                    <div class="order-simple__field order-props-in-two-columns__item order-simple__fieldwitch">
                                        <label class="switch-label">
                                            <input type="checkbox" name="checkboxName" class="checkbox hidden-checkbox"/>
                                            <div class="switch <?= ($_REQUEST['PERSON_TYPE'] == '2') ? 'switchOn' : ''?>"></div>
                                        </label>
                                        <div class="switch-label">
                                            <b>Оформить как юридическое лицо</b>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-props-in-two-columns">
                            <?php } ?>
                            <? } ?>
                        </div>

                        <?php if ($_REQUEST['PERSON_TYPE'] == '2') {?>
                            <div class="order-simple__block no-border-bottom">
                                <div class="order-simple__block__title">Файл с реквизитами</div>
                                <input class="btn btn-themes" type="button" id="loadFile" value="Загрузить файл" onclick="document.getElementById('download_file_banking_detail').click();" />
                                <span id="file_name"></span>

                                <input type="file" id="download_file_banking_detail"
                                       onChange="downloadFile(this);"
                                       accept="txt,application/pdf,application/vnd.ms-excel,application/vnd.ms-excel,application/msword,application/msword"
                                       style="display: none"
                                >
                                <input type="hidden" name="simple_order_form[FILE_WITH_BANKING_DETAILS]"  value="">
                            </div>
                        <?php }?>
                    </div>
                    <div class="order-simple__block"></div>
                <? } ?>

                <? if (!empty($arResult["DELIVERY"])) { ?>
                    <div class="order-simple__block">
                        <div class="order-simple__block__title">2. <? echo GetMessage("DELIVERY"); ?></div>

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

                            if ($arDelivery['ID'] == 4 && $arDelivery['CHECKED'] == "Y") {
                                $sDisplayTransportData = "style='display: block'";
                                $sDeliveryValue = 'СДЭК';
                            } else {
                                $sDisplayTransportData = "";
                                $sDeliveryValue = '';
                            }
                        } ?>
                        <div <?= $sDisplay?> class="b-label-address">

                            <div class="order-props-in-two-columns">
                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="simple_order_form_FIO_RECIPIENT">
                                        <span class="order-simple__field__title required-field">ФИО получателя</span>
                                        <input class="form-control" id="simple_order_form_FIO_RECIPIENT" value="" name="simple_order_form[FIO_RECIPIENT]" type="text" >
                                    </label>
                                </div>

                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="simple_order_form_CONTACT_PHONE_RECIPIENT">
                                        <span class="order-simple__field__title required-field">Контанктый телефон получателя</span>
                                        <input class="form-control" id="simple_order_form_CONTACT_PHONE_RECIPIENT" value="" name="simple_order_form[CONTACT_PHONE_RECIPIENT]" type="text" >
                                    </label>
                                </div>

                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="simple_order_form_DESIRED_DELIVERY_TIME">
                                        <span class="order-simple__field__title">
                                            Желаемое время доставки
                                        </span>

                                        <select id="choose_delivery_time">
                                            <option value="" disabled selected>Выберите желаемое время доставки</option>
                                            <option value="с 10:00 до 12:00">с 10:00 до 12:00</option>
                                            <option value="c 12:00 до 15:00">c 12:00 до 15:00</option>
                                            <option value="с 15:00 до 17:00">с 15:00 до 17:00</option>
                                            <option value="с 17:00 до 19:00">с 17:00 до 19:00</option>
                                        </select>

                                        <input class="form-control" id="simple_order_form_DESIRED_DELIVERY_TIME" value="" name="simple_order_form[DESIRED_DELIVERY_TIME]" type="hidden" >
                                    </label>
                                </div>

                                <label for="simple_order_form_ADDRESS">
                                    <span class="order-simple__field__title required-field">Адрес доставки (масимально подробно)</span>
                                    <input class="form-control form-control-address" id="simple_order_form_ADDRESS" value="<?= $_REQUEST['simple_order_form']['ADDRESS']?>" name="simple_order_form[ADDRESS]" type="text" placeholder="Введите адрес доставки">
                                </label>
                            </div>

                        </div>

                        <div class="b-transport-info" <?= $sDisplayTransportData?>>
                            <h4><b>Данные доставки:</b></h4>

                            <div class="delivery_company_name">
                                <div class="order-simple__field">
                                    <label for="sdek">
                                        <input type="radio" checked="" id="sdek" value="СДЭК" name="delivery_company_name" autocomplete="off">
                                        <b>СДЭК</b>
                                    </label>
                                </div>
                                <div class="order-simple__field">
                                    <label for="business_lines">
                                        <input type="radio" id="business_lines" value="Деловые линии" name="delivery_company_name" autocomplete="off">
                                        <b>Деловые линии</b>
                                    </label>
                                </div>
                                <div class="order-simple__field">
                                    <label for="pek">
                                        <input type="radio" id="pek" value="ПЭК" name="delivery_company_name" autocomplete="off">
                                        <b>ПЭК</b>
                                    </label>
                                </div>
                                <div class="order-simple__field">
                                    <label for="baikal_servise">
                                        <input type="radio" id="baikal_servise" value="Байкал сервис" name="delivery_company_name" autocomplete="off">
                                        <b>Байкал-Сервис</b>
                                    </label>
                                </div>
                                <div class="order-simple__field">
                                    <label for="vozovozov">
                                        <input type="radio" id="vozovozov" value="Возовоз" name="delivery_company_name" autocomplete="off">
                                        <b>Возовоз</b>
                                    </label>
                                </div>
                            </div>

                            <div class="order-props-in-two-columns">
                                <div class="order-simple__field order-props-in-two-columns__item" style="display: none">
                                    <label for="simple_order_form_TRANSPORT_COMPANY">
                                        <span class="order-simple__field__title">
                                            Транспортная компания
                                        </span>
                                        <input class="form-control" id="simple_order_form_TRANSPORT_COMPANY" value="<?= $sDeliveryValue?>" name="simple_order_form[TRANSPORT_COMPANY]" type="text">
                                    </label>
                                </div>

                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="simple_order_form_RECIPIENT_PHONE">
                                        <span class="order-simple__field__title required-field" id="transport_company_contact_phone"><?= ($_REQUEST['PERSON_TYPE'] == '2') ? 'Контактный телефон представителя' : 'Контактный телефон получателя'?></span>
                                        <input class="form-control" id="simple_order_form_RECIPIENT_PHONE" value="" name="simple_order_form[RECIPIENT_PHONE]" type="text">
                                    </label>
                                </div>

                                <div class="order-simple__field order-props-in-two-columns__item">
                                    <label for="simple_order_form_TRANSPORT_RECIPIENT_FULL_NAME">
                                        <span class="order-simple__field__title required-field" id="transport_company_fio"><?= ($_REQUEST['PERSON_TYPE'] == '2') ? 'ФИО представителя' : 'ФИО получателя'?></span>
                                        <input class="form-control" id="simple_order_form_TRANSPORT_RECIPIENT_FULL_NAME" value="" name="simple_order_form[TRANSPORT_RECIPIENT_FULL_NAME]" type="text">
                                    </label>
                                </div>

                                <div class="order-simple__field order-props-in-two-columns__item " <?= ($_REQUEST['PERSON_TYPE'] == '2') ? 'style="display: none;"' : ''?>>
                                    <label for="simple_order_form_PASSPORT_DATA_RECIPIENT">
                                        <span class="order-simple__field__title required-field">Паспортные данные получателя</span>
                                        <input class="form-control" id="simple_order_form_PASSPORT_DATA_RECIPIENT" value="" name="simple_order_form[PASSPORT_DATA_RECIPIENT]" type="text">
                                    </label>
                                </div>
                            </div>

                            <div class="delivery_type">
                                <div class="order-simple__field">
                                    <label for="terminal">
                                        <input type="radio" checked="" id="terminal" value="Адрес терминала" name="delivery_type" autocomplete="off">
                                        <b>Адрес терминала</b>
                                    </label>
                                </div>
                                <div class="order-simple__field">
                                    <label for="address_delivery">
                                        <input type="radio" id="address_delivery" value="Адрес доставки" name="delivery_type" autocomplete="off">
                                        <b>Адресная доставка (до двери)</b>
                                    </label>
                                </div>
                            </div>

                            <label for="simple_order_form_TERMINAL_ADDRESS">
                                            <span class="order-simple__field__title required-field" id="address_for_delivery_type">Адрес терминала</span>
                                <input class="form-control" id="simple_order_form_TERMINAL_ADDRESS" value="" name="simple_order_form[TERMINAL_ADDRESS]" type="text">
                            </label>
                        </div>
                    </div>
                <? } ?>

                <? if ($arResult["PAY_SYSTEM"]) { ?>
                    <div class="order-simple__block">
                        <div class="order-simple__block__title">3. <? echo GetMessage("PAY_SYSTEM"); ?></div>
                        <?
                        foreach ($arResult["PAY_SYSTEM"] as $arPaySystem) {
                            if ($arPaySystem['ID'] == 3 && $_REQUEST['PERSON_TYPE'] == '2') {
                                continue;
                            }
                            if ($arPaySystem['ID'] == 5 && $_REQUEST['PERSON_TYPE'] == '2') {
                                $arPaySystem["CHECKED"] = 'Y';
                            }

                            if ($_REQUEST['simple_order_form']['DELIVERY'] == '4' && $arPaySystem['ID'] == 3) {
                                $sDisplay = 'style="display: none;"';
                            } else {
                                $sDisplay = '';
                            }
                            ?>
                            <div class="order-simple__field" <?= $sDisplay; ?>>
                                <label for="pay_system_<?= $arPaySystem["ID"] ?>">
                                    <input type="radio"
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
                                    <div class="left-total-item_title"><a href="<?= $basketItem['DETAIL_PAGE_URL']?>"><?= $basketItem['NAME']?></a></div>
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
    var sTemplateFolder = '<?= $this->GetFolder();?>';
    objOrderForm.init();
</script>



