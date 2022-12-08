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
            'items' => [
                [
                    'text' => Loc::getMessage('BACKUP_LOG_MENU_TITLE'),
                    'url' => '/bitrix/admin/strprofi_backup.php?lang=' . LANGUAGE_ID,
                    'more_url' => ['/bitrix/admin/strprofi_backup.php?lang=' . LANGUAGE_ID],
                    'title' => Loc::getMessage('BACKUP_LOG_MENU_TITLE'),
                ],
            ],
        ],
    ];

    return $menu;
}

return false;