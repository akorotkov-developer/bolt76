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
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Старт резервного копирования'], '', 'log.txt');
        // Добавить запись в StorageTable для старта переноса
        $rowId = $this->addNewTask([], $diskType);

        if ($rowId > 0) {
            // Добавляем агент, который переносит бэкапы на внешний диск
            $this->addAgent($rowId);
        }
    }

    /**
     * Деактивировать все агенты переноса резервных копий
     */
    public function stopUpload(): void
    {
        $dbResult = CAgent::GetList(
            ['ID' => 'DESC'],
            ['NAME' => '\StrprofiBackupCloud\UploadByAgent::upload(%']
        );

        while($arResult = $dbResult->fetch()) {
            CAgent::Delete($arResult['ID']);
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
     * @throws \Exception
     */
    private function addAgent($rowId): void
    {
        $time1 =  strtotime(date("Y-m-d H:i:s"));
        $time2 =  strtotime(date("Y-m-d 5:30:00"));

        if ($time1 > $time2) {
            $startDate = new \DateTime(date('Y-m-d 5:30:00'));
            $startDate->format('Y-m-d H:i:s');
            $startDate->modify('+1 day');
            $time1 = $startDate->format('d.m.Y H:i:s');
        } else {
            $startDate = new \DateTime(date('Y-m-d 5:30:00'));
            $time1 = $startDate->format('d.m.Y H:i:s');
        }

        $isAdd = CAgent::addAgent(
            '\StrprofiBackupCloud\UploadByAgent::upload(' . $rowId . ');',
            'strprofibackupcloud',
            'N',
            86400,
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            ),
            'Y',
            $time1
        );

        \Bitrix\Main\Diag\Debug::dumpToFile(['$isAdd' => $isAdd], '', 'log.txt');
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