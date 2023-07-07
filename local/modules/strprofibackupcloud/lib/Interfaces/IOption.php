<?php

namespace StrprofiBackupCloud\Interfaces;

interface IOption
{
    /**
     * Получить значение опции
     * @param string $option
     * @return string
     */
    public function getOption(string $option): string;

    /**
     * Установить значение опции
     * @param string $name
     * @param string $value
     * @return mixed
     */
    public function setOption(string $name, string $value);
}