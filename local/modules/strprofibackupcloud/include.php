<?php
/**
 * Подключение классов модуля
 */
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\Backup" => "lib/Backup.php",
    ]
);
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\Option" => "lib/Option.php",
    ]
);
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\StorageTable" => "lib/StorageTable.php",
    ]
);
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\UploadActivity" => "lib/UploadActivity.php",
    ]
);
Bitrix\Main\Loader::registerAutoloadClasses(
    "strprofibackupcloud",
    [
        "StrprofiBackupCloud\\UploadByAgent" => "lib/UploadByAgent.php.php",
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