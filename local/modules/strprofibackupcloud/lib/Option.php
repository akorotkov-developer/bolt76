<?php

namespace StrprofiBackupCloud;

use Bitrix\Main\Config\Option as ConfigOption;
use StrprofiBackupCloud\Interfaces\IOption;

/**
 * Класс для работы с опциями модуля
 */
class Option implements IOption
{
    const ADMIN_MODULE_NAME = 'strprofibackupcloud';

    /**
     * Получить значение опции
     * @param string $option
     * @return string
     */
    public function getOption(string $option): string
    {
        return ConfigOption::get(self::ADMIN_MODULE_NAME, $option);
    }

    /**
     * Установить значение опции
     * @param string $name
     * @param string $value
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function setOption(string $name, string $value)
    {
        ConfigOption::set(self::ADMIN_MODULE_NAME, $name, $value);
    }
}