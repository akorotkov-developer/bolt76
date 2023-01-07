<?php

namespace StrprofiBackupCloud\Interfaces;

use Bitrix\Main\ORM\Data\UpdateResult;

interface IBaseCloud
{
    /**
     * Метод для переноса резервной копии на внешний накопитель
     * @param array $rowData
     * @return mixed
     */
    public function transferBackup(array $rowData): void;

    /**
     * Запись прогресса в StorageTable
     * @param int $loadedFiles
     * @param int $totalFiles
     * @param int $rowId
     * @return UpdateResult
     */
    public function setProgress(int $loadedFiles, int $totalFiles, int $rowId): UpdateResult;
}