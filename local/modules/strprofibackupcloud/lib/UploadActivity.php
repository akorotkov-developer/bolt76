<?php

namespace StrprofiBackupCloud;

use CAgent;
use StrprofiBackupCloud\Controller\YaDisk;

/**
 * Класс для работы с загрузкой файлов на внешний диск, работающих на агентах
 */
class UploadActivity
{
    /**
     * Запуск переноса резервных копий
     */
    public function startUpload(string $diskType)
    {
        // Получаем список локальных резервных копий
        $localBackup = new LocalBackup();
        $backupList = $localBackup->getLocalBackups();

        // Добавить запись в StorageTable для старта переноса
        $rowId = $this->addNewTask($backupList, $diskType);

        if ($rowId > 0) {
            // Добавляем агент, который переносит бэкапы на внешний диск
            $this->addAgent($rowId);
        }
    }

    /**
     * Добавить новую задачу на перенос бэкапов
     * @param array $backupList
     * @param string $diskType
     * @return int
     */
    private function addNewTask(array $backupList, string $diskType): int
    {
        $storageAdd = StorageTable::add(
            [
                'DATA' => $backupList,
                'DISK_TYPE' => $diskType,
                'PERCENT' => 0,
            ]
        );

        return (int)$storageAdd->getId();
    }

    /**
     * Добавить агент
     * @param $rowId
     */
    private function addAgent($rowId)
    {
        CAgent::addAgent(
            '\StrprofiBackupCloud\UploadByAgent::uploadToExternalDrive(' . $rowId . ');',
            'strprofibackupcloud',
            'Y',
            30,
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            ),
            'Y',
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            )
        );
    }

    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param int $rowId
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function uploadToExternalDrive(int $rowId)
    {
        $rowData = StorageTable::getRowById($rowId);

        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Попали сюда'], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['$rowId' => $rowId], '', 'log.txt');

        switch ($rowData['DISK_TYPE']) {
            case 'yadisk':
                $controller = new YaDisk($rowData);
                break;
        }

        $controller->transferBackup();
    }

    /**
     * Проверить статус текущей загрузки
     * @param int $rowId
     */
    public function checkUploadStatus(int $rowId)
    {
        $rowData = StorageTable::getRowById($rowId);

        return $rowData['PERCENT'];
    }
}