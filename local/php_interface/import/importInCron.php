<?php

class importInCron
{
    /**
     * Старт импорта
     * @return string
     */
    public static function start(): string {
        $obImport = new Import();

        ob_start();

        if ($obImport->startImport()) {
            echo('Импорт завершен ' . date("d.m.Y H:i:s"));
        }

        $sOutForLog = ob_get_contents();
        ob_end_clean();

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/import/log_import.txt', $sOutForLog);

        return '\\' . __METHOD__ . '();';
    }
}