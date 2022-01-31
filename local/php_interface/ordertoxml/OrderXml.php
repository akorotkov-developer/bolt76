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
        $propertyCollection = $obOrder->getPropertyCollection();
        $this->sFio = $propertyCollection->getProfileName()->getFieldValues()['VALUE'];
        $this->sPhone = $propertyCollection->getPhone()->getFieldValues()['VALUE'];
        $this->sEmail = $propertyCollection->getUserEmail()->getFieldValues()['VALUE'];

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
            ['ID', 'NAME', 'PROPERTY_NOMNOMER', 'PROPERTY_UNITS', 'PROPERTY_Naimenovanie', 'PROPERTY_PRICE_OPT', 'PROPERTY_ARTICUL']
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
    public function createXml()
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
                        <Свойство Имя="Код" Тип="Строка"><Значение>' . $arItem["PROPERTY_NOMNOMER_VALUE"] . '</Значение></Свойство>
                        <Свойство Имя="Комментарий" Тип="Строка"><Пусто/></Свойство>
                        <Свойство Имя="Наименование" Тип="Строка"><Значение>' . $arItem["NAME"] . '</Значение></Свойство>
                    </Объект>
                    <Объект Нпп="' . ($n + 1) . '" Тип="СправочникСсылка.Наименования счета">
                        <Свойство Имя="ДокументНаименования" Тип="Исходящие счета"><Нпп>2</Нпп></Свойство>
                        <Свойство Имя="Количество" Тип="Число"><Значение>' . $arItem['QUANTITY'] . '</Значение></Свойство>
                        <Свойство Имя="Номенклатура" Тип="СправочникСсылка.Складская картотека"><Нпп>' . ($n) . '</Нпп></Свойство>
                        <Свойство Имя="СуммаНДС" Тип="Число"><Пусто/></Свойство>
                        <Свойство Имя="Сумма" Тип="Число"><Значение>0</Значение></Свойство>
                        <Свойство Имя="Цена" Тип="Число"><Значение>0</Значение></Свойство>
                    </Объект>';
            $n += 2;
        }
        $XML .= '</ФайлОбмена>';
        $XML = iconv("UTF-8", "Windows-1251", $XML);

        return file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/cart/Исходящие счета.xml", $XML);
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
					<td style="' . $styles["td"] . $styles["td100"] . '">Цена роз</td>
					<td style="' . $styles["td"] . $styles["td100"] . '">Цена опт</td>
					</tr>';

        $j = 1;
        foreach ($this->arOrderParams as $arItem) {
            echo '<pre>';
            var_dump($arItem);
            echo '</pre>';
            $text .= '<tr style="' . $styles["tr" . ($i++ % 2)] . '">
						<td style="' . $styles["td"] . $styles["td20"] . '">' . ($j++) . '.</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . $arItem["PROPERTY_ARTICUL_VALUE"] . '</td>
						<td style="' . $styles["td"] . '">' . $arItem["PROPERTY_NAIMENOVANIE_VALUE"] . '</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . $arItem['NAME'] . '</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . $arItem["PRICE"] . ' руб</td>
						<td style="' . $styles["td"] . $styles["td100"] . '">' . round((float)$arItem["PROPERTY_PRICE_OPT_VALUE"], 2) . ' руб</td>
						</tr>';
        }

        $text .= '</table>';

        $items_list = $text;

        $text = "";
        $text .= "<p style='padding:5px;background: #FFE5D4;color:#000;margin-bottom:10px;'>Информационное сообщение с сайта www.strprofi.ru</p>";
        $text .= "<h3 style='padding:5px;color:#000;margin-bottom:10px;'>Клиент, " . $this->sFio . "!</h3>";
        $text .= "<p style='padding: 5px;'>Разместил заказ № " . $this->iOrderId . " от " . date("d.m.Y H:i") . ".<br/>Его состав:</p>";
        $text .= $items_list;
        $text .= "<p><b>Данные:</b></p>";
        $text .= "ФИО: " . $this->sFio . "<br/>";
        $text .= "Email: " . $this->sEmail . "<br/>";
        $text .= "Телефон: " . $this->sPhone;

        echo $text;

        return $text;
    }
}