<?php

/**
 * Получить опцию
 * @param $name
 * @param int $def
 * @return int
 */
function IntOption($name, $def = 0)
{
    \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Получение опций здесь'], '', 'log.txt');
    return COption::GetOptionInt("main", $name, $def);
}