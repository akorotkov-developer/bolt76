<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class strprofibackupcloud extends CModule
{
    private $errors;

    public function __construct()
    {
        $arModuleVersion = array();

        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_ID = 'strprofibackupcloud';
        $this->MODULE_NAME = Loc::getMessage('BACKUP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('BACKUP_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->MODULE_ROOT_DIR = dirname(__DIR__);
        $this->PARTNER_NAME = Loc::getMessage('BACKUP_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'https://strprofi.ru/';
    }

    /**
     * @return bool
     */
    public function doInstall(): bool
    {
        $this->installDB();
        $this->installFiles();
        ModuleManager::registerModule($this->MODULE_ID);

        return true;
    }

    /**
     * @return bool
     */
    public function doUninstall(): bool
    {
        $this->unInstallDB();
        $this->uninstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    /**
     * Установка файлов
     */
    public function installFiles()
    {
        copy($this->MODULE_ROOT_DIR . '/install/admin/strprofi_backup_journal.php', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/strprofi_backup_journal.php');
        copy($this->MODULE_ROOT_DIR . '/install/admin/js/script.js', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . $this->MODULE_ID . '/script.js');
    }

    /**
     * Создание таблиц
     * @return array|bool|string[]
     */
    public function installDB()
    {
        global $DB;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($this->MODULE_ROOT_DIR . '/install/db/mysql/install.sql');
        if (!$this->errors) {
            return true;
        } else {
            return $this->errors;
        }
    }

    /**
     * Удаление файлов
     */
    public function uninstallFiles()
    {
        unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/strprofi_backup_journal.php");
        unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID . '/script.js');
    }

    /**
     * Удаление таблиц
     * @return array|bool|string[]|void
     */
    function unInstallDB()
    {
        global $DB;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($this->MODULE_ROOT_DIR . '/install/db/mysql/uninstall.sql');
        if (!$this->errors) {
            return true;
        } else {
            return $this->errors;
        }
    }
}
