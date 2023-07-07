<?php

namespace StrprofiBackupCloud\Controller\Formats;

use Bitrix\Main\ORM\Data\UpdateResult;
use StrprofiBackupCloud\StorageTable;
use StrprofiBackupCloud\Interfaces\IBaseCloud;

/**
 * Абстрактный класс для объектов работы с внешними хранилищами
 */
abstract class BaseCloud implements IBaseCloud
{
    /**
     * Метод для переноса резервных копий на внешний накопитель
     * @param array $rowData
     */
    public function transferBackup(array $rowData): void {}

    /**
     * Запись прогресса в StorageTable
     * @param int $loadedFiles
     * @param int $totalFiles
     * @param int $rowId
     * @return UpdateResult
     * @throws \Exception
     */
    public function setProgress(int $loadedFiles, int $totalFiles, int $rowId): UpdateResult
    {
        $percent = round($loadedFiles * 100 / $totalFiles, 0);

        return StorageTable::update(
            $rowId,
            [
                'PERCENT' => $percent
            ]
        );
    }
}