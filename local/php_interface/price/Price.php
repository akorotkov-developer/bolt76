<?php

use \Bitrix\Main\Loader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\{Font, Border, Alignment};

/**
 * Класс для генерации прайс-листа
 * (требуется библиотека PhpSpreedSheet)
 */
class Price
{
    private $catalogIblockId = 1;
    private $catalogTree = [];
    private $products = [];
    private $spreadsheet;
    private $sheet;
    private $curDepthLevel = 1;

    /**
     * Номер текущей строки для записи
     * @var int
     */
    private $lineCount = 4;

    public function __construct()
    {
        Loader::includeModule('iblock');

        $this->catalogTree = $this->getCatalogTree();
        $this->products = $this->getCatalogItems();
    }

    /**
     * Получение иерархии разделов каталога
     */
    private function getCatalogTree(): array
    {
        // Получаем иерархию разделов каталога
        $arFilter = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->catalogIblockId,//ИД инфоблока товаров
            'GLOBAL_ACTIVE' => 'Y',
        ];

        $arSelect = ['IBLOCK_ID', 'ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID'];
        $arOrder = ['DEPTH_LEVEL' => 'ASC', 'SORT' => 'ASC'];
        $rsSections = CIBlockSection::GetList($arOrder, $arFilter, false, $arSelect);
        $sectionLinc = [];
        $arResult['ROOT'] = [];
        $sectionLinc[0] = &$arResult['ROOT'];
        while ($arSection = $rsSections->GetNext()) {
            $sectionLinc[(int) $arSection['IBLOCK_SECTION_ID']]['CHILD'][$arSection['ID']] = $arSection;
            $sectionLinc[$arSection['ID']] = &$sectionLinc[(int) $arSection['IBLOCK_SECTION_ID']]['CHILD'][$arSection['ID']];
        }
        unset($sectionLinc);

        $arResult['ROOT'] = $arResult['ROOT']['CHILD'];

