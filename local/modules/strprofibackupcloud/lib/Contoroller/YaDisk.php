<?php

namespace StrprofiBackupCloud\Controller;

use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use Bitrix\Main\Config\Option;
use League\Event\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use StrprofiBackupCloud\StorageTable;

// TODO НЕ ЗАБЫТЬ УБРАТЬ ЭТО ОТСЮДА!!!
require($_SERVER['DOCUMENT_ROOT']  . '/local/include/vendor/autoload.php');

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
     * Данные бэкапов
     * @var array
     */
    private array $rowData;

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
    const ROOT_FOLDER_BACKUP = '/Bitrix backups';

    const ADMIN_MODULE_NAME = 'strprofibackupcloud';

    public function __construct($rowData)
    {
        // Уберем ограничение времени выполнения скрипта
        set_time_limit(0);

        // Отключаем прерывание скрипта при отключении клиента
        ignore_user_abort(true);

        $this->token = Option::get(self::ADMIN_MODULE_NAME, "yandextoken");
        $this->rowData = $rowData;
    }

    /**
     * Перенос бэкапов на Яндекс.Диск
     */
    public function transferBackup()
    {
        if ($this->rowData !== null && (int)$this->rowData['PERCENT'] < 100) {
            // ID Текущей операции
            Option::set(self::ADMIN_MODULE_NAME, "CUR_TASK_ID", $this->rowData['ID']);

            $data = $this->rowData['DATA'];

            \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Запущен перенос копий на Яндекс Диск'], '', 'log.txt');

            if (count($data) > 0) {
                $backupInfo = $this->getBackUpInfo($data);

                foreach ($backupInfo as $backUpItem) {
                    foreach ($backUpItem['LINKS'] as $link) {
                        $this->uploadFileToYaDisk($link, $backUpItem['NAME']);
                    }
                }
            }
        }
    }

    /**
     * Загрузить файл на яндекс диск
     */
    public function uploadFileToYaDisk($link, $backUpName)
    {
        // передать OAuth-токен зарегистрированного приложения.
        $disk = new \Arhitector\Yandex\Disk($this->token);

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
                $this->uploadedLinks++;

                // Записываем процент и ссылки в таблицу StorageTable
                $selfOb->saveDataInStorage();
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
    private function saveDataInStorage()
    {
        $percent = round($this->uploadedLinks * 100 / $this->totalLinks, 0);

        StorageTable::update(
            $this->rowData['ID'],
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