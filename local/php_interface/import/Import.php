<?php

use \Bitrix\Main\Loader;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Type\DateTime;

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
    public $arCatalogSections;
    public $arProductElements;
    public $sFileLogImportPath;
    public $arPicturesMapping;

    /**
     * Установка начальных параметров
     */
    public function __construct($rootPath = '')
    {
        Loader::includeModule('iblock');
        Loader::includeModule('sale');

        $this->startTime = date("d.m.Y H:i:s");
        $this->iblock = 1;
        $this->sFileLogImportPath = $_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import.txt';

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

        if ($rootPath != '') {
            $catNewXml = $rootPath . '/import/cat_new.xml';
            $catNew = $rootPath . '/import/catalog_new.txt';
        } else {
            $catNewXml = $_SERVER['DOCUMENT_ROOT'] . '/import/cat_new.xml';
            $catNew = $_SERVER['DOCUMENT_ROOT'] . '/import/catalog_new.txt';
        }

        // Необходимо сначала подготовить файл с секциями для загрузки
        // заменить все <desc> на <desc><![CDATA[ , чтобы описание в HTML
        // у секций не воспринималось как объект
        $xmlFile = file_get_contents($catNewXml);

        $preparedXmlFile = str_replace("<desc>", "<desc><![CDATA[", $xmlFile);
        $preparedXmlFile = str_replace("</desc>", "]]></desc>", $preparedXmlFile);

        if ($rootPath != '') {
            file_put_contents($rootPath . '/import/preparedCatNew.xml', $preparedXmlFile);
            $catNewXml = $rootPath . '/import/preparedCatNew.xml';
        } else {
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/import/preparedCatNew.xml', $preparedXmlFile);
            $catNewXml = $_SERVER['DOCUMENT_ROOT'] . '/import/preparedCatNew.xml';
        }

        $this->xmlCat = simplexml_load_file($catNewXml);
        $this->productsFile = file($catNew);
    }

    /**
     * Функция печати сообщения
     * @param string $sString
     * @param bool $error
     */
    public function echo(string $sString, bool $error = false)
    {
        if (!$error) {
            file_put_contents($this->sFileLogImportPath, $sString . ' ' . date("d.m.Y H:i:s") . PHP_EOL, FILE_APPEND);
        } else {
            if ($sString != '') {
                file_put_contents($this->sFileLogImportPath, 'ОШИБКА: ' . $sString . ' ' . date("d.m.Y H:i:s") . PHP_EOL, FILE_APPEND);
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

        // Обнуляем файла лога перед началом импорта и пишем старт импорта
        file_put_contents($this->sFileLogImportPath, '');
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import_check.txt', 'N', 'Старт импорта');

        if ($this->productsFile == false) {
            $this->echo('Ошибка открытия файла с товарами', true);

            // Отправка лога импорта
            $this->sendLog();
        } else {
            $this->echo('Начало импорта!');

            // Устанавливаем массив с продуктами
            $this->setArProducts();
            sleep(10);
            // Установить динамические свойства
            $this->checkDynamicProps();
            sleep(10);
            // Загрузить разделы и товары
            $this->setSections();
            sleep(10);
            // Установить символьные коды для товаров
            $this->setCodesForProducts();
            sleep(10);
            // Сделать из элементов инфоблока товары с ценами и параметрами
            $this->makeProducts();
            sleep(10);
            // Установить коэфициенты единицы измерения для всех продуктов
            $this->setRatio();

            $this->echo('Конец импорта!');

            // Отправка лога импорта
            $this->sendLog();
        }

        return $bSuccess;
    }

    /**
     * Отправка лога в конце импорта
     */
    private function sendLog(): void
    {
        $objDateTime = new DateTime();
        Event::send([
            'EVENT_NAME' => 'SEND_LOGS_IMPORT',
            'LID' => 's1',
            'C_FIELDS' => [
                'LOG_DATE' => $objDateTime->format('d.m.Y')
            ],
            'FILE' => [
                $_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import.txt'
            ]
        ]);
    }

    /**
     * Сделать из элементов инфоблока товары с ценами и параметрами
     */
    public function makeProducts()
    {
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
            [],
            false,
            false,
            ['ID', 'MEASURE', 'QUANTITY']
        );

        $arProducts = [];
        while($arRez = $dbRez->Fetch()) {
            $arProducts[$arRez['ID']] = $arRez;
        }

        //3. Переберем все товары и запишем туда цены и доступное количество
        $PRICE_BASE_ID = 1; //Базовая цена
        $PRICE_OPT_ID = 2; //Оптовая цена
        $PRICE_OPT2_ID = 3; //Оптовая цена 2

        foreach ($this->arProductElements as $arItem) {
            //Сначала проверим существует ли такой продукт в системе и если не существует, то создадим его
            if (!$arProducts[$arItem['ID']]) {
                \Bitrix\Catalog\Model\Product::add(
                    [
                        'ID' => $arItem['ID'],
                        'VAT_ID' => 1, //выставляем тип ндс (задается в админке)
                        'VAT_INCLUDED' => 'Y' //НДС входит в стоимость
                    ]
                );

                $this->echo('Создание товара ' . $arItem['NAME']);
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
                    if (round($arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]['PRICE'], 2) != round($arItem['PROPERTY_PRICE_VALUE'], 2)) {
                        $this->echo('Обновление розничной цены товара ' . $arItem['NAME']);
                        CPrice::Update(
                            $arPricesItems[$arItem['ID'] . '_' . $PRICE_BASE_ID]['ID'],
                            $arFields
                        );
                    }
                } else {
                    $this->echo('Создание розничной цены товара ' . $arItem['NAME']);
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
                    if (round($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT_ID]['PRICE'], 2) != round($arItem['PROPERTY_PRICE_OPT_VALUE'], 2)) {
                        $this->echo('Обновление оптовой цены товара ' . $arItem['NAME']);
                        CPrice::Update(
                            $arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT_ID]['ID'],
                            $arFields
                        );
                    }
                } else {
                    $this->echo('Создание оптовой цены товара ' . $arItem['NAME']);
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
                    if (round($arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT2_ID]['PRICE'], 2) != round($arItem['PROPERTY_PRICE_OPT2_VALUE'], 2)) {
                        $this->echo('Обновление оптовой цены2 товара ' . $arItem['NAME']);
                        CPrice::Update(
                            $arPricesItems[$arItem['ID'] . '_' . $PRICE_OPT2_ID]['ID'],
                            $arFields
                        );
                    }
                } else {
                    $this->echo('Создание оптовой цены2 товара ' . $arItem['NAME']);
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

            if ($arProducts[$arItem['ID']]['MEASURE'] != $iMeasure || $arProducts[$arItem['ID']]['QUANTITY'] != $arItem['PROPERTY_Ostatok_VALUE']) {
                $this->echo('Обновление остатков и единиц измерения товара ' . $arItem['NAME']);
                CCatalogProduct::Update(
                    $arItem['ID'],
                    [
                        'QUANTITY' => $arItem['PROPERTY_Ostatok_VALUE'],
                        'MEASURE' => $iMeasure
                    ]
                );
            }
        }
    }

    /**
     * Установка символьных кодов для всех товров на сайте
     */
    public function setCodesForProducts()
    {
        $elem = new CIBlockElement();
        foreach ($this->arProductElements as $item) {
            $sCode = $this->translit($item['NAME']);

            if ($sCode != $item['CODE']) {
                $this->echo('Обновление символьного кода для товара ' . $item['NAME']);
                $elem->Update($item['ID'], ['CODE' => $sCode]);
            }
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
            // Получаем список всех категорий на сайте
            $this->arCatalogSections = $this->getCatalogSections();

            // Обновление разделов
            $this->sectionWalker($this->xmlCat, 0);

            // Обновляем доступные товары в переменных и хэшиmd5
            $this->arCatalogSections = $this->getCatalogSections();
            $this->arProductElements = $this->getProductElements();
            $this->arPicturesMapping = $this->getPictureMapping(true);

            // Обновление товаров
            foreach ($this->arCatalogSections as $arSection) {
                $this->parseSection(
                    $this->productSections[$arSection['ID']],
                    $arSection['ID']
                );
            }

            // Получение всех товаров заново, потому что могли добавиться новые
            $this->arProductElements = $this->getProductElements();
        }
    }

    /**
     * Формирование md5Хэшей для картинок
     * @param false $isProducts
     * @return array
     */
    private function getPictureMapping($isProducts = false): array
    {
        $arSectionPictureIDs = [];
        $arElementsPictureIDs = [];
        $arPropPhotosValue = [];

        // Получаем хэши для картинок разделов
        $arSectionPictureIDs = array_diff(array_unique(array_column($this->arCatalogSections, 'PICTURE')), ['']);

        if  ($isProducts) {
            $arElementsPictureIDs = array_diff(array_unique(array_column($this->arProductElements, 'PREVIEW_PICTURE')), ['']);
            // $arPropPhotosValue = array_column($this->arProductElements, 'PROPERTY_PHOTOS_VALUE');
        }

        // Общий массив картинок
        $arPictureIDs = array_unique(array_merge($arSectionPictureIDs, $arElementsPictureIDs));

        // Получаем все картинки по известным ID одним запросом
        // и составляем мапинг этих картинок
        $dbResult = CFile::GetList(
            $arOrder = [],
            $arFilter = [
                "@ID" => implode(',', $arPictureIDs)
            ],
            $arParams = []
        );

        $arPicturesMapping = [];
        while($arResult = $dbResult->Fetch()) {
            $md5Hash = md5_file($_SERVER['DOCUMENT_ROOT'] . '/upload/' . $arResult['SUBDIR'] . '/' . $arResult['FILE_NAME']);
            $arPicturesMapping[$arResult['ID']] = $md5Hash;
        }

        return $arPicturesMapping;
    }

    /**
     * Получить все товары на сайте
     * @return array
     */
    private function getProductElements(): array
    {
        $arSelect = [
            'ID',
            'NAME',
            'CODE',
            'SORT',
            'IBLOCK_ID',
            'PROPERTY_PHOTO_ID',
            'PREVIEW_PICTURE',
            'PROPERTY_ROWID',
            'PROPERTY_PHOTOS',
            'PROPERTY_PRICE_OPT',
            'PROPERTY_PRICE_OPT2',
            'PROPERTY_PRICE',
            'PROPERTY_Ostatok',
            'PROPERTY_UNITS',
            'PROPERTY_KRATNOST',
            'PROPERTY_ARTICUL',
            'PROPERTY_VES',
            'PROPERTY_UPAKOVKA',
            'PROPERTY_UPAKOVKA2',
            'PROPERTY_AVAILABLE',
            'PROPERTY_SHOW_IN_PRICE',
            'PROPERTY_SORT_IN_PRICE',
        ];

        $arDynamicPropsKeys = array_keys($this->arDynamicPropsMap);

        foreach ($arDynamicPropsKeys as $keyProp) {
            if ($keyProp != '') {
                $arSelect[] = 'PROPERTY_' . $keyProp;
            }
        }

        // Массив свойств для проверки
        $arCheckedProps = [
            'ID',
            'NAME',
            'CODE',
            'SORT',
            'IBLOCK_ID',
            'PREVIEW_PICTURE',
            'PROPERTY_PHOTO_ID_VALUE',
            'PROPERTY_ROWID_VALUE',
            'PROPERTY_PHOTOS_VALUE',
            'PROPERTY_PRICE_OPT_VALUE',
            'PROPERTY_PRICE_OPT2_VALUE',
            'PROPERTY_PRICE_VALUE',
            'PROPERTY_Ostatok_VALUE',
            'PROPERTY_UNITS_VALUE',
            'PROPERTY_KRATNOST_VALUE',
            'PROPERTY_ARTICUL_VALUE',
            'PROPERTY_VES_VALUE',
            'PROPERTY_UPAKOVKA_VALUE',
            'PROPERTY_UPAKOVKA2_VALUE',
            'PROPERTY_AVAILABLE_VALUE',
            'PROPERTY_SHOW_IN_PRICE_VALUE',
            'PROPERTY_SORT_IN_PRICE_VALUE',
        ];
        foreach ($arDynamicPropsKeys as $keyProp) {
            if ($keyProp != '') {
                $arCheckedProps[] = 'PROPERTY_' . $keyProp . '_VALUE';
            }
        }

        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
        ];

        $obDbRequest = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        $arElements = [];
        while ($arResult = $obDbRequest->GetNext()) {
            foreach ($arCheckedProps as $prop) {
                if ($arResult[strtoupper($prop)] != '') {
                    $arElements[$arResult['PROPERTY_ROWID_VALUE']][$prop] = $arResult[strtoupper($prop)];
                }
            }
        }

        return $arElements;
    }

    /**
     * Функция для получения массива секций каталога
     * @return array
     */
    private function getCatalogSections(): array
    {
        $arFilter = [
            'IBLOCK_ID' => $this->iblock,
        ];

        $dbList = \CIBlockSection::GetList([], $arFilter, false,["UF_*"]);

        $arSections = [];
        while($arResult = $dbList->Fetch()) {
            $arSections[$arResult['UF_ROWID']] = $arResult;
        }

        return $arSections;
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
                $desc = (string) $topContent->desc;
                $desc = str_replace('[BR]', "\n", str_replace('[BR][BR]', "\n", $desc));
                $viewTemplate = $topContent->viewTemplate;
                $bookmarks = $topContent->IdBookmark;
                $ID = $this->addSection(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), $desc, $viewTemplate, $bookmarks);
                $present[] = $ID;
                $this->sectionWalker($topContent->childs, $ID);
            } else {
                $desc = (string) $topContent->desc;
                $desc = str_replace('[BR]', "\n", str_replace('[BR][BR]', "\n", $desc));
                $viewTemplate = $topContent->viewTemplate;
                $bookmarks = $topContent->IdBookmark;
                $ID = $this->addSection(strval($topContent->name), intval($topContent->ID), $top, $image, intval($topContent->PorNomer), intval($topContent->price_id), $desc, $viewTemplate, $bookmarks, true);
                $present[] = $ID;
                $this->removeSection($ID, array('После добавления'));
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
     * @param $viewTemplate
     * @param false $isItem
     * @return false|int|mixed
     */
    private function addSection($name, $internal, $parent, $photo, $sort, $price_id, $descr, $viewTemplate, $bookmarks, $isItem = false)
    {
        $newName = preg_replace("/^([0-9]{1,2}\. ?[0-9]?\.? ?[0-9]* ?)/", "", trim($name));
        $newName = preg_replace('/^\d+\s+/', '', $newName);
        $code = $this->rus2lat($newName);

        $obSection = new CIBlockSection;

        $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photo . '.jpg';

        if (!empty($this->arCatalogSections[$internal])) {
            // Проверяем есть ли у нас изменения в разделе, если есть
            // то вносим изменения, если нет, то проходим дальше
            $isUpdate = false; // Флаг для того, чтобы понять надо обновлять раздел или нет

            // Проверяем свойства раздела
            /*if (trim($this->arCatalogSections[$internal]['NAME']) != trim($newName)
                || trim($this->arCatalogSections[$internal]['UF_ORIGINAL_NAME']) != trim($name)
                || trim($this->arCatalogSections[$internal]['UF_PRICE_ID']) != trim($price_id)
                || trim($this->arCatalogSections[$internal]['DESCRIPTION']) != trim($descr)
                || trim($this->arCatalogSections[$internal]['SORT']) != trim($sort)
                || trim($this->arCatalogSections[$internal]['CODE']) != trim($code)
                || trim($this->arCatalogSections[$internal]['UF_TEMPLATE']) != trim($viewTemplate)) {

                $isUpdate = true;
            }

            // Проверяем картинку раздела
            $curMd5 = $this->arPicturesMapping[$this->arCatalogSections[$internal]['PICTURE']];
            $md5LocalFile = md5_file($picfile);

            if ($curMd5 !== $md5LocalFile) {
                $isUpdate = true;
            }*/

            // TODO подумать как можно переделать этот метода
            if ($isUpdate || true) {
                $this->echo('Обновление раздела ' . strval($name));

                $ID = $this->arCatalogSections[$internal]["ID"];
                $arUpdate = [
                    "NAME" => $newName,
                    "UF_ORIGINAL_NAME" => $name,
                    "UF_PRICE_ID" => $price_id,
                    "DESCRIPTION" => $descr,
                    "SORT" => $sort,
                    "CODE" => $code,
                    "UF_TABS" => explode(':', $bookmarks),
                    'IPROPERTY_TEMPLATES' => [
                        'SECTION_META_KEYWORDS' => '{=this.Name} в Ярославле, {=this.Name}, опт, недорого, прайс-лист, каталог, доставка до транспортной компании.',
                        'SECTION_META_DESCRIPTION' => 'Купить {=this.Name} в Ярославле оптом и в розницу, доставка до транспортной компании. Низкие цены, акции и скидки на {=this.Name} в интернет-магазине СтройПрофи.',
                    ]
                ];

                $arUpdate["PICTURE"] = false;
                $arUpdate["UF_TEMPLATE"] = $viewTemplate;

                if ($photo != 0) {
                    if ($this->testphile($picfile)) {
                        $pic = CFile::MakeFileArray($picfile);
                        if ($pic["size"] > 0) {
                            $arUpdate["PICTURE"] = $pic;
                        }
                    }
                }

                // TODO не забыть восстановить функционал
                $obSection->Update($ID, $arUpdate);
            }
        } else {
            $this->echo('Добавление раздела ' . strval($name));

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
                "UF_TEMPLATE" => $viewTemplate,
                "UF_TABS" => explode(':', $bookmarks),
                'IPROPERTY_TEMPLATES' => [
                    'SECTION_META_KEYWORDS' => '{=this.Name} в Ярославле, {=this.Name}, опт, недорого, прайс-лист, каталог, доставка до транспортной компании.',
                    'SECTION_META_DESCRIPTION' => 'Купить {=this.Name} в Ярославле оптом и в розницу, доставка до транспортной компании. Низкие цены, акции и скидки на {=this.Name} в интернет-магазине СтройПрофи.',
                ]
            ];
            if ($photo != 0) {
                if ($this->testphile($picfile)) {
                    $pic = CFile::MakeFileArray($picfile);
                    if ($pic["size"] > 0) {
                        $arAdd["PICTURE"] = $pic;
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
     */
    private function parseSection($strCatID, $siteCatID)
    {
        $total = 0;

        // Список ID товаров, который должны находиться в текущем разделе
        // нужен для того, что бы удалить лишние товары из раздела
        $present = [];

        if (isset($this->arProducts[$strCatID])) {
            foreach ($this->arProducts[$strCatID] as $item) {
                $ID = $this->addUpdateElement($item, $siteCatID);
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
            $this->echo('Удаление товара ' . $ob['NAME']);
            $el->Delete($ob['ID']);
        }
    }

    /**
     * Функция для добавления $item в раздел $siteCatId (товаров в раздел)
     * @param $item
     * @param $siteCatID
     * @return false|mixed
     * @throws \Bitrix\Main\LoaderException
     */
    private function addUpdateElement($item, $siteCatID)
    {
        \Bitrix\Main\Loader::includeModule('iblock');

        $sName = ($item['Svertka'] != '') ? $item['Svertka'] : $item['Naimenovanie'];

        $el = new \CIBlockElement;

        if (!empty($this->arProductElements[$item['ID']])) {
            $arUpdate = [
                'NAME' => $sName,
                'PREVIEW_TEXT' => trim(strval($item['Opisanie'])),
                'PREVIEW_TEXT_TYPE' => 'html',
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
                    'AVAILABLE' => ((int)$item['Ostatok'] > 0) ? $this->iAvailablePropId : '',
                    'SALE' => $item['Rasprodaja']
                ],
                'IPROPERTY_TEMPLATES' => [
                    'ELEMENT_META_KEYWORDS'    =>  '{=this.Name} в Ярославле, {=this.Name}, опт, недорого, прайс-лист, каталог, доставка до транспортной компании.',
                    'ELEMENT_META_DESCRIPTION' =>  'Купить {=this.Name} в Ярославле оптом и в розницу, доставка до транспортной компании. Низкие цены, акции и скидки на {=this.Name} в интернет-магазине СтройПрофи.',
                ]
            ];

            // Добавим значения динамических свойств в товар
            $arDynamicPropsKeys = array_keys($this->arDynamicPropsMap);

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

            $isUpdate = false; // Флаг обновления товара
            $flag_update_pic = false;
            if ($this->arProductElements[$item['ID']]["PROPERTY_PHOTO_ID_VALUE"] != $item['Foto'][0]) {
                $flag_update_pic = true;
            }
            if ($this->arProductElements[$item['ID']]["PREVIEW_PICTURE"] == NULL) {
                $flag_update_pic = true;
            }
            if (count($item['Foto']) > 0) {
                $flag_update_pic = true;
            }

            $propsToUpdate = [];

            if (($flag_update_pic) || ($this->testphile($picfile))) {
                $photo = $item['Foto'][0];

                if ($photo != 0) {

                    // Проверяем совпадает ли картинки, которые пытаемся загрузить
                    // и картинка, которая уже находится на сайте
                    if ($this->arPicturesMapping[$this->arProductElements[$item['ID']]['PREVIEW_PICTURE']] != md5_file($picfile)) {
                        $isUpdate = true;
                    }

                    if ($isUpdate) {
                        $arUpdate['PROPERTY_VALUES']["PHOTO_ID"] = $item['Foto'][0];
                        $pic = CFile::MakeFileArray($picfile);
                        if ($pic["size"] > 0) {
                            $arUpdate['PREVIEW_PICTURE'] = $pic;
                        }
                    }
                }

                // Если у нас несколько фото, то добавим их в свойство фотографии (PROPERTY_PHOTOS)
                if (count($item['Foto']) > 1) {
                    array_shift($item['Foto']);

                    \Bitrix\Main\Diag\Debug::dumpToFile(['photo' => $item['Foto']], '', 'log.txt');
                    \Bitrix\Main\Diag\Debug::dumpToFile(['$item' => $item], '', 'log.txt');
                    foreach ($item['Foto'] as $photoItem) {
                        $picfile = $_SERVER['DOCUMENT_ROOT'] . '/import/img/' . $photoItem . '.jpg';
                        if ($this->testphile($picfile)) {
                            $propsToUpdate['PHOTOS'][] = \CFile::MakeFileArray($picfile);
                        }
                    }

                    // TODO здесь надо сделать проверку на совпадение фото, в следующем релизе
                    // сейчас пока, если у нас товар с несколькими картинками, то в любом случае обновляем его
                    $isUpdate = true;
                }
            } else {
                if ($this->arPicturesMapping[$this->arProductElements[$item['ID']]['PREVIEW_PICTURE']] != md5_file($picfile)) {
                    $isUpdate = true;
                }

                if ($photo == '') {
                    $arUpdate['PREVIEW_PICTURE'] = ['del' => 'Y'];
                    $isUpdate = true;
                }
            }

            /**
             * Проверка есть ли разница в обновляемых свойствах
             */
            // Текущий элемент, который сейчас находится на сайте
            $curElement = $this->arProductElements[$item['ID']];

            // Проверка стандартных полей
            if ($curElement['NAME'] != $sName
                || $curElement['SORT'] != intval($item['PorNomer'])
            ) {
                $isUpdate = true;
            }

            // Проверка динамических полей
            foreach ($item as $propCode => $propValue) {
                switch ($propCode) {
                    case 'SECTION_ID':
                        break;
                    case 'ID':
                        if ($curElement['PROPERTY_ROWID_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'Artikul':
                        if ($curElement['PROPERTY_ARTICUL_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'CZena1':
                        if ($curElement['PROPERTY_PRICE_VALUE'] != (float)str_replace(",", ".", $propValue)) {
                            $isUpdate = true;
                        }
                        break;
                    case 'CZena2':
                        if ($curElement['PROPERTY_PRICE_OPT_VALUE'] != (float)str_replace(",", ".", $propValue)) {
                            $isUpdate = true;
                        }
                        break;
                    case 'CZena3':
                        if ($curElement['PROPERTY_PRICE_OPT2_VALUE'] != (float)str_replace(",", ".", $propValue)) {
                            $isUpdate = true;
                        }
                        break;
                    case 'Foto':
                        if ($curElement['PROPERTY_PHOTO_ID_VALUE'] != $propValue[0]) {
                            $isUpdate = true;
                        }
                        break;
                    case 'Ves':
                        if ($curElement['PROPERTY_VES_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'EdIzmereniya':
                        if ($curElement['PROPERTY_UNITS_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'VUpakovke':
                        if ($curElement['PROPERTY_UPAKOVKA_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'VUpakovke2':
                        if ($curElement['PROPERTY_UPAKOVKA2_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    case 'Ostatok':
                        if ($curElement['PROPERTY_OSTATOK_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        if ($curElement['PROPERTY_AVAILABLE_VALUE'] != ((int)$propValue > 0) ? $this->iAvailablePropId : '') {
                            $isUpdate = true;
                        }
                        break;
                    case 'show_in_price':
                        if ($curElement['PROPERTY_SHOW_IN_PRICE_VALUE'] != ($propValue > 0) ? 1 : 0) {
                            $isUpdate = true;
                        }
                        if ($curElement['PROPERTY_SORT_IN_PRICE_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                    default:
                        // Удалить лишние свойства из динамических
                        if (!in_array($propCode, ['PorNomer', 'Opisanie', 'Naimenovanie', 'Svertka']) &&
                            $curElement['PROPERTY_' . strtoupper($propCode) . '_VALUE'] != $propValue) {
                            $isUpdate = true;
                        }
                        break;
                }
            }

            if ($isUpdate) {
                $isUpdated = $el->Update($this->arProductElements[$item['ID']]['ID'], $arUpdate);
                if (!$isUpdated) {
                    $this->echo($el->LAST_ERROR);
                } else {
                    $this->echo('Обновление товара  ' . $this->arProductElements[$item['ID']]['NAME']);
                }

                // Обнуляем фото
                if (count($this->arProductElements[$item['ID']]['PROPERTY_PHOTOS_VALUE']) > 0 || !empty($propsToUpdate['PHOTOS'])) {
                    \CIBlockElement::SetPropertyValuesEx($this->arProductElements[$item['ID']]['ID'], $this->iblock, ['PHOTOS' => ['VALUE' => '']]);
                }

                if (!empty($propsToUpdate['PHOTOS']) && count($propsToUpdate['PHOTOS']) > 0) {
                    // Записываем новые свойства
                    \CIBlockElement::SetPropertyValuesEx($this->arProductElements[$item['ID']]['ID'], $this->iblock, $propsToUpdate);
                }
            }

            $ID = $this->arProductElements[$item['ID']]['ID'];
        } else {
            $arLoad = [
                'IBLOCK_ID' => $this->iblock,
                'IBLOCK_SECTION_ID' => $siteCatID,
                'NAME' => $sName,
                'PREVIEW_TEXT' => trim(strval($item['Opisanie'])),
                'PREVIEW_TEXT_TYPE' => 'html',
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
                'IPROPERTY_TEMPLATES' => [
                    'ELEMENT_META_KEYWORDS'    =>  '{=this.Name} в Ярославле, {=this.Name}, опт, недорого, прайс-лист, каталог, доставка до транспортной компании.',
                    'ELEMENT_META_DESCRIPTION' =>  'Купить {=this.Name} в Ярославле оптом и в розницу, доставка до транспортной компании. Низкие цены, акции и скидки на {=this.Name} в интернет-магазине СтройПрофи.',
                ]
            ];

            // Добавим значения динамических свойств в товар
            $arDynamicPropsKeys = array_keys($this->arDynamicPropsMap);
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
            ['ID', 'NAME', 'PROPERTY_PRICE_OPT', 'PROPERTY_PRICE_OPT2', 'PROPERTY_PRICE',
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
                $this->echo('Добавление единицы измерения для товара  ' . $arItems[$product['ID']]['NAME']);
                CCatalogMeasureRatio::add([
                    'PRODUCT_ID' => $product['ID'],
                    'RATIO' => $arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE']
                ]);
            } else {
                // Если единица измерения задана в файле, то обновляем ее, если нет, то удаляем единицу измерения
                if (!$arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE'] ) {
                    if ($arRatios[$product['ID']]['RATIO']) {
                        $this->echo('Удаление единицы измерения у товара  ' . $arItems[$product['ID']]['NAME']);
                        CCatalogMeasureRatio::delete($arRatios[$product['ID']]['ID']);
                    }
                } else {
                    if ($arRatios[$product['ID']]['RATIO'] != $arItems[$product['ID']]['PROPERTY_KRATNOST_VALUE']) {
                        $this->echo('Обновление единицы измерения у товара  ' . $arItems[$product['ID']]['NAME']);
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
}