<?php

class importInCron
{
    /**
     * Старт импорта
     * @return string
     */
    public static function start(): string
    {
        $obImport = new Import();

        $obImport->startImport();

        return '\\' . __METHOD__ . '();';
    }

    /**
     * Проверить состояние импорта
     * @return string
     */
    public static function checkImport(): string
    {
        $objAgentStart = \CAgent::GetList([], ['NAME' => '\importInCron::start();']);
        $objAgentCheck = \CAgent::GetList([], ['NAME' => '\importInCron::checkImport();']);

        $resultCheckImport = $objAgentStart->Fetch();
        $resultStartImport = $objAgentCheck->Fetch();

        // Если Агент импорта оказался не активен, значит импорт не прошел
        // устанавливаем время импорта на 5 минут позже и переводим время
        // агента проверки импорта на 25 минут позднее
        // Если агент импорта был активен, то переводим время агента проверки
        // на следующие сутки на 4:20 утра
        if ($resultStartImport['ACTIVE'] == 'N') {
            $resultStartImport['ACTIVE'] = 'Y';

            $after25minutes = date("d.m.Y h:i:s", strtotime(date('d.m.Y h:i:s').'+ 25 minutes'));
            $resultCheckImport['NEXT_EXEC'] = $after25minutes;

            $after5minutes = date("d.m.Y h:i:s", strtotime(date('d.m.Y h:i:s').'+ 5 minutes'));
            $resultStartImport['NEXT_EXEC'] = $after5minutes;

            \CAgent::Update($resultCheckImport['ID'], $resultCheckImport);
            \CAgent::Update($resultStartImport['ID'], $resultStartImport);
        } else {
            $tomorrow = date("d.m.Y", strtotime(date('d.m.Y').'+ 1 days'));
            $resultStartImport['NEXT_EXEC'] = $tomorrow . ' 4:00:00';
            $resultCheckImport['NEXT_EXEC'] = $tomorrow . ' 4:20:00';

            \CAgent::Update($resultCheckImport['ID'], $resultCheckImport);
            \CAgent::Update($resultStartImport['ID'], $resultStartImport);
        }

        \Bitrix\Main\Diag\Debug::dumpToFile(['$resultCheckImport' => $resultCheckImport], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['$resultStartImport' => $resultStartImport], '', 'log.txt');

        return '\\' . __METHOD__ . '();';
    }
}