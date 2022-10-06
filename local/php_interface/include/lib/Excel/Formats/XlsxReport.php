<?php

namespace NLMK\Report\Formats;

use \Box\Spout\Writer\XLSX\Writer;
use \Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use \Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use NLMK\Report\Exception\ReportException;
use \Box\Spout\Common\Entity\Style\Style;

/**
 * Class XlsxReport
 * Абстрактный класс для стандартных xlsx-отчётов
 * @package NLMK\Report\Formats
 */
abstract class XlsxReport extends BaseFormat
{
    /**
     * Метод получения данных для отчёта
     * @return array
     */
    abstract public function getData(): array;


    /**
     * Метод формирования стилей
     * @return Style
     */
    public function getStyles(): Style
    {
        return (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();
    }

    /**
     * Метод создания объекта writer
     * @return Writer
     */
    public function getWriter(): Writer
    {
        return WriterEntityFactory::createXLSXWriter();
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
        $style = $this->getStyles();
        $writer = $this->getWriter();
        $writer->openToFile('php://output');
        $rows = [];
        foreach ($this->getData() as $row) {
            $rows[] = WriterEntityFactory::createRowFromArray($row, $style);
        }
        $writer->addRows($rows);
        $writer->close();

        $this->setHeaders();
        die();
    }

    /**
     * Метод задания http-заголовков
     */
    protected function setHeaders()
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->getFileName() . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
