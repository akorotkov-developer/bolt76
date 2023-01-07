<?php

namespace StrprofiBackupCloud;

use StrprofiBackupCloud\Controller;
use Exception;
use StrprofiBackupCloud\Controller\Formats\BaseCloud;

/**
 * Класс для определения внешнего накопителя для переноса резервных копий
 */
class CloudFactory
{
    /**
     * Возвращает контроллер для внешнего накопителя
     * @param string $diskType
     * @return BaseCloud
     * @throws Exception
     */
    public static function factory(string $diskType): BaseCloud
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