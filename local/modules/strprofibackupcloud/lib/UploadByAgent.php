<?php

namespace StrprofiBackupCloud;

use StrprofiBackupCloud\Interfaces\IUploadByAgent;
use StrprofiBackupCloud\Dump\Dump;

class UploadByAgent implements IUploadByAgent
{
    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param $rowId
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function upload($rowId): bool
    {
        if ($rowId > 0) {
            // Делаем бэкап сайта
            \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Перед дампом'], '', 'log.txt');
            $dump = new Dump();
            $dump->createDump2();

            $rowData = StorageTable::getRowById($rowId);

            // Получаем контроллер для переноса бэкапов
            $controller = CloudFactory::factory($rowData['DISK_TYPE']);

            // Отправляем задание на загрузку копий по $rowId
            $uploader = new UploadActivity();
            $uploader->uploadToExternalDrive($controller, $rowData);
        }

        return false;
    }
}