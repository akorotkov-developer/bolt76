<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class strprofibackupcloud extends CModule
{
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

    public function doInstall()
    {
        if ($this->installFiles()) {
            ModuleManager::registerModule($this->MODULE_ID);

            return true;
        }
    }

    public function doUninstall()
    {
        if ($this->uninstallFiles()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);

            return true;
        }
    }

    public function installFiles()
    {
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => 'Здесь'], '', 'log.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile(['fields' => $this->MODULE_ROOT_DIR . "/install/admin/strprofi_backup_journal.php"], '', 'log.txt');
        copy($this->MODULE_ROOT_DIR . "/install/admin/strprofi_backup_journal.php", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/strprofi_backup_journal.php");

        return true;

    }

    public function uninstallFiles()
    {
        unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/strprofi_backup_journal.php");

        return true;

    }

}
