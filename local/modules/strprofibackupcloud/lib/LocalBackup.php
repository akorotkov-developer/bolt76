<?php

namespace StrprofiBackupCloud;

use CDBResult;
use CFile;
use StrprofiBackupCloud\Interfaces\ILocalBackup;

/**
 * Класс для работы с локальными бэкапами
 */
class LocalBackup implements ILocalBackup
{
    /**
     * Получить backup 'ы c сайта
     */
    public static function getLocalBackups(): array
    {
        $entries = array_filter(scandir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/backup/.'), function($item) {
            return $item[0] !== '.';
        });

        $fileList = [];
        foreach ($entries as $entry) {
            $fileList[] = $entry;
        }

        $archives = [];
        foreach ($fileList as $file) {
            if (preg_match('/^(.+)\.tar\.gz(\.\d+)?$/', $file, $matches)) {
                $archiveName = $matches[1];
                $archiveDate = explode('_', $archiveName);
                $archiveFiles = $archives[$archiveName]['files'] ?? [];
                $archiveFiles[] = '/bitrix/backup/' . $file;
                $archives[$archiveName]['files'] = $archiveFiles;
                $archives[$archiveName]['date'] = $archiveDate;
            }
        }

        return $archives;
    }

    /**
     * Получаем информацию о бэкапах
     * @param array $data
     * @return array
     */
    public function getBackUpInfo(array $data): array
    {
        $backInfo = [
            'TOTAL_LINKS' => 0
        ];

        foreach ($data as $key => $backupItem) {
            // Получаем список файлов backup а
            $path = BX_ROOT . "/backup";
            $name = $path . '/' . $backupItem['ID'];

            $arLink = [];
            while (file_exists($_SERVER['DOCUMENT_ROOT'] . $name)) {
                $arLink[] = htmlspecialcharsbx($name);
                $name = $this->getNextName($name);
            }

            // Общее количество ссылок во всех резервных копиях
            // для определения прогресса загрузки
            $backInfo['TOTAL_LINKS'] += count($arLink);

            $backInfo[$key] = [
                'LINKS' => $arLink,
                'TOTAL_ITEMS' => count($arLink),
                'NAME' => $backupItem['NAME'],
            ];
        }

        return $backInfo;
    }

    /**
     * Удаление бэкапа с сервера
     * @param array $localBackupFiles
     * @return bool
     */
    public function delete(array $localBackupFiles): bool
    {
        \Bitrix\Main\Diag\Debug::dumpToFile(['$localBackupFiles' => $localBackupFiles], '', 'log.txt');
        foreach ($localBackupFiles as $file) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $file);
        }

        return true;
    }

    /**
     * Получить имя файла
     * @param $file
     * @return mixed|string
     */
    private function getNextName($file)
    {
        if (!$file)
            $file = $this->file;

        static $CACHE;
        $c = &$CACHE[$file];

        if (!$c) {
            $l = strrpos($file, '.');
            $num = substr($file, $l + 1);
            if (is_numeric($num))
                $file = substr($file, 0, $l + 1) . ++$num;
            else
                $file .= '.1';
            $c = $file;
        }
        return $c;
    }
}