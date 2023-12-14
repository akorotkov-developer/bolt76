<?php

namespace StrprofiBackupCloud;

use StrprofiBackupCloud\Interfaces\IUploadByAgent;
use StrprofiBackupCloud\LocalBackup;

class UploadByAgent implements IUploadByAgent
{
    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function upload(): string
    {
        // Получаем контроллер для переноса бэкапов
        $controller = CloudFactory::factory('yadisk');

        $backUpList = LocalBackup::getLocalBackups();
        reset($backUpList);
        $backup = current($backUpList);

        if (!empty($backup['files']) && count($backup['files']) > 0) {
            // Отправляем задание на загрузку копий по $rowId
            $uploader = new UploadActivity();
            $uploader->uploadToExternalDrive($controller, $backup['files']);
        }

        return '\\' . __METHOD__ . '();';
    }
}