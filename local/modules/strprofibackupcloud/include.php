<?php
use PackageLoader\PackageLoader;
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
        'StrprofiBackupCloud\\Controller\\YaDisk' => 'lib/Contoroller/YaDisk.php',
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
 * Подключение SDK и зависимостей
 */

$homeDir = __DIR__ . '/lib/SDK/';
$requires = [
    $homeDir . 'arhitector/yandex',
    $homeDir . 'arhitector/requires/laminas/laminas-diactoros',
    $homeDir . 'arhitector/requires/laminas/laminas-escaper',
    $homeDir . 'arhitector/requires/league/event',
    $homeDir . 'arhitector/requires/php-http/client-common',
    $homeDir . 'arhitector/requires/php-http/curl-client',
    $homeDir . 'arhitector/requires/php-http/message',
    $homeDir . 'arhitector/requires/php-http/httplug',
    $homeDir . 'arhitector/requires/php-http/message-factory',
    $homeDir . 'arhitector/requires/psr/http-client',
    $homeDir . 'arhitector/requires/psr/http-factory',
    $homeDir . 'arhitector/requires/psr/http-message',
    $homeDir . 'arhitector/requires/psr/simple-cache',
    $homeDir . 'arhitector/requires/symfony/options-resolver',
    $homeDir . 'arhitector/requires/symfony/deprecation-contracts',
    $homeDir . 'arhitector/requires/symfony/polyfill-mbstring',
    $homeDir . 'arhitector/requires/symfony/polyfill-php73',
    $homeDir . 'arhitector/requires/symfony/polyfill-php80',
];

foreach ($requires as $requirePath) {
    $loader = new PackageLoader();
    $loader->load($requirePath);
}

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