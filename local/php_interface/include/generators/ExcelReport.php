<?php

namespace NLMK\Report\Generators;

use Bitrix\Main\Loader;
use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use NLMK\Report\Formats\XlsxReport;

/**
 * Class HazardRequestsReport
 * Класс для выгрузки заявок КВО
 * @package NLMK\Report\Generators
 */
final class ExcelReport extends XlsxReport
{

    /** @var string $exportPath переопределим путь для экспорта */
    public $exportPath = '/upload/export/hazard_request_statistic/';

    /** @var string $nameFile Сохраним имя файла для добавления в скачивания после выполнения агента */
    private $nameFile;


    /**
     * @param array $params
     * @throws \Bitrix\Main\LoaderException
     * @throws \NLMK\Report\Exception\ReportException
     */
    public function __construct(array $params = [])
    {
        Loader::includeModule('iblock');

        parent::__construct($params);
    }

    /**
     * Метод генерации отчёта
     *
     * @throws ReportException
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function generate()
    {
        $writer = $this->getWriter();
        $writer->setShouldUseInlineStrings(false);
        $writer->openToFile($this->getFileName());
        $writer->addRows($this->getData());






        $style2 = (new StyleBuilder())
            ->setFontSize(12)
            ->setFontColor(Color::BLACK)
            ->setBackgroundColor(Color::LIGHT_GREEN)
            ->setFormat(1100)
            ->build();
        $rowFromValues = WriterEntityFactory::createRowFromArray(['11111111111111111111111', '22389472839adghahsdg72389493dkajs'], $style2);
        $writer->addRow($rowFromValues);


        $writer->close();
    }

    /**
     * Метод получения имени файла отчёта
     * @return string
     */
    public function getFileName(): string
    {
        /**
         * Сохраним имя файла, что бы передать его в экспорт на агенте.
         */
        $this->nameFile = 'hazard_request_statistic_test.xlsx';
        /**
         * Проверим наличие директории
         */
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $this->exportPath)) {
            if (!defined(BX_DIR_PERMISSIONS)) {
                define('BX_DIR_PERMISSIONS', 0775);
            }
            mkdir($_SERVER['DOCUMENT_ROOT'] . $this->exportPath, BX_DIR_PERMISSIONS, true);
        }
        $this->nameFile = $_SERVER['DOCUMENT_ROOT'] . $this->exportPath . $this->nameFile;

        return $this->nameFile;
    }

    /**
     * Метод получения данных отчёта
     * @return array
     * @throws ArgumentException
     * @throws FileNotFoundException
     * @throws FileOpenException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function getData(): array
    {
        $arExportData = [];

        $this->prepareData($arExportData);

        return $arExportData;
    }

    /**
     * Подготовка данных для генерации xlsx файла
     * @param $arExportData
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private function prepareData(&$arExportData)
    {
        $border = (new BorderBuilder())
            ->setBorderBottom(Color::BLACK, Border::WIDTH_THIN, Border::STYLE_SOLID)
            ->build();

        /**
         * Заголовки для таблицы
         */
        $headerStyle = (new StyleBuilder())
            ->setFontSize(14)
            ->setFontColor(Color::BLACK)
            ->setBackgroundColor("CFD4D8")
            ->setBorder($border)
            ->build();

        $arExportData[] = WriterEntityFactory::createRowFromArray(
            [
                'Артикул',
                'Наименование',
                'Оптовая цена',
                'В упаковке',
                'Единица измерения',
            ],
            $headerStyle
        );

        $defaultStyle = $this->getStyles();





    }
}
