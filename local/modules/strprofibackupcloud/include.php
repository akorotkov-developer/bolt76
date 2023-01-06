<?php
use Bitrix\Main\Loader;

/**
 * Подключение классов модуля
 */
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\Backup' => 'lib/Backup.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\Option' => 'lib/Option.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\StorageTable' => 'lib/StorageTable.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\UploadActivity' => 'lib/UploadActivity.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\UploadByAgent' => 'lib/UploadByAgent.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\LocalBackup' => 'lib/LocalBackup.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\Uploader' => 'lib/Uploader.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\CloudFactory' => 'lib/CloudFactory.php',
    ]
);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\Controller\\YaDisk' => 'lib/Controller/YaDisk.php',
    ]
);
Loader::registerAutoLoadClasses(null, [
    'Strprofi\Backup' => '/local/php_interface/include/lib/backup/Backup.php'
]);
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'PackageLoader\\PackageLoader' => 'lib/PackageLoader.php',
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