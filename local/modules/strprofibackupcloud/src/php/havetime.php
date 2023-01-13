<?php
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");

if (!function_exists('haveTime')) {

    function haveTime()
    {
        return true;
        /*if (defined('NO_TIME')) {
            return microtime(true) - START_EXEC_TIME < 1;
        }

        return microtime(true) - START_EXEC_TIME < IntOption("dump_max_exec_time");*/
    }
}