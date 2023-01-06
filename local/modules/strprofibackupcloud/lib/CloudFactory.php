<?php

namespace StrprofiBackupCloud;

use StrprofiBackupCloud\Controller;
use Exception;

/**
 * Класс для определения внешнего накопителя для переноса резервных копий
 */
class CloudFactory
{
    /**
     * Возвращает контроллер для внешнего накопителя
     * @param string $diskType
     * @return Controller\YaDisk
     * @throws Exception
     */
    public static function factory(string $diskType)
    {
        switch ($diskType) {
            case 'yadisk':
                $controller = new Controller\YaDisk();
                break;
            default:
                throw new Exception('Неизвестный внешний накопитель');
        }

        return $controller;
    }
}