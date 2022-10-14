<?php

class importInCron
{
    /**
     * Старт импорта
     * @return string
     */
    public static function start(): string
    {
        try {
            $obImport = new Import();

            $obImport->startImport();
        } catch (Exception $e) {

        }

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

        $resultCheckImport = $objAgentCheck->Fetch();
        $resultStartImport = $objAgentStart->Fetch();

        $importFlag = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import_check.txt');

        $logImport = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import.txt');
        $isEnd = strpos($logImport, 'Конец импорта');

        // Если Агент импорта оказался не активен, значит импорт не прошел
        // устанавливаем время импорта на 5 минут позже и переводим время
        // агента проверки импорта на 25 минут позднее
        // Если агент импорта был активен, то переводим время агента проверки
        // на следующие сутки на 4:20 утра
        if ($resultStartImport['ACTIVE'] == 'N' || !$isEnd) {
            // Записываем в файл лога Флаг, о том, что импорт пока не завершился
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/import/logs/log_import_check.txt', 'N');

            $resultStartImport['ACTIVE'] = 'Y';

            $after25minutes = date("d.m.Y H:i:s", strtotime(date('d.m.Y H:i:s').'+15 minute'));
            $resultCheckImport['NEXT_EXEC'] = $after25minutes;

            $after5minutes = date("d.m.Y H:i:s", strtotime(date('d.m.Y H:i:s').'+5 minute'));
            $resultStartImport['NEXT_EXEC'] = $after5minutes;
        } else if ($importFlag != 'N') {

            $tomorrow = date("d.m.Y", strtotime(date('d.m.Y').'+ 1 days'));
            $resultStartImport['NEXT_EXEC'] = $tomorrow . ' 4:00:00';
            $resultCheckImport['NEXT_EXEC'] = $tomorrow . ' 4:20:00';

        }
        \CAgent::Update($resultCheckImport['ID'], $resultCheckImport);
        \CAgent::Update($resultStartImport['ID'], $resultStartImport);

        return '\\' . __METHOD__ . '();';
    }
}