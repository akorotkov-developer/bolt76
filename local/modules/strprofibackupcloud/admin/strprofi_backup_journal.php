<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use StrprofiBackupCloud\Backup;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'strprofibackupcloud');

require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_after.php";

$APPLICATION->SetTitle("Резервные копии на Яндекс.Диске");
?>

<div class="backup-container">
    <?php
    Loader::includeModule('strprofibackupcloud');

    $obBackup = new Backup();
    $token = $obBackup->getToken();

    echo '<pre>';
    var_dump($token);
    echo '</pre>';
    ?>

	<input type="button" value="Запустить перенос резервных копий" onClick="clearLogFile();" />
</div>

<?

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php';