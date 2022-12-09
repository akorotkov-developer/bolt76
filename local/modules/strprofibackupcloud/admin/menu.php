<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight("strprofibackupcloud") > "D") {

    if (!CModule::IncludeModule('strprofibackupcloud')) {
        return false;
    }

    $menu = [
        [
            'parent_menu' => 'global_menu_services',
            'sort' => 400,
            'text' => Loc::getMessage('BACKUP_MENU_TITLE'),
            'title' => Loc::getMessage('BACKUP_MENU_TITLE'),
            'items_id' => 'menu_references',
            'icon' => 'bitrixcloud_menu_icon',
            'url' => '/bitrix/admin/strprofi_backup_journal.php?lang=' . LANGUAGE_ID,
            'items' => [],
        ],
    ];

    return $menu;
}

return false;