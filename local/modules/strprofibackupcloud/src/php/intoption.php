<?php

/**
 * Получить опцию
 * @param $name
 * @param int $def
 * @return int
 */
function IntOption($name, $def = 0)
{
    return COption::GetOptionInt("main", $name, $def);
}