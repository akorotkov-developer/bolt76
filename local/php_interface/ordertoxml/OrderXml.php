<?php

use Bitrix\Sale;
use \Bitrix\Main\Loader;

/**
 * Класс для генерации XML для СБИС++
 */
class OrderXml
{
    /**
     * Параметры заказа
     * @var array
     */
    private $arOrderParams = [];
    private $iOrderId;
    private $sFio;
    private $sPhone;
    private $sEmail;

    // Данные компании
    private $companyName;
    private $companyAddr;
    private $companyInn;
    private $companyKpp;

    // Название доставки
    private $deliveryName;

    // Данные доставки
    private $delivFioRecipient;
    private $delivContactPhoneRecipient;
    private $delivTime;
    private $delivAddress;
    private $delivCompanyName;
    private $delivPhoneTerminalReicipient;
    private $delivFioTerminalRecipient;
    private $delivPassportTerminalRecipient;
    private $delivTerminalAddress;

    // Название платежной системы
    private $paySystemName;

    // Комментарий пользователя
    private $userComment;

    // Файл, прикрепленные к заказу
    private $orderAttachFile;

    // Id покупателя
    private $iUserId;

    /**
     * Получение праметров заказа
     * @param int $iOrderId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct(int $iOrderId)
    {
        Loader::includeModule('sale');
        Loader::includeModule('iblock');

        $this->iOrderId = $iOrderId;
        $obOrder = Sale\Order::load($iOrderId);
        $this->iUserId = $obOrder->getUserId();
        $propertyCollection = $obOrder->getPropertyCollection();
        $this->sFio = $propertyCollection->getProfileName()->getFieldValues()['VALUE'];
        $this->sPhone = $propertyCollection->getPhone()->getFieldValues()['VALUE'];
        $this->sEmail = $propertyCollection->getUserEmail()->getFieldValues()['VALUE'];

        // Данные компании
        if ($obOrder->getPersonTypeId() == '2') {
            $this->companyName = $propertyCollection->getItemByOrderPropertyCode('COMPANY')->getFieldValues()['VALUE'];
            $this->companyAddr = $propertyCollection->getItemByOrderPropertyCode('COMPANY_ADR')->getFieldValues()['VALUE'];
            $this->companyInn = $propertyCollection->getItemByOrderPropertyCode('INN')->getFieldValues()['VALUE'];
            $this->companyKpp = $propertyCollection->getItemByOrderPropertyCode('KPP')->getFieldValues()['VALUE'];
        }

        // Способы доставки
        $shipmentCollection = $obOrder->getShipmentCollection();
        foreach($shipmentCollection as $shipment)
        {
            $this->deliveryName = $shipment->getDeliveryName(); //тут мы уже получили имя доставки
        }

        // Данные доставки
        $this->delivFioRecipient = $propertyCollection->getItemByOrderPropertyCode('FIO_RECIPIENT')->getFieldValues()['VALUE'];
        $this->delivContactPhoneRecipient = $propertyCollection->getItemByOrderPropertyCode('CONTACT_PHONE_RECIPIENT')->getFieldValues()['VALUE'];
        $this->delivTime = $propertyCollection->getItemByOrderPropertyCode('DESIRED_DELIVERY_TIME')->getFieldValues()['VALUE'];
        $this->delivAddress = $propertyCollection->getItemByOrderPropertyCode('ADDRESS')->getFieldValues()['VALUE'];
        $this->delivCompanyName = $propertyCollection->getItemByOrderPropertyCode('TRANSPORT_COMPANY')->getFieldValues()['VALUE'];
        $this->delivPhoneTerminalReicipient = $propertyCollection->getItemByOrderPropertyCode('RECIPIENT_PHONE')->getFieldValues()['VALUE'];
        $this->delivFioTerminalRecipient = $propertyCollection->getItemByOrderPropertyCode('TRANSPORT_RECIPIENT_FULL_NAME')->getFieldValues()['VALUE'];
        $this->delivPassportTerminalRecipient = $propertyCollection->getItemByOrderPropertyCode('PASSPORT_DATA_RECIPIENT')->getFieldValues()['VALUE'];
        $this->delivTerminalAddress = $propertyCollection->getItemByOrderPropertyCode('TERMINAL_ADDRESS')->getFieldValues()['VALUE'];

        // Название платежной системы
        $paymentCollection = $obOrder->getPaymentCollection();
        foreach ($paymentCollection as $payment) {
            $this->paySystemName = $payment->getPaymentSystemName();
        }

        // Комментарий пользователя
        $this->userComment = $obOrder->getField('USER_DESCRIPTION');

        // Файл, прикрепленный к заказу
        $this->orderAttachFile = $propertyCollection->getItemByOrderPropertyCode('FILE_WITH_BANKING_DETAILS')->getFieldValues()['VALUE'];

        // Получаем товары в корзине с их ценой и количеством
        $dbBasketItems = \CSaleBasket::GetList(
            [],
            ['ORDER_ID' => $iOrderId],
            false,
            false,
            []
        );

        $arCartProductItems = [];
        while ($arResult = $dbBasketItems->Fetch()) {
            $arCartProductItems[$arResult['PRODUCT_ID']]['PRICE'] = $arResult['PRICE'];
            $arCartProductItems[$arResult['PRODUCT_ID']]['QUANTITY'] = $arResult['QUANTITY'];
        }
        $arProductIds = array_keys($arCartProductItems);

        //Получаем товары из инфоблока для определния свойств, которые должны добавится в XML файл
        $dbResult = \CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => 1,
                'ID' => $arProductIds
            ],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_Nomenklaturniy_nomer', 'PROPERTY_UNITS', 'PROPERTY_Naimenovanie', 'PROPERTY_PRICE_OPT', 'PROPERTY_ARTICUL']
        );

        $arIblockItems = [];
        while ($arResult = $dbResult->Fetch()) {
            $arIblockItems[] = $arResult;
        }

        //Соединим два массива
        $arResultItems = [];
        foreach ($arIblockItems as $arItem) {
            $arResultItems[$arItem['ID']] = $arItem;
            $arResultItems[$arItem['ID']]['PRICE'] = $arCartProductItems[$arItem['ID']]['PRICE'];
            $arResultItems[$arItem['ID']]['QUANTITY'] = $arCartProductItems[$arItem['ID']]['QUANTITY'];
        }

        $this->arOrderParams = $arResultItems;
    }

    /**
     * Метод создает XML файл для СБИС++
     * @return false|int
     */
    public function createXml($isKiosk = false)
    {
        $arResultItems = $this->arOrderParams;

        // Формируем XML документ для СБИС++
        $XML_time = date("Y-m-d") . "T" . date("H:i:s");
        $XML = '<?xml version="1.0" encoding="Windows-1251"?>
                    <ФайлОбмена ВерсияФормата="1.0" ДатаВыгрузки="' . $XML_time . '" Комментарий="">
                        <ПравилаОбмена/>
                        <Объект Нпп="1" Тип="СправочникСсылка.Лица">
                            <Свойство Имя="ЮрФизЛицо" Тип="Строка">
                                <Значение>Организация</Значение>
                            </Свойство>
                            <Свойство Имя="ИНН" Тип="Строка">
                                <Значение>ЗАКАЗ</Значение>
                            </Свойство>
                            <Свойство Имя="КПП" Тип="Строка">
                                <Пусто/>
                            </Свойство>
                            <Свойство Имя="КодПоОКПО" Тип="Строка">
                                <Пусто/>
                            </Свойство>
                            <Свойство Имя="Родитель" Тип="Строка">
                                <Значение>Частные лица</Значение>
                            </Свойство>
                            <Свойство Имя="НаименованиеПолное" Тип="Строка">
                                <Значение>ЗАКАЗ С САЙТА</Значение>
                            </Свойство>
                            <Свойство Имя="Наименование" Тип="Строка">
                                <Значение>Центральный склад</Значение>
                            </Свойство>
                            <Свойство Имя="Комментарий" Тип="Строка">
                                <Пусто/>
                            </Свойство>
                        </Объект>
                        <Объект Нпп="2" Тип="Исходящие счета">
                            <Свойство Имя="Дата" Тип="Дата">
                                <Значение>' . $XML_time . '</Значение>
                            </Свойство>
                            <Свойство Имя="ВалютаДокумента" Тип="Строка">
                                <Пусто/>
                            </Свойство>
                            <Свойство Имя="КурсВзаиморасчетов" Тип="Число">
                                <Пусто/>
                            </Свойство>
                            <Свойство Имя="Контрагент" Тип="СправочникСсылка.Лица">
                                <Нпп>1</Нпп>
                            </Свойство>
                            <Свойство Имя="Склад" Тип="Число">
                                <Значение>9</Значение>
                            </Свойство>
                            <Свойство Имя="СуммаДокумента" Тип="Число">
                                <Значение>0</Значение>
                            </Свойство>
                            <Свойство Имя="Комментарий" Тип="Строка">
                                <Значение>Заказ ' . $this->iOrderId . ', Имя: ' . $this->sFio . ', Телефон: ' . $this->sPhone . '</Значение>
                            </Свойство>
                            <Свойство Имя="Номер" Тип="Строка">
                                <Пусто/>
                            </Свойство>
                        </Объект>
                    ';

        $n = 3;
        foreach ($arResultItems as $arItem) {
            $XML .= '<Объект Нпп="' . ($n) . '" Тип="СправочникСсылка.Складская картотека">
                        <Свойство Имя="Склад реализации/именного списания" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Учет по продажной цене" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Склад хранения" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Cклад з. путевых листов" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Учет по местам хранения" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Учет по видам собственности" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Склад" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="Группа складов" Тип="Булево"><Значение>false</Значение></Свойство>
                        <Свойство Имя="НаименованиеПолное" Тип="Строка"><Значение>' . $arItem["PROPERTY_NAIMENOVANIE_VALUE"] . '</Значение></Свойство>
                        <Свойство Имя="БазоваяЕдиницаИзмерения" Тип="Строка"><Значение>' . $arItem["PROPERTY_UNITS_VALUE"] . '</Значение></Свойство>
                        <Свойство Имя="Код" Тип="Строка"><Значение>' . $arItem["PROPERTY_NOMENKLATURNIY_NOMER_VALUE"] . '</Значение></Свойство>
                        <Свойство Имя="Комментарий" Тип="Строка"><Пусто/></Свойство>
                        <Свойство Имя="Наименование" Тип="Строка"><Значение>' . $arItem["NAME"] . '</Значение></Свойство>
                    </Объект>
                    <Объект Нпп="' . ($n + 1) . '" Тип="СправочникСсылка.Наименования счета">
                        <Свойство Имя="ДокументНаименования" Тип="Исходящие счета"><Нпп>2</Нпп></Свойство>
                        <Свойство Имя="Количество" Тип="Число"><Значение>' . $arItem['QUANTITY'] . '</Значение></Свойство>
                        <Свойство Имя="Номенклатура" Тип="СправочникСсылка.Складская картотека"><Нпп>' . ($n) . '</Нпп></Свойство>
                        <Свойство Имя="СуммаНДС" Тип="Число"><Пусто/></Свойство>
                        <Свойство Имя="Сумма" Тип="Число"><Значение>' . $arItem['QUANTITY'] * $arItem['PRICE'] . '</Значение></Свойство>
                        <Свойство Имя="Цена" Тип="Число"><Значение>' . $arItem['PRICE'] . '</Значение></Свойство>
                    </Объект>';
            $n += 2;
        }
        $XML .= '</ФайлОбмена>';
        $XML = iconv("UTF-8", "Windows-1251", $XML);

        if ($isKiosk) {
            return file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/cart/Исходящие счета киоск №" . $this->iOrderId . ".xml", $XML);
        } else {
            return file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/cart/Исходящие счета.xml", $XML);
        }
    }

