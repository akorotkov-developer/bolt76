<?php

use \Bitrix\Main\Loader;

/**
 * Класс импорта товаров
 */
class Import
{
    public $startTime;
    public $iblock;
    public $host;
    public $url;
    public $productsFile;
    public $arProducts = [];
    public $productSections = [];
    public $arDynamicPropsMap = [];
    public $xmlCat;
    public $iPropAvailbleId;

    /**
     * Установка начальных параметров
     */
    public function __construct()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('sale');

        $this->startTime = date("d.m.Y H:i:s");
        $this->iblock = 1;

        // Определяем ID свойства в наличии
        $dbResult = CIBlockProperty::GetPropertyEnum('AVAILABLE', '', ['IBLOCK_ID' => 1]);

        if ($arResult = $dbResult->Fetch()) {
            $this->iAvailablePropId = $arResult['ID'];
        }

        if ($_SERVER['SERVER_PORT'] == '443') {
            $sHttp = 'https://';
        } else {
            $sHttp = 'http://';
        }

        $this->host = $sHttp . $_SERVER['SERVER_NAME'] . '/';
        $this->url = $this->host . "import/export/";

        $this->xmlCat = simplexml_load_file("./cat_new.xml");
        $this->productsFile = file('catalog_new.txt');
    }

    /**
     * Функция печати сообщения
     * @param string $sString
     * @param bool $error
     */
    public function echo(string $sString, bool $error = false)
    {
        if (!$error) {
            echo $sString . '<br>';
        } else {
            if ($sString != '') {
                echo '<span style="color: red">' . $sString . '</span><br>';
            }
        }
    }

    /**
     * Старт импорта
     */
    public function startImport()
    {
        global $DB;
        $bSuccess = true;

        if ($this->productsFile == false) {
            $this->echo('Ошибка открытия файла с товарами', true);
        } else {
            $this->echo('Начало импорта ' . date("d.m.Y H:i:s"));

            $DB->StartTransaction();
            try {
                // Устанавливаем массив с продуктами
                $this->setArProducts();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии получения списка товаров \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }

            $DB->StartTransaction();
            try {
                // Установить динамические свойства
                $this->checkDynamicProps();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии установки динамических свойств \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }

            $DB->StartTransaction();
            try {
                // Загрузить разделы для товаров
                $this->setSections();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии добавлени/удаления элементов \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }

            $DB->StartTransaction();
            try {
                // Установить символьные коды для товаров
                $this->setCodesForProducts();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии установки символьных кодов товаров \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }

            $DB->StartTransaction();
            try {
                // Сделать из элементов инфоблока товары с ценами и параметрами
                $this->makeProducts();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии формирования товаров с ценами и параметрами \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }

            $DB->StartTransaction();
            try {
                // Установить коэфициенты единицы измерения для всех продуктов
                $this->setRatio();

                // Сохранение данных
                $DB->Commit();
            } catch( Exception $ex ) {
                // Откат изменений
                $DB->Rollback();

                // Сброс флага успеха
                $bSuccess = false;

                echo 'Ошибка на стадии установки коэффициентов кратности \n';
                echo 'An error occurred: ' . $ex->getMessage();
            }
        }

        return $bSuccess;
    }

    /**
     * Сделать из элементов инфоблока товары с ценами и параметрами
     */
    public function makeProducts()
    {
        //1. Получаем все товары на сайте
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'INCLUDE_SUBSECTIONS' => 'Y'
        ];

        $dbRez = CIBlockElement::GetList(
            ['SORT'=>'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'PROPERTY_PRICE_OPT', 'PROPERTY_PRICE_OPT2', 'PROPERTY_PRICE',
                'PROPERTY_Ostatok', 'PROPERTY_UNITS', 'PROPERTY_KRATNOST']
        );

        $arItems = [];
        while($arRez = $dbRez->Fetch())
        {
            $arItems[] = $arRez;
        }

        //2. Получаем все цены на сайте
        $dbRez = CPrice::GetList(
            [],
            []
        );

        $arPricesItems = [];
        while ($arRez = $dbRez->Fetch())
        {
            $arPricesItems[$arRez['PRODUCT_ID'] . '_' . $arRez['CATALOG_GROUP_ID']] = $arRez;
        }

        //4. Получаем все продукты на сайте
        $dbRez = CCatalogProduct::GetList(
            [],
            []
        );

        $arProducts = [];
        while($arRez = $dbRez->Fetch()) {
            $arProducts[$arRez['ID']] = $arRez;
        }

        //3. Переберем все товары и запишем туда цены и доступное количество
        $PRICE_BASE_ID = 1; //Базовая цена
        $PRICE_OPT_ID = 2; //Оптовая цена
        $PRICE_OPT2_ID = 3; //Оптовая цена 2
        foreach ($arItems as $arItem) {
            //Сначала проверим существует ли такой продукт в системе и если не существует, то создадим его
            if (!$arProducts[$arItem['ID']]) {
                \Bitrix\Catalog\Model\Product::add(
                    [
                        'ID' => $arItem['ID'],
                        'VAT_ID' => 1, //выставляем тип ндс (задается в админке)
                        'VAT_INCLUDED' => 'Y' //НДС входит в стоимость
                    ]
                );
            }

            //Запишем цены
            //Базовая цена
            if ($arItem['PROPERTY_PRICE_VALUE']) {
                $arFields = [
                    'PRODUCT_ID' => $arItem['ID'],
                    "CATALOG_GROUP_ID" => $PRICE_BASE_ID,
                    "PRICE" => $arItem['PROPERTY_PRICE_VALUE'],
                    "CURRENCY" => "RUB",
                ];

                if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]) {
                    CPrice::Update(
                        $arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]['ID'],
                        $arFields
                    );
                } else {
                    CPrice::Add(
                        $arFields
                    );
                }
            }

            //Оптовая цена
            if ($arItem['PROPERTY_PRICE_OPT_VALUE']) {
                $arFields = [
                    'PRODUCT_ID' => $arItem['ID'],
                    "CATALOG_GROUP_ID" => $PRICE_OPT_ID,
                    "PRICE" => $arItem['PROPERTY_PRICE_OPT_VALUE'],
                    "CURRENCY" => "RUB",
                ];
                if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT_ID]) {
                    CPrice::Update(
                        $arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT_ID]['ID'],
                        $arFields
                    );
                } else {
                    CPrice::Add(
                        $arFields
                    );
                }
            }

            //Оптовая цена 2
            if ($arItem['PROPERTY_PRICE_OPT2_VALUE']) {
                $arFields = [
                    'PRODUCT_ID' => $arItem['ID'],
                    "CATALOG_GROUP_ID" => $PRICE_OPT2_ID,
                    "PRICE" => $arItem['PROPERTY_PRICE_OPT2_VALUE'],
                    "CURRENCY" => "RUB",
                ];
                if ($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT2_ID]) {
                    CPrice::Update(
                        $arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT2_ID]['ID'],
                        $arFields
                    );
                } else {
                    CPrice::Add(
                        $arFields
                    );
                }
            }

            //Записываем доступное количество товара
            switch ($arItem['PROPERTY_UNITS_VALUE']) {
                case 'тыс. шт':
                    $iMeasure = 6;
                    break;
                case 'упак':
                    $iMeasure = 7;
                    break;
                case 'кг':
                    $iMeasure = 4;
                    break;
                case 'м2':
                    $iMeasure = 8;
                    break;
                case 'компл':
                    $iMeasure = 9;
                    break;
                case 'пог. м':
                    $iMeasure = 10;
                    break;
                default:
                    $iMeasure = 5;
                    break;
            }

            CCatalogProduct::Update(
                $arItem['ID'],
                [
                    'QUANTITY' => $arItem['PROPERTY_Ostatok_VALUE'],
                    'MEASURE' => $iMeasure
                ]
            );
        }
    }

    /**
     * Установка символьных кодов для всех товров на сайте
     */
    public function setCodesForProducts()
    {
        //1. Получаем все товары на сайте
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'INCLUDE_SUBSECTIONS' => 'Y'
        ];

        $dbRez = CIBlockElement::GetList(
            ['SORT'=>'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'NAME']
        );

        $arItems = [];
        while($arRez = $dbRez->Fetch())
        {
            $arItems[] = $arRez;
        }

        foreach ($arItems as $item) {
            $elem = new CIBlockElement();
            $elem->Update($item['ID'], ['CODE' => $this->translit($item['NAME'])]);
        }
    }

    /**
     * Транслтерация
     * @param $s
     * @return array|string|string[]
     */
    public function translit($s) {
        $s = (string) $s; // преобразуем в строковое значение
        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
        $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
        $s = trim($s); // убираем пробелы в начале и конце строки
        $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'x','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
        $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
        $s = str_replace(" ", "_", $s); // заменяем пробелы знаком минус
        return $s; // возвращаем результат
    }

    /**
     * Загрузка секций из файлв
     */
    private function setSections()
    {
        if (sizeof($this->xmlCat->category)) {
            $this->sectionWalker($this->xmlCat, 0);

            $arFilter = [
                'IBLOCK_ID' => $this->iblock,
                'ID' => array_keys($this->productSections)
            ];

            $dbList = CIBlockSection::GetList([], $arFilter, false, ["UF_PRICE_ID"]);
            while ($arResult = $dbList->GetNext()) {
                $this->parseSection(
                    $this->productSections[$arResult["ID"]],
                    $arResult["ID"],
                    (int)$arResult["UF_PRICE_ID"],
                    $arResult["DEPTH_LEVEL"],
                    $this->arProducts,
                    $this->arDynamicPropsMap
                );
            }
        }
    }

    /**
     * Рекурсивная функция для обхода разделов
     * @param $xml
     * @param $top
     */
    private function sectionWalker($xml, $top)
    {
        $present = array();
        foreach ($xml->category as $topContent) {

            $image = (int)$topContent->sID;
            if (sizeof($topContent->childs->category) > 0) {
                $desc = str_replace('[BR]', "\n", str_replace('[BR][BR]', "\n", $topContent->desc));
                $ID = $this->addSection(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), $desc);
                $present[] = $ID;
                $this->sectionWalker($topContent->childs, $ID);
            } else {
                $desc = str_replace('[BR]', "\n", str_replace('[BR][BR]', "\n", $topContent->desc));
                $ID = $this->addSection(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), $desc, true);
                $present[] = $ID;
                $this->removeSection($ID, array());
            }

            $this->productSections[$ID] = (int)$topContent->ID;
        }

        $this->removeSection($top, $present);
    }

    /**
     * Функция добавления раздела
     * @param $name
     * @param $internal
     * @param $parent
     * @param $photo
     * @param $sort
     * @param $price_id
     * @param $descr
     * @param false $isItem
     * @return false|int|mixed
     */
    private function addSection($name, $internal, $parent, $photo, $sort, $price_id, $descr, $isItem = false)
    {
        $newName = preg_replace("/^([0-9]{1,2}\. ?[0-9]?\.? ?[0-9]* ?)/", "", trim($name));
        $code = $this->rus2lat($newName);

        $obSection = new CIBlockSection;

        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'UF_ROWID' => $internal,
            "SECTION_ID" => $parent
        ];

        $dbList = CIBlockSection::GetList([], $arFilter, false,["UF_*"]);
        $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photo . '.jpg';
        if ($arResult = $dbList->GetNext()) {
            $ID = $arResult["ID"];
            $arUpdate = [
                "NAME" => $newName,
                "UF_ORIGINAL_NAME" => $name,
                "UF_PRICE_ID" => $price_id,
                "DESCRIPTION" => $descr,
                "SORT" => $sort,
                "CODE" => $code,
            ];

            $arUpdate["PICTURE"] = false;
            $arUpdate["UF_TEMPLATE"] = 0;

            if ($photo != 0) {
                if ($this->testphile($picfile)) {
                    $pic = CFile::MakeFileArray($picfile);
                    if ($pic["size"] > 0) {
                        $arUpdate["PICTURE"] = $pic;
                        $arUpdate["UF_TEMPLATE"] = 1;
                    }
                }
            }

            $obSection->Update($ID, $arUpdate);
        } else {
            $arAdd = [
                "IBLOCK_ID" => $this->iblock,
                "IBLOCK_SECTION_ID" => $parent,
                "SORT" => $sort,
                "NAME" => $newName,
                "UF_ORIGINAL_NAME" => $name,
                "CODE" => $code,
                "DESCRIPTION" => $descr,
                "UF_PHOTO_ID" => $photo,
                "UF_PRODUCT" => ($isItem ? '1' : '0'),
                "UF_PRICE_ID" => $price_id,
                "UF_ROWID" => $internal,
                "UF_TEMPLATE" => 0
            ];
            if ($photo != 0) {
                if ($this->testphile($picfile)) {
                    $pic = CFile::MakeFileArray($picfile);
                    if ($pic["size"] > 0) {
                        $arAdd["PICTURE"] = $pic;
                        $arAdd["UF_TEMPLATE"] = 1;
                    }
                }
            }

            $ID = $obSection->Add($arAdd);
        }
        return $ID;
    }

    /**
     * Проверка существования файла
     * @param $url
     * @return bool
     */
    public function testphile($url): bool
    {
        if (@fopen($url, "r")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Функция для какого то проеобраазования символов
     * @param $text
     * @return false|string
     */
    public function rus2lat($text)
    {
        $tr = [
            "Ґ" => "G", "Ё" => "YO", "Є" => "E", "Ї" => "YI", "І" => "I",
            "і" => "i", "ґ" => "g", "ё" => "yo", "№" => "#", "є" => "e",
            "ї" => "yi", "А" => "A", "Б" => "B", "В" => "V", "Г" => "G",
            "Д" => "D", "Е" => "E", "Ж" => "ZH", "З" => "Z", "И" => "I",
            "Й" => "Y", "К" => "K", "Л" => "L", "М" => "M", "Н" => "N",
            "О" => "O", "П" => "P", "Р" => "R", "С" => "S", "Т" => "T",
            "У" => "U", "Ф" => "F", "Х" => "H", "Ц" => "TS", "Ч" => "CH",
            "Ш" => "SH", "Щ" => "SCH", "Ъ" => "'", "Ы" => "YI", "Ь" => "",
            "Э" => "E", "Ю" => "YU", "Я" => "YA", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "zh",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "  " => " ", " - " => "_", "- " => "_", " -" => "_", "." => "_", "," => "_", " " => "_", "-" => "_", " " => "_"
        ];
        $text = preg_replace("/[^- _a-zA-Z0-9абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ]+/i", '', trim($text));
        $text = strtr($text, $tr);
        $text = str_replace(" ", '_', $text);
        $text = str_replace("__", '_', $text);

        return substr(mb_strtolower($text), 0, 50);
    }

    /**
     * Фукция для удаления разделов из категории $category кроме $presentedCats)
     * @param $category
     * @param $presentedCats
     */
    private function removeSection($category, $presentedCats)
    {
        $obSection = new CIBlockSection;
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            '!ID' => $presentedCats,
            'SECTION_ID' => $category
        ];
        $dbList = CIBlockSection::GetList([], $arFilter, false);
        while ($arResult = $dbList->GetNext()) {
            $obSection->Delete($arResult["ID"]);
        }
    }

    /**
     * @param $strCatID
     * @param $siteCatID
     * @param $price_id
     * @param $depth
     * @param $items
     * @param $arDynamicPropsMap
     */
    private function parseSection($strCatID, $siteCatID, $price_id, $depth, $items, $arDynamicPropsMap)
    {
        $total = 0;

        $present = [];

        if (isset($items[$strCatID])) {
            foreach ($items[$strCatID] as $item) {
                $ID = $this->addUpdateElement($item, $siteCatID, $arDynamicPropsMap);
                $present[] = $ID;
                $total++;
            }
        }

        // Удалить лишние элементы, которые не попали в файл выгрузки
        $el = new CIBlockElement;

        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'SECTION_ID' => $siteCatID,
            '!ID' => $present
        ];

        $res = CIBlockElement::GetList([], $arFilter, false, false, ['ID']);

        while ($ob = $res->GetNext()) {
            $el->Delete($ob['ID']);
        }
    }

    /**
     * Функция для добавления $item в раздел $siteCatId (товаров в раздел)
     * @param $item
     * @param $siteCatID
     * @param $arDynamicPropsMap
     * @return false|mixed
     */
    private function addUpdateElement($item, $siteCatID, $arDynamicPropsMap)
    {
        $sName = ($item['Svertka'] != '') ? $item['Svertka'] : $item['Naimenovanie'];

        $el = new CIBlockElement;
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'PROPERTY_PHOTO_ID',
            'PREVIEW_PICTURE'
        ];
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'PROPERTY_ROWID' => intval($item['ID']),
            'SECTION_ID' => $siteCatID,
        ];

        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($ob = $res->GetNext()) {
            $arUpdate = [
                'NAME' => $sName,
                'PREVIEW_TEXT' => trim(strval($item['Opisanie'])),
                'SORT' => intval($item['PorNomer']),
                'PROPERTY_VALUES' => [
                    'ARTICUL' => $item['Artikul'],
                    'ROWID' => $item['ID'],
                    'PRICE' => (float)str_replace(",", ".", $item['CZena1']),
                    'PRICE_OPT' => (float)str_replace(",", ".", $item['CZena2']),
                    'PRICE_OPT2' => (float)str_replace(",", ".", $item['CZena3']),
                    'Naimenovanie' => $item['Naimenovanie'],
                    'Svertka' => $item['Svertka'],
                    'PHOTO_ID' => $item['Foto'][0],
                    'VES' => $item['Ves'],
                    'UNITS' => $item['EdIzmereniya'],
                    'UPAKOVKA' => $item['VUpakovke'],
                    'UPAKOVKA2' => $item['VUpakovke2'],
                    'Ostatok' => $item['Ostatok'],
                    'SHOW_IN_PRICE' => ($item['show_in_price'] > 0) ? 1 : 0,
                    'SORT_IN_PRICE' => $item['show_in_price'],
                    'AVAILABLE' => ((int)$item['Ostatok'] > 0) ? $this->iAvailablePropId : ''
                ]
            ];

            // Добавим значения динамических свойств в товар
            $arDynamicPropsKeys = array_keys($arDynamicPropsMap);

            foreach ($arDynamicPropsKeys as $sPropCode) {
                if ($item[$sPropCode] != '') {
                    if ($item[$sPropCode] !== 0 && $item[$sPropCode] !== '0') {
                        $arDinamicProps[$sPropCode] = $item[$sPropCode];
                    } else {
                        $arDinamicProps[$sPropCode] = '';
                    }
                }
            }

            $arUpdate['PROPERTY_VALUES'] = array_merge($arUpdate['PROPERTY_VALUES'], $arDinamicProps);

            // Установим фото
            $photo = $item['Foto'][0];
            $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photo . '.jpg';

            $flag_update_pic = false;
            if ($ob["PROPERTY_PHOTO_ID_VALUE"] != $item['Foto'][0]) {
                $flag_update_pic = true;
            }
            if ($ob["PREVIEW_PICTURE"] == NULL) {
                $flag_update_pic = true;
            }
            if ($this->testphile($picfile)) {
                $flag_update_pic = true;
            }

            if (($flag_update_pic) && ($this->testphile($picfile))) {
                $photo = $item['Foto'][0];

                if ($photo != 0) {
                    $propsToUpdate["PHOTO_ID"] = $item['Foto'][0];
                    $pic = CFile::MakeFileArray($picfile);
                    if ($pic["size"] > 0) {
                        $arUpdate['PREVIEW_PICTURE'] = $pic;
                    }
                }

                // Если у нас несколько фото, то добавим их в свойство фотографии (PROPERTY_PHOTOS)
                if (count($item['Foto']) > 1) {
                    array_shift($item['Foto']);

                    foreach ($item['Foto'] as $photoItem) {
                        $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photoItem . '.jpg';
                        if ($this->testphile($picfile)) {
                            $propsToUpdate['PHOTOS'][] = \CFile::MakeFileArray($picfile);
                        }
                    }
                }
            } else {
                $arUpdate['PREVIEW_PICTURE'] = ['del' => 'Y'];
            }

            $el->Update($ob["ID"], $arUpdate);

            // Обнуляем фото
            CIBlockElement::SetPropertyValuesEx($ob["ID"], $this->iblock, ['PHOTOS' => ['VALUE' => '']]);

            // Записываем новые свойства
            CIBlockElement::SetPropertyValuesEx($ob["ID"], $this->iblock, $propsToUpdate);

            $ID = $ob["ID"];
        } else {
            $arLoad = [
                'IBLOCK_ID' => $this->iblock,
                'IBLOCK_SECTION_ID' => $siteCatID,
                'NAME' => $sName,
                'PREVIEW_TEXT' => trim(strval($item['Opisanie'])),
                'SORT' => intval($item['PorNomer']),
                'PROPERTY_VALUES' => [
                    'ARTICUL' => $item['Artikul'],
                    'ROWID' => $item['ID'],
                    'PRICE' => (float)str_replace(",", ".", $item['CZena1']),
                    'PRICE_OPT' => (float)str_replace(",", ".", $item['CZena2']),
                    'PRICE_OPT2' => (float)str_replace(",", ".", $item['CZena3']),
                    'Naimenovanie' => $item['Naimenovanie'],
                    'Svertka' => $item['Svertka'],
                    'PHOTO_ID' => $item['Foto'][0],
                    'VES' => $item['Ves'],
                    'UNITS' => $item['EdIzmereniya'],
                    'UPAKOVKA' => $item['VUpakovke'],
                    'UPAKOVKA2' => $item['VUpakovke2'],
                    'Ostatok' => $item['Ostatok'],
                    'SHOW_IN_PRICE' => ($item['show_in_price'] > 0) ? 1 : 0,
                    'SORT_IN_PRICE' => $item['show_in_price'],
                    'AVAILABLE' => ((int)$item['Ostatok'] > 0) ? $this->iAvailablePropId : ''
                ],
            ];

            // Добавим значения динамических свойств в товар
            $arDynamicPropsKeys = array_keys($arDynamicPropsMap);
            foreach ($arDynamicPropsKeys as $sPropCode) {
                if ($item[$sPropCode] != '') {
                    if ($item[$sPropCode] !== 0 && $item[$sPropCode] !== '0') {
                        $arDinamicProps[$sPropCode] = $item[$sPropCode];
                    } else {
                        $propsToUpdate[$sPropCode] = '';
                    }
                }
            }
            $arLoad['PROPERTY_VALUES'] = array_merge($arLoad['PROPERTY_VALUES'], $arDinamicProps);

            $photo = $item['Foto'][0];
            $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photo . '.jpg';

            if ($photo != 0) {
                if ($this->testphile($picfile)) {
                    $pic = CFile::MakeFileArray($picfile);

                    if ($pic['size'] > 0) {
                        $arLoad['PREVIEW_PICTURE'] = $pic;
                    }
                }
            }

            // Если у нас несколько фото, то добавим их в свойство фотографии (PROPERTY_PHOTOS)
            if (count($item['Foto']) > 1) {
                array_shift($item['Foto']);

                foreach ($item['Foto'] as $photoItem) {
                    $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photoItem . '.jpg';
                    if ($this->testphile($picfile)) {
                        $arLoad['PROPERTY_VALUES']['PHOTOS'][] = \CFile::MakeFileArray($picfile);
                    }
                }
            }

            $ID = $el->Add($arLoad);

            if ((isset($el->LAST_ERROR))) {
                $this->echo($el->LAST_ERROR, true);
            }
        }

        return $ID;
    }

    /**
     * Установить массив с продуктами
     */
    private function setArProducts()
    {
        foreach ($this->productsFile as $key => $product) {
            $product = iconv("windows-1251", "utf-8", $product);
            $a = explode(';', $product);

            if ($a[2] == 'ID') {
                $arTemp[$a[0]]['ID'] = trim($a[count($a) - 1]);;

                continue;
            }
            if ($a[2] == 'AdresZapisi') {
                $arTemp[$a[0]]['SECTION_ID'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'Svertka') {
                $arTemp[$a[0]]['Svertka'] = $a[count($a) - 1];

                continue;
            }
            if ($a[2] == 'Naimenovanie') {
                $arTemp[$a[0]]['Naimenovanie'] = $a[count($a) - 1];

                continue;
            }
            if ($a[2] == 'Artikul') {
                $arTemp[$a[0]]['Artikul'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'CZena1') {
                $arTemp[$a[0]]['CZena1'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'CZena2') {
                $arTemp[$a[0]]['CZena2'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'CZena3') {
                $arTemp[$a[0]]['CZena3'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'EdIzmereniya') {
                $arTemp[$a[0]]['EdIzmereniya'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'VUpakovke1') {
                $arTemp[$a[0]]['VUpakovke'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'VUpakovke2') {
                $arTemp[$a[0]]['VUpakovke2'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'Ostatok') {
                $arTemp[$a[0]]['Ostatok'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'Otobrajat_v_prayse') {
                $arTemp[$a[0]]['show_in_price'] = trim($a[count($a) - 1]);

                continue;
            }
            if ($a[2] == 'Opisanie') {
                $arTemp[$a[0]]['Opisanie'] = str_replace('<br>', "\n", str_replace('<br><br>', "\n",$a[count($a) - 1]));

                continue;
            }
            if ($a[2] == 'Foto') {
                $arTemp[$a[0]]['Foto'] = explode(',', trim($a[count($a) - 1]));

                continue;
            }

            $arTemp[$a[0]]['PorNomer'] = $key;
            $arTemp[$a[0]][$a[2]] = trim($a[count($a) - 1]);

            // Добавляем свойство в список динамических свойств

            if (!in_array($a[2], $this->arDynamicPropsMap)) {
                $this->arDynamicPropsMap[$a[2]] = $a[3];
            }
        }

        foreach ($arTemp as $i => $product) {
            $this->arProducts[$product['SECTION_ID']][$i] = $product;
        }
    }

    /**
     * Добавить свойства в инфоблок
     */
    public function checkDynamicProps()
    {
        $dbResult = CIBlockProperty::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblock
            ]
        );

        // Получаем все текущие свойства инфоблока каталог
        $arAvailableProps = [];
        while ($arResult = $dbResult->Fetch()) {
            $arAvailableProps[] = $arResult['CODE'];
        }

        // Переберём все динамические свойства инфблока, и если какого-то свойства не хватает, то создадим его
        $arKeysDynamicProps = array_keys($this->arDynamicPropsMap);
        foreach ($arKeysDynamicProps as $propCode) {
            if (!in_array($propCode, $arAvailableProps)) {
                $arPropFields = [
                    'NAME' => $this->arDynamicPropsMap[$propCode],
                    'ACTIVE' => 'Y',
                    'SORT' => '1000',
                    'CODE' => $propCode,
                    'PROPERTY_TYPE' => 'S',
                    'IBLOCK_ID' => $this->iblock
                ];

                // Создаем свойство
                $obCIBlockProperty = new CIBlockProperty;
                $iPropId = $obCIBlockProperty->Add($arPropFields);

                // Добавляем его в умный фильтр
                if ($iPropId > 0) {
                    $obCIBlockProperty->Update(
                        $iPropId,
                        [
                            'SMART_FILTER' => 'Y',
                            'IBLOCK_ID' => $this->iblock
                        ]
                    );
                }
            }
        }
    }

    /**
     * Установить коэфициенты единицы измерения для всех продуктов
     */
    public function setRatio()
    {
        //1. Получаем все товары на сайте
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
            'INCLUDE_SUBSECTIONS' => 'Y'
        ];

        $dbRez = CIBlockElement::GetList(
            ['SORT'=>'ASC'],
            $arFilter,
            false,
            false,
            ['ID', 'PROPERTY_PRICE_OPT', 'PROPERTY_PRICE_OPT2', 'PROPERTY_PRICE',
                'PROPERTY_Ostatok', 'PROPERTY_UNITS', 'PROPERTY_KRATNOST']
        );

        $arItems = [];
        while($arRez = $dbRez->Fetch())
        {
            $arItems[$arRez['ID']] = $arRez;
        }

        //4. Получаем все продукты на сайте
        $dbRez = CCatalogProduct::GetList(
            [],
            []
        );

        $arProducts = [];
        while($arRez = $dbRez->Fetch()) {
            $arProducts[$arRez['ID']] = $arRez;
        }

        //Получаем все коэфициенты единицы измерения на сайте
        $rsRatios = CCatalogMeasureRatio::getList(
            [],
            [],
            false,
            false,
            ['PRODUCT_ID', 'RATIO', 'ID']
        );

        $arRatios = [];
        while($arResult = $rsRatios->Fetch()) {
            $arRatios[$arResult['PRODUCT_ID']] = $arResult;
        }

        // Добавляем коэфиценты единицы измерения
        foreach ($arProducts as $key => $product) {
            // Записываем и создаем коэфициент единицы измерения
            if (empty($arRatios[$key]) && $arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE'] != '') {
                CCatalogMeasureRatio::add([
                    'PRODUCT_ID' => $product['ID'],
                    'RATIO' => $arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE']
                ]);
            } else {
                // Если единица измерения задана в файле, то обновляем ее, если нет, то удаляем единицу измерения
                if (!$arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE']) {
                    CCatalogMeasureRatio::delete($arRatios[$product['ID']]['ID']);
                } else {
                    CCatalogMeasureRatio::update(
                        $arRatios[$product['ID']]['ID'],
                        [
                            'PRODUCT_ID' => $arRatios[$product['ID']]['PRODUCT_ID'],
                            'RATIO' => $arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE']
                        ]
                    );
                }
            }
        }
    }
}