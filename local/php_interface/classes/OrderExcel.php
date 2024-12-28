<?php

namespace StrProfi;

use Bitrix\Main\Loader;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\{Font, Border, Alignment};
use Bitrix\Sale;
use Bitrix\Main\Context;

class OrderExcel
{
    private $spreadsheet;
    private $sheet;

    /**
     * Номер текущей строки для записи
     * @var int
     */
    private int $lineCount = 4;

    private array $products = [];
    private float $totalOrderPrice = 0;
    private string $sessionId;

    public function __construct(?string $sessionId)
    {
        Loader::includeModule('sale');
        Loader::includeModule('catalog');

        $this->sessionId = $sessionId ?? session_id();
    }

    /**
     * @throws Exception
     */
    public function generate(): bool
    {
        $this->createSheet();
        $this->createHeader();
        $this->createTitles();
        $this->getBasket();
        $this->createContent();
        $this->save();

        return true;
    }

    private function save()
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($_SERVER['DOCUMENT_ROOT'] . '/upload/order_excel/price_order_' . $this->sessionId . '.xlsx');
    }

    private function createContent(): void
    {
        if (count($this->products) > 0) {
            foreach ($this->products as $product) {
                // Спускаемся на строку ниже
                $this->lineCount++;

                // Записываем параметры товара в текущую ячейку
                $this->sheet->setCellValue('A' . ($this->lineCount), $product['ARTICUL']);
                $this->sheet->setCellValue('B' . ($this->lineCount), $product['NAME']);
                $this->sheet->setCellValue('C' . ($this->lineCount), $product['PRICE']);
                $this->sheet->setCellValue('D' . ($this->lineCount), $product['QUANTITY']);
                $this->sheet->setCellValue('E' . ($this->lineCount), $product['TOTAL']);
            }
        }

        $this->lineCount++;
        $this->lineCount++;
        $this->sheet->getStyle('A' . $this->lineCount . ':E' . $this->lineCount)->applyFromArray([
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
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
            ]
        ]);

        $this->sheet->setCellValue('E' . ($this->lineCount), 'Итого: ' . number_format($this->totalOrderPrice, 2, '.', ' ' ).' ₽');
    }

    private function getBasket(): void
    {
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Context::getCurrent()->getSite());

        foreach ($basket as $basketItem) {
            $quantity = round((float) $basketItem->getField('QUANTITY'), 2);
            $price = $basketItem->getField('PRICE');
            $total = $quantity * (float)$basketItem->getField('PRICE');
            $this->totalOrderPrice += $total;

            $this->products[$basketItem->getField('PRODUCT_ID')] = [
                'NAME' => $basketItem->getField('NAME'),
                'QUANTITY' => $quantity,
                'PRICE' => number_format($price, 2, '.', ' ' ).' ₽',
                'TOTAL' => number_format($total, 2, '.', ' ' ).' ₽'
            ];
        }

        $ids = array_keys($this->products);
        if (!empty($ids)) {
            $dbResult = \CIBlockElement::GetList(
                [],
                [
                    'IBLOCK_ID' => 1,
                    'ID' => $ids
                ],
                false,
                false,
                ['ID', 'PROPERTY_ARTICUL']
            );

            while ($result = $dbResult->Fetch()) {
                $this->products[$result['ID']]['ARTICUL'] = $result['PROPERTY_ARTICUL_VALUE'];
            }
        }
    }

    /**
     * Создание заголовков
     */
    private function createTitles(): void
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
        $this->sheet->setCellValue('B' . $this->lineCount, 'Наименование товара');
        $this->sheet->getColumnDimension('B')->setWidth(55);
        $this->sheet->setCellValue('C' . $this->lineCount, 'Цена ₽');
        $this->sheet->getColumnDimension('C')->setWidth(25);
        $this->sheet->setCellValue('D' . $this->lineCount, 'Количество');
        $this->sheet->getColumnDimension('D')->setWidth(15);
        $this->sheet->setCellValue('E' . $this->lineCount, 'Сумма');
        $this->sheet->getColumnDimension('E')->setWidth(30);
    }

    /**
     * Создание листа
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function createSheet(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->freezePane('A' . ($this->lineCount + 1));
        $this->sheet->freezePane('B' . ($this->lineCount + 1));
        $this->sheet->freezePane('C' . ($this->lineCount + 1));
        $this->sheet->freezePane('D' . ($this->lineCount + 1));
        $this->sheet->freezePane('E' . ($this->lineCount + 1));
        $this->sheet->freezePane('F' . ($this->lineCount + 1));

        // $this->sheet->setAutoFilter('A' . $this->lineCount . ':E' . $this->lineCount);
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
        $this->sheet->setCellValue('B1', 'Телефон: +7 (4852) 93-63-53 Email: mail@strprofi.ru');
        $this->sheet->setCellValue('B2', 'Дата : ' . date('d.m.Y'));
    }
}