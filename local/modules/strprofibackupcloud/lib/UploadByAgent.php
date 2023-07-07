<?php

namespace StrprofiBackupCloud;

use StrprofiBackupCloud\Interfaces\IUploadByAgent;
use StrprofiBackupCloud\Dump\Dump;

class UploadByAgent implements IUploadByAgent
{
    /**
     * Загрузка бэкапов на внешний диск, по параметрам из записи в StorageTable c id = $rowId
     * @param $rowId
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function upload($rowId): string
    {
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Начало работы агента'], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['__METHOD__' => '\\' . __METHOD__ . '(' . $rowId . ');'], '', 'log.txt');
        // Делаем резервную копию
        $dump = new Dump();
        $dump->CreateDump();

        $rowData = StorageTable::getRowById($rowId);

        // Получаем контроллер для переноса бэкапов
        $controller = CloudFactory::factory('yadisk');

        // Отправляем задание на загрузку копий по $rowId
        $uploader = new UploadActivity();
        $uploader->uploadToExternalDrive($controller, $rowData);
        \Bitrix\Main\Diag\Debug::dumpToFile(['$rowId' => $rowId], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => '\StrprofiBackupCloud\UploadByAgent::upload(' . $rowId . ');'], '', 'log.txt');

        return '\\' . __METHOD__ . '(' . $rowId . ');';
    }
}