<?php

namespace StrprofiBackupCloud;

class UploadByAgent
{
    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param $rowId
     * @return false
     */
    public static function uploadToExternalDrive($rowId)
    {
        if ($rowId > 0) {
            // Отправляем задание на загрузку копий по $rowId
            $uploader = new UploadActivity();
            $uploader->uploadToExternalDrive($rowId);
        }

        return false;
    }
}