    /**
     * Метод получает текст письма с информацие о заказе
     * @return string
     */
    public function getMailText(): string
    {
        $styles = Array(
            "td" => "padding: 3px 5px 3px 5px; border: solid 1px #E0E0E0;",
            "td20" => "width:20px;",
            "td100" => "width:100px;",
            "tr0" => "background: #ffffff;",
            "tr1" => "background: #F0F0F0;"
        );

        $text = '<table style="border-spacing:0px;border-collapse:collapse;width:100%;font-size:12px;font-family:Arial;">
					<tr style="background:#FF7920;">
                        <td style="' . $styles["td"] . $styles["td20"] . '">№</td>
                        <td style="' . $styles["td"] . $styles["td100"] . '">Артикул</td>
                        <td style="' . $styles["td"] . '">Наименование</td>
                        <td style="' . $styles["td"] . $styles["td100"] . '">Количество</td>
                        <td style="' . $styles["td"] . $styles["td100"] . '">Цена</td>
                        <!--<td style="' . $styles["td"] . $styles["td100"] . '">Цена опт</td>-->
					</tr>';

        $j = 1;
        $totalPrice = 0;
        foreach ($this->arOrderParams as $arItem) {
            $text .= '<tr style="' . $styles["tr" . ($i++ % 2)] . '">
						<td style="' . $styles["td"] . $styles["td20"] . '">' . ($j++) . '.</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . $arItem["PROPERTY_ARTICUL_VALUE"] . '</td>
						<td style="' . $styles["td"] . '">' . $arItem["PROPERTY_NAIMENOVANIE_VALUE"] . '</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . $arItem['QUANTITY'] . '</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . round((float)$arItem["PRICE"], 2) . ' руб</td>
						<!--<td style="' . $styles["td"] . $styles["td100"] . '">' . round((float)$arItem["PROPERTY_PRICE_OPT_VALUE"], 2) . ' руб</td>-->
						</tr>';
            $totalPrice += (round((float)$arItem["PRICE"], 2) * $arItem['QUANTITY']);
        }

        $text .= '<tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><b>Итого:</b></td>
                    <td><b>' . $totalPrice . ' руб</b></td>
                  ';

        $text .= '</table>';

        $items_list = $text;

        $text = "";
        $text .= "<p style='padding:5px;background: #FFE5D4;color:#000;margin-bottom:10px;'>Информационное сообщение с сайта www.strprofi.ru</p>";
        $text .= "<h3 style='padding:5px;color:#000;margin-bottom:10px;'>Клиент, " . $this->sFio . "!</h3>";
        $text .= "<p style='padding: 5px;'>Разместил заказ № " . $this->iOrderId . " от " . date("d.m.Y H:i") . ".<br/>Его состав:</p>";
        $text .= $items_list;
        $text .= "<p><b>Данные покупателя:</b></p>";
        $text .= "ФИО: " . $this->sFio . "<br/>";
        $text .= "Email: " . $this->sEmail . "<br/>";
        $text .= "Телефон: " . $this->sPhone . "<br/>";


        if ($this->companyName != '' || $this->companyAddr != '' || $this->companyInn != '' || $this->companyKpp != '') {
            $text .= "<p><b>Данные компании:</b></p>";

            if ($this->companyName != '') {
                $text .= "Название компании: " . $this->companyName . "<br/>";
            }
            if ($this->companyAddr != '') {
                $text .= "Юридический адрес: " . $this->companyAddr . "<br/>";
            }
            if ($this->companyInn != '') {
                $text .= "ИНН: " . $this->companyInn . "<br/>";
            }
            if ($this->companyKpp != '') {
                $text .= "КПП: " . $this->companyKpp . "<br/>";
            }
        }

        $text .= "<p><b>Данные доставки:</b></p>";
        $text .= "Вариант доставки: " . $this->deliveryName . "<br/>";

        if ($this->delivFioRecipient != '') {
            $text .= "ФИО получателя: " . $this->delivFioRecipient . "<br/>";
        }
        if ($this->delivContactPhoneRecipient != '') {
            $text .= "Контанктый телефон получателя: " . $this->delivContactPhoneRecipient . "<br/>";
        }
        if ($this->delivTime != '') {
            $text .= "Желаемое время доставки: " . $this->delivTime . "<br/>";
        }
        if ($this->delivAddress != '') {
            $text .= "Адрес доставки (масимально подробно): " . $this->delivAddress . "<br/>";
        }
        if ($this->delivCompanyName != '') {
            $text .= "Название транспортной компании: " . $this->delivCompanyName . "<br/>";
        }
        if ($this->delivPhoneTerminalReicipient != '') {
            $text .= "Контактный телефон получателя: " . $this->delivPhoneTerminalReicipient . "<br/>";
        }
        if ($this->delivFioTerminalRecipient != '') {
            $text .= "ФИО получателя: " . $this->delivFioTerminalRecipient . "<br/>";
        }
        if ($this->delivPassportTerminalRecipient != '') {
            $text .= "Паспортные данные получателя: " . $this->delivPassportTerminalRecipient . "<br/>";
        }
        if ($this->delivTerminalAddress != '') {
            $text .= "Адрес доставки: " . $this->delivTerminalAddress . "<br/>";
        }

        $text .= "<p><b>Способ оплаты:</b></p>";
        $text .= "Название способа оплаты: " . $this->paySystemName . "<br/>";

        $text .= "<p><b>Комментарий пользователя:</b></p>";
        $text .=  $this->userComment . "<br/>";

        echo $text;

        return $text;
    }

