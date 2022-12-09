<?php

namespace StrprofiBackupCloud;

use Bitrix\Main\Config\Option;

class Backup
{
    const ADMIN_MODULE_NAME = 'strprofibackupcloud';

    /**
     * Токен для доступа к Яндекс диску
     * @var string
     */
    private string $yandexToken;

    public function __construct()
    {
        $this->yandexToken = Option::get(self::ADMIN_MODULE_NAME, "yandextoken");
    }

    public function getToken()
    {
        return $this->yandexToken;
    }
}