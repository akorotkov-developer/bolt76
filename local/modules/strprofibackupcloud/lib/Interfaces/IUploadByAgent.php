<?php

namespace StrprofiBackupCloud\Interfaces;

interface IUploadByAgent
{
    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @return bool
     */
    public static function upload(): string;
}