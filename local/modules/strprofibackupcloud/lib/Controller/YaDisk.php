<?php

namespace StrprofiBackupCloud\Controller;

use Bitrix\Main\Localization\Loc;
use StrprofiBackupCloud\PackageLoader;
use Arhitector\Yandex\Disk;
use Arhitector\Yandex\Disk\Resource\Closed;
use League\Event\Event;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use StrprofiBackupCloud\Option as ModuleOption;
use StrprofiBackupCloud\LocalBackup;
use StrprofiBackupCloud\Controller\Formats\BaseCloud;
use Exception;

/**
 * Класс для загрузки данных на Яндекс.Диск
 */
class YaDisk extends BaseCloud
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
     * Экземпляр класса ModuleOption
     * @var ModuleOption
     */
    private ModuleOption $option;

    /**
     * Корневая папка для бэкапов модуля
     */
    const ROOT_FOLDER_BACKUP = '/BackUp strprofi.ru';

    public function __construct()
    {
        // Уберем ограничение времени выполнения скрипта
        set_time_limit(0);

        // Отключаем прерывание скрипта при отключении клиента
        ignore_user_abort(true);

        // Подключить все зависимости для Яндекс SDK
        $this->loadSDK();

        $this->option = new ModuleOption();
        $this->token = $this->option->getOption("yandextoken");
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
     * @param array $backUpFiles
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function transferBackup(array $backUpFiles): void
    {
        \Bitrix\Main\Diag\Debug::dumpToFile(['$backUpFiles' => $backUpFiles], '', 'log.txt');
        if (count($backUpFiles) > 0) {
            $localBackup = new LocalBackup();
            $this->totalLinks = (count($backUpFiles));

            foreach ($backUpFiles as $backUpItem) {
                $this->uploadFileToYaDisk($backUpItem, date('d.m.Y') . '_backup');
                sleep(2);
            }

            // Удаление бэкапа после закачки на внешний диск
            $localBackup->delete($backUpFiles);
        }
    }

    /**
     * Загрузить файл на яндекс диск
     * @param $link
     * @param $backUpName
     * @param $rowId
     */
    public function uploadFileToYaDisk($link, $backUpName)
    {
        try {
            // передать OAuth-токен зарегистрированного приложения.
            $disk = new Disk($this->token);

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

            // Запись файлов на Яндекс.Диск
            if (!$resource->has()) {
                $disk->addListener('uploaded', function (Event $event, Closed $resource, Disk $disk, StreamInterface $uploadedStream, ResponseInterface $response) use ($selfOb, $rowId) {
                    $this->uploadedLinks++;
                });

                $localFilePath = $_SERVER['DOCUMENT_ROOT'] . $link;
                $resource->upload($localFilePath); // Записываем файл на яндекс диск
            }
        } catch (Exception $e) {
            \Bitrix\Main\Diag\Debug::dumpToFile([date('d.m.Y H:i:s') . ' $e->getMessage()' => $e->getMessage()], '', 'log.txt');
        }
    }
}