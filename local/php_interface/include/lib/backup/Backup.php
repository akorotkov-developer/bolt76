<?php

namespace Strprofi;

use CBackup;
use CModule;
use CBitrixCloudBackup;
use CDBResult;
use CFile;
use CTar;

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
     * Получить backup 'ы c сайта
     */
    public function getBackupSite(): array
    {
        $bMcrypt = function_exists('mcrypt_encrypt') || function_exists('openssl_encrypt');
        $bBitrixCloud = $bMcrypt && CModule::IncludeModule('bitrixcloud') && CModule::IncludeModule('clouds');

        $arFiles = array();
        $arTmpFiles = array();

        if (is_dir($p = DOCUMENT_ROOT . BX_ROOT . '/backup')) {
            if ($dir = opendir($p)) {
                while (($item = readdir($dir)) !== false) {
                    $f = $p . '/' . $item;
                    if (!is_file($f))
                        continue;
                    $arTmpFiles[] = array(
                        'NAME' => $item,
                        'SIZE' => filesize($f),
                        'DATE' => filemtime($f),
                        'BUCKET_ID' => 0,
                        'PLACE' => 'Локально'
                    );
                }
                closedir($dir);
            }
        }

        // Получаем резервные копии из облака
        if ($bBitrixCloud) {
            $backup = CBitrixCloudBackup::getInstance();
            try {
                foreach ($backup->listFiles() as $ar) {
                    $arTmpFiles[] = array(
                        'NAME' => $ar['FILE_NAME'],
                        'SIZE' => $ar['FILE_SIZE'],
                        'DATE' => preg_match('#^([0-9]{4})([0-9]{2})([0-9]{2})_([0-9]{2})([0-9]{2})([0-9]{2})#', $ar['FILE_NAME'], $r) ? strtotime("{$r[1]}-{$r[2]}-{$r[3]} {$r[4]}:{$r[5]}:{$r[6]}") : '',
                        'BUCKET_ID' => -1,
                        'PLACE' => 'В облаке'
                    );
                }
            } catch (Exception $e) {
                $bBitrixCloud = false;
                $strBXError = $e->getMessage();
            }
        }

        // Резервные копии из журнала
        $arAllBucket = CBackup::GetBucketList();
        if ($arAllBucket) {
            foreach ($arAllBucket as $arBucket) {
                if ($arCloudFiles = CBackup::GetBucketFileList($arBucket['ID'], BX_ROOT . '/backup/')) {
                    foreach ($arCloudFiles['file'] as $k => $v) {
                        $arTmpFiles[] = array(
                            'NAME' => $v,
                            'SIZE' => $arCloudFiles['file_size'][$k],
                            'DATE' => preg_match('#^([0-9]{4})([0-9]{2})([0-9]{2})_([0-9]{2})([0-9]{2})([0-9]{2})#', $v, $r) ? strtotime("{$r[1]}-{$r[2]}-{$r[3]} {$r[4]}:{$r[5]}:{$r[6]}") : '',
                            'BUCKET_ID' => $arBucket['ID'],
                            'PLACE' => htmlspecialcharsbx($arBucket['BUCKET'] . ' (' . $arBucket['SERVICE_ID'] . ')')
                        );
                    }
                }
            }
        }

        // Формирования массива backup ов
        $arParts = [];
        $arSize = [];
        $i = 0;
        foreach ($arTmpFiles as $k => $ar) {
            if (preg_match('#^(.*\.(enc|tar|gz|sql))(\.[0-9]+)?$#', $ar['NAME'], $regs)) {
                $i++;
                $BUCKET_ID = intval($ar['BUCKET_ID']);
                $arParts[$BUCKET_ID . $regs[1]]++;
                $arSize[$BUCKET_ID . $regs[1]] += $ar['SIZE'];
                if (!$regs[3]) {
                    if ($by == 'size') {
                        $key = $arSize[$BUCKET_ID . $regs[1]];
                    } elseif ($by == 'timestamp') {
                        $key = $ar['DATE'];
                    } elseif ($by == 'location') {
                        $key = $ar['PLACE'];
                    } else { // name
                        $key = $regs[1];
                    }
                    $key .= '_' . $i;
                    $arFiles[$key] = $ar;
                }
            }
        }

        if ($order == 'desc') {
            krsort($arFiles);
        } else {
            ksort($arFiles);
        }

        // Получим данные о резервных копиях в виде объекта CDBResult
        $arBackups = [];

        $rsDirContent = new CDBResult;
        $rsDirContent->InitFromArray($arFiles);
        $rsDirContent->NavStart(20);

        while ($f = $rsDirContent->NavNext(true, "f_")) {
            $BUCKET_ID = intval($f['BUCKET_ID']);

            $c = $arParts[$BUCKET_ID . $f['NAME']];

            if ($c > 1) {
                $parts = ' ( частей: ' . $c . ')';
                $size = $arSize[$BUCKET_ID . $f['NAME']];
            } else {
                $parts = '';
                $size = $f['SIZE'];
            }

            $arBackups[] = [
                'NAME' => $f['NAME'] . $parts,
                'SIZE' => CFile::FormatSize($size),
                'PLACE' => $f['PLACE'],
                'DATE' => FormatDate('x', $f['DATE']),
                'ID' => $f['NAME'],
                'BUCKET_ID' => $BUCKET_ID,
                'PARTS' => $c
            ];
        }

        return $arBackups;
    }

    /**
     * Закачать Backup на внешний диск
     * @param array $backup
     * @return bool
     */
    public function uploadBackUp(array $backup)
    {
        // Получаем список файлов backup а
        $path = BX_ROOT . "/backup";
        $name = $path . '/' . $backup['ID'];

        while (file_exists(DOCUMENT_ROOT . $name)) {
            $arLink[] = htmlspecialcharsbx($name);
            $name = CTar::getNextName($name);
        }

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
        $folderToCopy = self::ROOT_FOLDER_BACKUP . '/' . SITE_SERVER_NAME . '/' . $backup['NAME'];
        $resource = $disk->getResource($folderToCopy);
        if (!$resource->has()) {
            $resource->create();
        }

        // Копирование резервной копии на яндекс.диск
        foreach ($arLink as $link) {
            $spellLink = explode('/', $link);
            $fileName = $spellLink[count($spellLink) - 1];

            $resource = $disk->getResource($folderToCopy . '/' . $fileName);

            if (!$resource->has()) {
                $localFilePath = DOCUMENT_ROOT . $link;
                $resource->upload($localFilePath); // Записываем файл на яндекс диск
                unlink($localFilePath); // Удаляем файл на сервере
            }
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

        return true;
    }
}