    /**
     * Получить список товаров в заказе
     * @return string
     */
    public function getOrderTableList(): string
    {
        $text = '<table style="border-spacing:0px;border-collapse:collapse;width:100%;font-size:12px;font-family:Arial;">
					<tr style="background:#bad3df;">
                        <td style="border: 1px solid;">№</td>
                        <td style="border: 1px solid;">Артикул</td>
                        <td style="border: 1px solid;">Наименование</td>
                        <td style="border: 1px solid;">Количество</td>
                        <td style="border: 1px solid;">Цена</td>
					</tr>';

        $j = 1;
        $totalPrice = 0;
        foreach ($this->arOrderParams as $arItem) {
            $text .= '<tr>
						<td style="border: 1px solid;">' . ($j++) . '.</td>
						<td style="border: 1px solid;">' . $arItem["PROPERTY_ARTICUL_VALUE"] . '</td>
						<td style="border: 1px solid;">' . $arItem["PROPERTY_NAIMENOVANIE_VALUE"] . '</td>
						<td style="border: 1px solid;">' . $arItem['QUANTITY'] . '</td>
						<td style="border: 1px solid;">' . round((float)$arItem["PRICE"], 2) . ' руб</td>
					  </tr>';
            $totalPrice += (round((float)$arItem["PRICE"], 2) * $arItem['QUANTITY']);
        }

        $text .= '<tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="border: 1px solid;"><b>Итого:</b></td>
                    <td style="border: 1px solid;"><b>' . $totalPrice . ' руб</b></td>
                  ';

        $text .= '</table>';

        return $text;
    }

    /**
     * Функция получения прикрепленного файла
     * @return mixed
     */
    public function getAttachFile()
    {
        return $this->orderAttachFile;
    }

    /**
     * Установить пользовательские поля для текущего пользователя
     */
    public function setFieldsForUser()
    {
        $obUser = new \CUser;

        if ($this->companyName != '') {
            $obUser->Update($this->iUserId, ['UF_COMPANY_NAME' => $this->companyName]);
        }
        if ($this->companyAddr != '') {
            $obUser->Update($this->iUserId, ['UF_YUR_ADDRESS' => $this->companyAddr]);
        }
        if ($this->companyInn != '') {
            $obUser->Update($this->iUserId, ['UF_INN' => $this->companyInn]);
        }
        if ($this->companyKpp != '') {
            $obUser->Update($this->iUserId, ['UF_KPP' => $this->companyKpp]);
        }
    }
}