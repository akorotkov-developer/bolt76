<?php
use Bitrix\Main\Loader;

/**
 * Подключение классов модуля
 */
Loader::registerAutoloadClasses(
    'strprofibackupcloud',
    [
        'StrprofiBackupCloud\\Backup' => 'lib/Backup.php',
        'StrprofiBackupCloud\\Option' => 'lib/Option.php',
        'StrprofiBackupCloud\\StorageTable' => 'lib/StorageTable.php',
        'StrprofiBackupCloud\\UploadActivity' => 'lib/UploadActivity.php',
        'StrprofiBackupCloud\\UploadByAgent' => 'lib/UploadByAgent.php',
        'StrprofiBackupCloud\\LocalBackup' => 'lib/LocalBackup.php',
        'StrprofiBackupCloud\\Uploader' => 'lib/Uploader.php',
        'StrprofiBackupCloud\\CloudFactory' => 'lib/CloudFactory.php',
        'StrprofiBackupCloud\\PackageLoader' => 'lib/PackageLoader.php',
        'StrprofiBackupCloud\\Dump\\Dump' => 'lib/Dump/Dump.php',
        'StrprofiBackupCloud\\Dump\\Helpers\\CDirScan' => 'lib/Dump/Helpers/CDirScan.php',
        'StrprofiBackupCloud\\Dump\\Helpers\\CDirRealScan' => 'lib/Dump/Helpers/CDirRealScan.php',
        'StrprofiBackupCloud\\Dump\\Helpers\\CBackup' => 'lib/Dump/Helpers/CBackup.php',
        'StrprofiBackupCloud\\Controller\\YaDisk' => 'lib/Controller/YaDisk.php',
        'StrprofiBackupCloud\\Controller\\Formats\\BaseCloud' => 'lib/Controller/Formats/BaseCloud.php',
        'StrprofiBackupCloud\\Interfaces\\ILocalBackup' => 'lib/Interfaces/ILocalBackup.php',
        'StrprofiBackupCloud\\Interfaces\\IUploadActivity' => 'lib/Interfaces/IUploadActivity.php',
        'StrprofiBackupCloud\\Interfaces\\IBaseCloud' => 'lib/Interfaces/IBaseCloud.php',
        'StrprofiBackupCloud\\Interfaces\\IUploadByAgent' => 'lib/Interfaces/IUploadByAgent.php',
        'StrprofiBackupCloud\\Interfaces\\IOption' => 'lib/Interfaces/IOption.php',
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