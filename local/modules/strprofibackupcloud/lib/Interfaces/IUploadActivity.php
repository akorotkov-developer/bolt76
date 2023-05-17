<?php

namespace StrprofiBackupCloud\Interfaces;

use StrprofiBackupCloud\Controller\Formats\BaseCloud;

interface IUploadActivity
{
    /**
     * Запуск переноса резервных копий
     */
    public function startUpload(string $diskType, array $params): int;

    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param BaseCloud $controller
     * @param array $rowData
     */
    public function uploadToExternalDrive(BaseCloud $controller, array $rowData): void;

    /**
     * Проверка статуса загрузки
     * @param int $rowId
     * @return string
     */
    public function checkUploadStatus(int $rowId): string;
}