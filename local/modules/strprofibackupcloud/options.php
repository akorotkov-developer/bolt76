<?php

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'strprofibackupcloud');

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

$app = Application::getInstance();
$context = $app->getContext();
$request = $context->getRequest();

Loc::loadMessages($context->getServer()->getDocumentRoot() . "/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

// Таб на странице настройки модулей
$tabControl = new CAdminTabControl("tabControl", [
    [
        "DIV" => "b2f_prefs",
        "TAB" => Loc::getMessage("MAIN_TAB_SET"),
        "TITLE" => Loc::getMessage("MAIN_TAB_TITLE_SET"),
    ],
]);

//// Сохранение настроек ////
if (!empty($save) && $request->isPost() && check_bitrix_sessid()) {
    if ($request->getPost('yandextoken')) {
        Option::set(ADMIN_MODULE_NAME, "yandextoken", $request->getPost('yandextoken'));

        CAdminMessage::showMessage([
            "MESSAGE" => Loc::getMessage("PREFERENCES_OPTIONS_SAVED"),
            "TYPE" => "OK",
        ]);
    } else {
        CAdminMessage::showMessage(Loc::getMessage("PREFERENCES_INVALID_VALUE"));
    }
}

$tabControl->begin();
?>
<form method="post"
      action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
    <?php
    echo bitrix_sessid_post();
    $tabControl->beginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label for="domain">Токен Яндекс Диска:</label>
        <td width="60%">
            <input type="text"
                   size="50"
                   maxlength="255"
                   name="yandextoken"
                   value="<?= Option::get(ADMIN_MODULE_NAME, "yandextoken") ?>"
            />
        </td>
    </tr>

    <?php
    $tabControl->buttons();
    ?>
    <input type="submit"
           name="save"
           value="<?= Loc::getMessage("MAIN_SAVE") ?>"
           title="<?= Loc::getMessage("MAIN_OPT_SAVE_TITLE") ?>"
           class="adm-btn-save"
    />
    <?php
    $tabControl->end();
    ?>
</form>