        return current($arResult);
    }

    /**
     * Получить прайс-лист
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getPrice()
    {
        $this->createSheet();
        $this->createHeader();
        $this->createTitles();
        $this->createContent();;
        $this->savePrice();
    }

    /**
     * Создание листа
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createSheet()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->freezePane('A' . ($this->lineCount + 1));
        $this->sheet->freezePane('B' . ($this->lineCount + 1));
        $this->sheet->freezePane('C' . ($this->lineCount + 1));
        $this->sheet->freezePane('D' . ($this->lineCount + 1));
        $this->sheet->freezePane('E' . ($this->lineCount + 1));
        $this->sheet->freezePane('F' . ($this->lineCount + 1));

        $this->sheet->setAutoFilter('A' . $this->lineCount . ':F' . $this->lineCount);
    }

    /**
     * Создание шапки
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createHeader()
    {
        //С помощью класса Drawing можно осуществлять вставку картинок
        $drawing = new Drawing();
        //Указываем путь до картинки, которая должна быть расположена
        //на том же сервере
        $drawing->setPath($_SERVER['DOCUMENT_ROOT'] . '/price/images/logo.png');
        //Указываем ячейку в которой разместим изображение
        $drawing->setCoordinates('C1');
        //Можно задать отступ по X или Y оси
        $drawing->setOffsetX(110);
        $drawing->setOffsetY(0);
        //Передаем объект текущего листа
        $drawing->setWorksheet($this->sheet);

        // Установка стилей для ячеек
        $this->sheet->getStyle('B1:B2')->applyFromArray([
            'font' => [
                'name' => 'Verdana',
                'bold' => true,
                'size' => 10,
                'italic' => true,
                'underline' => Font::UNDERLINE_DOUBLE,
                'strikethrough' => false,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::HORIZONTAL_LEFT,
                'wrapText' => false,
            ]
        ]);

        // Информация о компании
        $this->sheet->setCellValue('B1', 'Телефон: +7 (4852) 58-04-45 Email: mail@strprofi.ru');
        $this->sheet->setCellValue('B2', 'Дата генерация прайс-листа: ' . date('d.m.Y'));
    }

    /**
     * Создание заголовков
     */
    private function createTitles()
    {
        // Установка стилей для ячеек
        $this->sheet->getStyle('A' . $this->lineCount . ':F' . $this->lineCount)->applyFromArray([
            'font' => [
                'name' => 'Verdana',
                'bold' => true,
                'size' => 10,
                'italic' => false,
                'underline' => Font::UNDERLINE_DOUBLE,
                'strikethrough' => false,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
            ]
        ]);

        // Заголовки
        $this->sheet->setCellValue('A' . $this->lineCount, 'Артикул');
        $this->sheet->getColumnDimension('A')->setWidth(20);
        $this->sheet->setCellValue('B' . $this->lineCount, 'Название товара');
        $this->sheet->getColumnDimension('B')->setWidth(55);
        $this->sheet->setCellValue('C' . $this->lineCount, 'Единица измерения');
        $this->sheet->getColumnDimension('C')->setWidth(25);
        $this->sheet->setCellValue('D' . $this->lineCount, 'Цена ₽');
        $this->sheet->getColumnDimension('D')->setWidth(15);
        $this->sheet->setCellValue('E' . $this->lineCount, 'Цена оптовая ₽');
        $this->sheet->getColumnDimension('E')->setWidth(15);
        $this->sheet->setCellValue('F' . $this->lineCount, 'Наличие');
        $this->sheet->getColumnDimension('F')->setWidth(15);
    }

    /**
     * Создание контента прайс-листа
     */
    private function createContent()
    {
        // Обходим рекурсивно все разделы каталога
        $this->recursionCatalogTree($this->catalogTree);
    }

    /**
     * Сохранение прайс-листа
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function savePrice()
    {
        $writer = new Xlsx($this->spreadsheet);

        $writer->save($_SERVER['DOCUMENT_ROOT'] . '/price/price.xlsx');
    }

    /**
     * Получение товаров каталога
     */
    private function getCatalogItems(): array
    {
        $dbResult = CIBlockElement::GetList(
            ['PROPERTY_Naimenovanie' => 'ASC'],
            [
                'IBLOCK_ID' => $this->catalogIblockId,
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            [
                'ID', 'NAME', 'PROPERTY_PRICE', 'PROPERTY_PRICE_OPT', 'IBLOCK_SECTION_ID', 'PROPERTY_ARTICUL',
                'PROPERTY_UNITS', 'PROPERTY_Svobodno', 'PROPERTY_TipSkladskogoZapasa',
            ]
        );

        $arItems = [];
        while($arResult = $dbResult->Fetch()) {
            $arItems[] = $arResult;
        }

        return $arItems;
    }

    /**
     * Запись заголовка раздела в прайс-лист
     * @param $value
     */
    private function writeTitle($value)
    {
        // Спускаемся на строку ниже
        $this->lineCount++;

        $this->setTitleStyle();
        $this->sheet->mergeCells('A' . $this->lineCount . ':F' . $this->lineCount);
        $this->sheet->setCellValue('A' . $this->lineCount, str_repeat('   ', (int)$value['DEPTH_LEVEL'] - 1) . $value['NAME']);

        $this->curDepthLevel = $value['DEPTH_LEVEL'];
        if ($this->curDepthLevel > 1) {
            $this->sheet->getRowDimension($this->lineCount)->setOutlineLevel($this->curDepthLevel - 1);
            $this->sheet->getRowDimension($this->lineCount)->setVisible(false);
            $this->sheet->getRowDimension($this->lineCount)->setCollapsed(true);
        }
    }

    /**
     * Запись товаров в прайс-лист
     */
    private function writeProducts($section)
    {
        // Получаем все товары раздела
        $sectionId = $section['ID'];
        $arrProducts = array_filter($this->products, function ($value) use ($sectionId) {
            return ($value['IBLOCK_SECTION_ID'] == $sectionId);
        });

        // Записываем товары в прайс-лист
        if (count($arrProducts) > 0) {
            foreach ($arrProducts as $product) {
                // Спускаемся на строку ниже
                $this->lineCount++;

                // Устанавливаем стили для текущей ячейки
                $this->setItemStyle();

                // Записываем параметры товара в текущую ячейку
                $this->sheet->setCellValue('A' . ($this->lineCount), $product['PROPERTY_ARTICUL_VALUE']);
                $this->sheet->setCellValue('B' . ($this->lineCount), trim($product['NAME']));
                $this->sheet->setCellValue('C' . ($this->lineCount), $product['PROPERTY_UNITS_VALUE']);
                $this->sheet->setCellValue('D' . ($this->lineCount), $product['PROPERTY_PRICE_VALUE']);
                $this->sheet->setCellValue('E' . ($this->lineCount), $product['PROPERTY_PRICE_OPT_VALUE']);
                if ($product['PROPERTY_SVOBODNO_VALUE'] > 0) {
                    $avail = 'В наличии';
                } else if ($product['PROPERTY_TIPSKLADSKOGOZAPASA_VALUE'] == 'Обязательный ассортимент' &&
                    (float)$product['PROPERTY_SVOBODNO_VALUE'] <= 0) {
                    $avail = 'Временно отсутствует';
                } else {
                    $avail = 'Под заказ';
                }
                $this->sheet->setCellValue('F' . ($this->lineCount), $avail);

                // Скрываем строки с товарами
                $this->sheet->getRowDimension($this->lineCount)->setOutlineLevel($this->curDepthLevel);
                $this->sheet->getRowDimension($this->lineCount)->setVisible(false);
            }

            $this->sheet->getRowDimension($this->lineCount)->setCollapsed(true);
        }
    }

    /**
     * Установка стилей для заголовков
     */
    private function setTitleStyle()
    {
        $this->sheet->getStyle('A' . $this->lineCount)->applyFromArray([
            'font' => [
                'name' => 'Verdana',
                'bold' => true,
                'size' => 10,
                'italic' => true,
                'strikethrough' => false,
                'color' => [
                    'rgb' => 'FF7920'
                ]
            ],
        ]);
    }

    /**
     * Установка стилей для заголовков
     */
    private function setItemStyle()
    {
        $this->sheet->getStyle('A' . $this->lineCount . ':F' . $this->lineCount)->applyFromArray([
            'font' => [
                'name' => 'Verdana',
                'bold' => false,
                'size' => 10,
                'italic' => false,
                'strikethrough' => false,
                'color' => [
                    'rgb' => '000000'
                ]
            ],
        ]);
    }

    /**
     * Рекурсивный обход дерева каталога
     * @param $catalogTree
     */
    private function recursionCatalogTree($catalogTree)
    {
        foreach ($catalogTree as $value) {
            if (is_array($value['CHILD'])) {
                // Записываем заголовок в прайс-лист
                $this->writeTitle($value);

                // Запись товаров в прайс лист
                $this->writeProducts($value);

                $this->recursionCatalogTree($value['CHILD']);
            } else {
                // Записываем заголовок в прайс-лист
                $this->writeTitle($value);

                // Запись товаров в прайс лист
                $this->writeProducts($value);
            }
        }
    }
}