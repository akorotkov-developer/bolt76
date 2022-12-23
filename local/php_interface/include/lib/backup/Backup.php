<?php

namespace Strprofi;

use CBackup;
use CModule;
use CBitrixCloudBackup;
use CDBResult;
use CFile;
use CTar;
use StrprofiBackupCloud\StorageTable;
use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use League\Event\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Класс для backup ов
 */
class Backup
{
    /**
     * Корневая папка для бэкапов модуля
     */
    const ROOT_FOLDER_BACKUP = '/Bitrix backups';

    private string $token;

    public function __construct($token)
    {
        // Получаем резервные копии из папки backup
        define('DOCUMENT_ROOT', rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/'));

        $this->token = $token;
    }

    /**
     * Закачать Backup на внешний диск
     * @param array $backup
     */
    public function uploadBackUp(array $backup)
    {
        // Получаем список файлов backup а
        $path = BX_ROOT . "/backup";
        $name = $path . '/' . $backup['ID'];

        $arLink = [];
        while (file_exists(DOCUMENT_ROOT . $name)) {
            $arLink[] = htmlspecialcharsbx($name);
            $name = CTar::getNextName($name);
        }

        $dataForStorage = [
            'LINKS' => $arLink,
            'TOTAL_ITEMS' => count($arLink),
            'NAME' => $backup['NAME']
        ];

        // Записываем информацию в StorageTable
        $rowId = $this->addBackupInfoToStorage($dataForStorage);

        // Загружаем файлы на Яндекс.Диск
        $this->uploadToYaDisk($rowId);
    }

    /**
     * Метод агента для загрузки резервной копии на Яндекс.Диск
     * @param int $rowId
     * @return false|string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function uploadToYaDisk(int $rowId)
    {
        /**
         * Будем работать с большими массивами. Добавим памяти.
         */
        if ($rowId > 0) {
            $rowData = StorageTable::getRowById($rowId);

            // Если процент загрузки меньше 100, то продолжаем загрузку данных на Яндекс.Диск
            if ($rowData !== null && (int)$rowData['PERCENT'] < 100) {
                $data = $rowData['DATA'];

                // Отправляем первую ссылку на загрузку
                \Bitrix\Main\Diag\Debug::dumpToFile(['$data' => $data], '', 'log.txt');
                if (count($data['LINKS']) > 0) {
                    $link = array_shift($data['LINKS']);

                    echo '<pre>';
                    var_dump($link);
                    echo '</pre>';

                    $this->uploadFileToYaDisk($link, $data, $rowId);
                }
            }
        }
    }

    /**
     * Сохранение данных в Storage
     * @param int $rowId
     * @param array $data
     * @throws \Exception
     */
    private function saveDataInStorage(int $rowId, array $data)
    {
        $percent = round(100 - (count($data['LINKS']) * 100 / (int)$data['TOTAL_ITEMS']), 0);

        StorageTable::update(
            $rowId,
            [
                'DATA' => $data,
                'PERCENT' => $percent
            ]
        );
    }

    /**
     * Загрузить файл на яндекс диск
     */
    public function uploadFileToYaDisk($link, $data, $rowId)
    {
        // передать OAuth-токен зарегистрированного приложения.
        $disk = new \Arhitector\Yandex\Disk($this->token);

        $backUpName = $data['NAME'];

        // Получаем папку с backup ами или создаем папку
        $resource = $disk->getResource(self::ROOT_FOLDER_BACKUP);
        if (!$resource->has()) {
            $resource->create();
        }

        $resource = $disk->getResource(self::ROOT_FOLDER_BACKUP . '/' . SITE_SERVER_NAME);
        if (!$resource->has()) {
            $resource->create();
        }

        // Создаем папку для резервной копии
        $folderToCopy = self::ROOT_FOLDER_BACKUP . '/' . SITE_SERVER_NAME . '/' . $backUpName;
        $resource = $disk->getResource($folderToCopy);
        if (!$resource->has()) {
            $resource->create();
        }

        // Копирование резервной копии на яндекс.диск
        $spellLink = explode('/', $link);
        $fileName = $spellLink[count($spellLink) - 1];

        $resource = $disk->getResource($folderToCopy . '/' . $fileName);

        // Рекурсивная запись файлов на Яндекс.Диск
        $selfOb = $this;
        if (!$resource->has()) {
            $disk->addListener('uploaded', function (Event $event, Closed $resource, Disk $disk, StreamInterface $uploadedStream, ResponseInterface $response) use ($selfOb, $rowId, $data) {
                \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Файл загружен'], '', 'log.txt');

                // Записываем процент и ссылки в таблицу StorageTable
                $selfOb->saveDataInStorage($rowId, $data);

                // Загрузка следующего файла
                $selfOb->uploadToYaDisk($rowId);
            });

            $localFilePath = DOCUMENT_ROOT . $link;
            $resource->upload($localFilePath); // Записываем файл на яндекс диск
        }


        /*      $resource = $disk->getResource('/test_backup2');


                $isDir = $resource->getPath();
                echo '<pre>';
                var_dump($isDir);
                echo '</pre>';

                // Получаем иттератор
                $resource->items->getIterator();

                // Количество элементов внутри ресурса
                echo '<pre>';
                var_dump($resource->items->count());
                echo '</pre>';

                foreach ($resource->items as $item) {

                    echo '<pre>';
                    var_dump( $item->get('name'));
                    echo '</pre>';
                    echo '<pre>';
                    var_dump($item->toArray());
                    echo '</pre>';
                }*/


        /*$resource->upload( $_SERVER['DOCUMENT_ROOT'] . '/test_backup/new_file.txt');

        echo '<pre>';
        var_dump($resource->toArray(['name', 'type', 'size']));
        echo '</pre>';*/
    }

    /**
     * Записываем информацию в StorageTable
     * @param array $arLink
     */
    private function addBackupInfoToStorage(array $dataForStorage): int
    {
        $storageAdd = StorageTable::add(
            [
                'DATA' => $dataForStorage,
                'PERCENT' => 0,
            ]
        );

        return (int)$storageAdd->getId();
    }

    /**
     * Добавляем агента для загрузки резервных копий на Яндекс диск
     */
    /*private function addAgent($rowId)
    {
        echo '<pre>';
        var_dump('Добавился агент');
        echo '</pre>';

        CAgent::addAgent(
            '\Strprofi\Backup::uploadToYaDisk(' . $rowId . ');',
            'strprofibackupcloud',
            'Y',
            30,
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            ),
            'Y',
            ConvertTimeStamp(
                time() + \CTimeZone::getOffset(),
                'FULL'
            )
        );
    }*/
}