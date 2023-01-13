<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

if (!function_exists('workTime')) {
    function workTime()
    {
        return microtime(true) - START_EXEC_TIME;
    }
}