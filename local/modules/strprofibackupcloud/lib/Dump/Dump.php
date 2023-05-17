<?php

namespace StrprofiBackupCloud\Dump;

/**
 * Класс для создания дампа
 */
class Dump
{
    public function CreateDump(): void
    {
        $this->dumpDb();
        $this->dumpFileSystem();
    }

    /**
     * Дамп базы данных
     */
    private function dumpDb(): void
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/dbconn.php');

        // Определяем кодировку БД
        $charset = (BX_UTF) ? 'utf8' : 'cp1251';
        $dir = $_SERVER['DOCUMENT_ROOT'];
        $command = 'cd ' . $dir . ' && mysqldump --host=localhost --user=bitrix0 --password=awasawas --default-character-set=' . $charset . ' sitemanager > ' . 'bitrix/backup/strprofi/mysqlDump.sql';

        passthru($command, $retval);

        if ($retval != 0) {
            // TODO занесение ошибки в журнал
        }
    }

    /**
     * Дамп файловой системы
     */
    private function dumpFileSystem(): void
    {
        // Дамп сайта
        $dir = $_SERVER['DOCUMENT_ROOT'];
        $scannedDirectory = array_diff(scandir($dir), ['..', '.']);

        // Папки для исключения из дампа
        $exclude = [
            'import/.cache',
            'bitrix/backup',
            'upload/resize_cache',
            'upload/tmp',
        ];

        $command = 'cd ' . $dir . ' && tar';
        foreach ($exclude as $rule) {
            $command .= ' --exclude=\'' . $rule . '\'';
        }

        $command .= ' -zcvf -';
        foreach ($scannedDirectory as $element) {
            $command .= ' ' . $element;
        }

        $backupName = 'backup_' . date('d.m.Y') . '.tar.gz';
        $command .= ' | split -b 100M - bitrix/backup/strprofi/' . $backupName;
        passthru($command, $retval);

        if ($retval != 0) {
            // TODO занесение ошибки в журнал
        }
    }
}