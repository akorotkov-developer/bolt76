<?php

namespace StrprofiBackupCloud;

use CDBResult;
use CFile;

/**
 * Класс для работы с локальными бэкапами
 */
class LocalBackup
{
    /**
     * Получить backup 'ы c сайта
     */
    public function getLocalBackups(): array
    {
        $arFiles = array();
        $arTmpFiles = array();

        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/backup')) {
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

            $arBackups[$f['NAME']] = [
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
}