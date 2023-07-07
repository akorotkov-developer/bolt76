<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

if (!function_exists('IntOption')) {
    function IntOption($name, $def = 0)
    {
        // TODO уберем кеширование этого метода для правильной работы на бэке
        /*global $arParams;
        if (isset($arParams[$name]))
            return $arParams[$name];

        static $CACHE;
        $name .= '_auto';

        if (!$CACHE[$name])
            $CACHE[$name] = COption::GetOptionInt("main", $name, $def);
        return $CACHE[$name];*/

        return COption::GetOptionInt("main", $name, $def);
    }
}