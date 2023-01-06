<?php

namespace StrprofiBackupCloud\Controller;

use PackageLoader\PackageLoader;
use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use Bitrix\Main\Config\Option;
use League\Event\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use StrprofiBackupCloud\StorageTable;
use StrprofiBackupCloud\Option as ModuleOption;

/**
 * Класс для загрузки данных на Яндекс.Диск
 */
class YaDisk
{
    /**
     * Токен для доступа к Яндекс.Диск
     * @var string
     */
    private string $token;

    /**
     * Количество ссылок на архивы бэкапов, используется для получения процента завершения загрузки
     * @var int
     */
    private int $totalLinks = 0;
    /**
     * Количество загруженных ссылок на архивы бэкапов, используется для получения процента завершения загрузки
     * @var int
     */
    private int $uploadedLinks = 0;

    /**
     * Корневая папка для бэкапов модуля
     */
    const ROOT_FOLDER_BACKUP = '/BackUp strprofi.ru';

    const ADMIN_MODULE_NAME = 'strprofibackupcloud';

    public function __construct()
    {
        // Уберем ограничение времени выполнения скрипта
        set_time_limit(0);

        // Отключаем прерывание скрипта при отключении клиента
        ignore_user_abort(true);

        // Подключить все зависимости для Яндекс SDK
        $this->loadSDK();

        $this->token = Option::get(self::ADMIN_MODULE_NAME, "yandextoken");
    }

    /**
     * Подключение SDK Яндекса и его зависимостей
     */
    private function loadSDK()
    {
        $homeDir = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ModuleOption::ADMIN_MODULE_NAME . '/lib/SDK/';
        $requires = [
            $homeDir . 'arhitector/yandex',
            $homeDir . 'arhitector/requires/laminas/laminas-diactoros',
            $homeDir . 'arhitector/requires/laminas/laminas-escaper',
            $homeDir . 'arhitector/requires/league/event',
            $homeDir . 'arhitector/requires/php-http/client-common',
            $homeDir . 'arhitector/requires/php-http/curl-client',
            $homeDir . 'arhitector/requires/php-http/message',
            $homeDir . 'arhitector/requires/php-http/httplug',
            $homeDir . 'arhitector/requires/php-http/message-factory',
            $homeDir . 'arhitector/requires/php-http/promise',
            $homeDir . 'arhitector/requires/psr/http-client',
            $homeDir . 'arhitector/requires/psr/http-factory',
            $homeDir . 'arhitector/requires/psr/http-message',
            $homeDir . 'arhitector/requires/psr/simple-cache',
            $homeDir . 'arhitector/requires/symfony/options-resolver',
            $homeDir . 'arhitector/requires/symfony/deprecation-contracts',
            $homeDir . 'arhitector/requires/symfony/polyfill-mbstring',
            $homeDir . 'arhitector/requires/symfony/polyfill-php73',
            $homeDir . 'arhitector/requires/symfony/polyfill-php80',
        ];

        foreach ($requires as $requirePath) {
            $loader = new PackageLoader();
            $loader->load($requirePath);
        }
    }

    /**
     * Перенос бэкапов на Яндекс.Диск
     * @param $rowData
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function transferBackup(array $rowData)
    {
        if ((int)$rowData['PERCENT'] < 100) {
            // ID Текущей операции
            Option::set(self::ADMIN_MODULE_NAME, "CUR_TASK_ID", $rowData['ID']);

            $data = $rowData['DATA'];

            if (count($data) > 0) {
                $backupInfo = $this->getBackUpInfo($data);

                foreach ($backupInfo as $backUpItem) {
                    foreach ($backUpItem['LINKS'] as $link) {
                        $this->uploadFileToYaDisk($link, $backUpItem['NAME'], $rowData['ID']);
                        sleep(2);
                    }
                }
            }
        }
    }

    /**
     * Загрузить файл на яндекс диск
     */
    public function uploadFileToYaDisk($link, $backUpName, $storageFieldId)
    {
        // передать OAuth-токен зарегистрированного приложения.
        $disk = new \Arhitector\Yandex\Disk($this->token);

        // Получаем папку с backup ами или создаем папку
        $resource = $disk->getResource(self::ROOT_FOLDER_BACKUP);
        if (!$resource->has()) {
            $resource->create();
        }

        $resource = $disk->getResource(self::ROOT_FOLDER_BACKUP);
        if (!$resource->has()) {
            $resource->create();
        }

        // Создаем папку для резервной копии
        $folderToCopy = self::ROOT_FOLDER_BACKUP . '/' . $backUpName;
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
            $disk->addListener('uploaded', function (Event $event, Closed $resource, Disk $disk, StreamInterface $uploadedStream, ResponseInterface $response) use ($selfOb, $storageFieldId) {
                $this->uploadedLinks++;

                // Записываем процент и ссылки в таблицу StorageTable
                $selfOb->saveDataInStorage($storageFieldId);
            });

            $localFilePath = $_SERVER['DOCUMENT_ROOT'] . $link;
            $resource->upload($localFilePath); // Записываем файл на яндекс диск
        }
    }

    // TODO вынести эту функцию в отдельный класс (Такой метод должен быть во всех дисках)
    /**
     * Сохранение данных в Storage
     * @throws \Exception
     */
    private function saveDataInStorage($id)
    {
        $percent = round($this->uploadedLinks * 100 / $this->totalLinks, 0);

        StorageTable::update(
            $id,
            [
                'PERCENT' => $percent
            ]
        );
    }

    // TODO вынести эту функцию в отдельный класс
    /**
     * Получаем информацию о бэкапах
     * @param array $data
     * @return array
     */
    private function getBackUpInfo(array $data): array
    {
        $backInfo = [];

        foreach ($data as $key => $backupItem) {
            // Получаем список файлов backup а
            $path = BX_ROOT . "/backup";
            $name = $path . '/' . $backupItem['ID'];

            $arLink = [];
            while (file_exists($_SERVER['DOCUMENT_ROOT'] . $name)) {
                $arLink[] = htmlspecialcharsbx($name);
                $name = $this->getNextName($name);
            }

            $backInfo[$key] = [
                'LINKS' => $arLink,
                'TOTAL_ITEMS' => count($arLink),
                'NAME' => $backupItem['NAME']
            ];

            // Общее количество ссылок во всех резервных копиях
            // для определения прогресса загрузки
            $this->totalLinks += count($arLink);
        }

        return $backInfo;
    }

    // TODO вынести эту функцию в отдельный класс

    /**
     * Получить имя файла
     * @param $file
     */
    private function getNextName($file)
    {
        if (!$file)
            $file = $this->file;

        static $CACHE;
        $c = &$CACHE[$file];

        if (!$c) {
            $l = strrpos($file, '.');
            $num = substr($file, $l + 1);
            if (is_numeric($num))
                $file = substr($file, 0, $l + 1) . ++$num;
            else
                $file .= '.1';
            $c = $file;
        }
        return $c;
    }
}