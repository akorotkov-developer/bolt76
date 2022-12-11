<?php
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\Backup" => "lib/Backup.php",
    ]
);

/**
 * Подключение js файлов
 */
$arJsConfig = [
    'strprofibackupcloud' => [
        'js' => '/local/modules/strprofibackupcloud/src/js/script.js',
        'rel' => [],
    ]
];

foreach ($arJsConfig as $ext => $arExt) {
    \CJSCore::RegisterExt($ext, $arExt);
}