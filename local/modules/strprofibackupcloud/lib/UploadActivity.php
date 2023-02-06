<?php

namespace StrprofiBackupCloud;

use CAgent;
use StrprofiBackupCloud\Controller\Formats\BaseCloud;
use StrprofiBackupCloud\Interfaces\IUploadActivity;

/**
 * Класс для работы с загрузкой файлов на внешний диск, работающих на агентах
 */
class UploadActivity implements IUploadActivity
{
    /**
     * Запуск переноса резервных копий
     */
    public function startUpload(string $diskType): void
    {
        // Получаем список локальных резервных копий
        $localBackup = new LocalBackup();
        $backupList = $localBackup->getLocalBackups();

        // Добавить запись в StorageTable для старта переноса
        $rowId = $this->addNewTask($backupList, $diskType);
        \Bitrix\Main\Diag\Debug::dumpToFile(['$rowId' => $rowId], '', 'log.txt');

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
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Добавление агента'], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => '\StrprofiBackupCloud\UploadByAgent::upload(' . $rowId . ');'], '', 'log.txt');
        // TODO выполнение агента через 10 минут после старта, не забыть убрать это, что бы агент выполнялся сразу следом за созданием резервной копии
        CAgent::addAgent(
            '\StrprofiBackupCloud\UploadByAgent::upload(' . $rowId . ');',
            'strprofibackupcloud',
            'N',
            30,
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            ),
            'Y',
            date('d.m.Y H:i:s', strtotime('+10 minutes'))
        );
    }

    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param BaseCloud $controller
     * @param array $rowData
     */
    public function uploadToExternalDrive(BaseCloud $controller, array $rowData): void
    {
        $controller->transferBackup($rowData);
    }

    /**
     * Проверить статус текущей загрузки
     * @param int $rowId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkUploadStatus(int $rowId): string
    {
        $rowData = StorageTable::getRowById($rowId);

        return $rowData['PERCENT'];
    }
}