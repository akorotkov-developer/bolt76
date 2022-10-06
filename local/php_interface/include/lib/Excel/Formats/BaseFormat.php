<?php

namespace NLMK\Report\Formats;

use NLMK\Report\Exception\ReportException;

/**
 * Class BaseFormat
 * Абстрактный родительский клас для всех файловых отчётов
 * @package NLMK\Report\Formats
 */
abstract class BaseFormat
{
    /** @var string тип отчета с незамедлительной выгрузгой */
    const IMMEDIATELY = 'IMMEDIATELY';

    /** @var string тип отчета с отложеным запуском */
    const DEFERRED = 'DEFERRED';

    /**
     * Массив параметров для построения отчёта
     * @var array
     */
    protected $params;

    /**
     * BaseFormat constructor.
     * Базовый конструктор файлового отчёта,
     * принимает и проверяет набор входных параметров для генерации отчёта
     * проверяет права доступа текущего пользователя к генерации файла отчёта
     *
     * @param array $params
     * @throws ReportException
     */
    function __construct(array $params = [])
    {
        $this->params = $params;
        $this->processParams();
        $this->checkAccessRights();
    }

    /**
     * Возвращаем тип запуска отчета.
     * По умолчанию: незамедлительно.
     * @return string
     */
    protected function getLaunchType(): string
    {
        return self::IMMEDIATELY;
    }

    /**
     * Метод обработки и проверки корректности входных параметров отчёта.
     * По умолчанию проверка не производится.
     * Для реализации проверки требуется переопределить метод в дочернем классе-генераторе файлового отчёта.
     * В случае обнаружения ошибки должно быть выброшено соответствующее исключение.
     */
    protected function processParams()
    {

    }

    /**
     * Метод проверки прав доступа авторизованного пользователя к генерации файла отчёта.
     * Администраторам портала доступны для генерации все отчёты.
     * В случае, если у пользователя нет прав доступа для генерации текущего отчёта, будет выброшено соответствующее исключение
     * @return bool
     * @throws ReportException
     */
    private function checkAccessRights()
    {
        $arAllowedGroups = $this->getAllowedGroups();
        if (!in_array(1, $arAllowedGroups)) {
            $arAllowedGroups[] = 1;
        }
    }

    /**
     * Метод, возвращающий массив символьных кодов и/или id групп пользователей, которым разрешен доступ к генерации файла отчёта.
     * По умолчанию любой отчёт доступен только администраторам портала.
     * Для изменения этого поведения, метод должен быть переопределён в дочернем классе отчёта.
     * @return array
     */
    protected function getAllowedGroups(): array
    {
        return [];
    }

    /**
     * Метод получения имени файла отчёта
     * @return string
     */
    abstract public function getFileName(): string;

    /**
     * Метод генерации отчёта
     */
    abstract public function generate();
}
