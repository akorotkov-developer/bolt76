<?php

use Bitrix\Main\Loader;
use StrprofiBackupCloud\Backup;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

defined('ADMIN_MODULE_NAME') or define('ADMIN_MODULE_NAME', 'strprofibackupcloud');

require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_before.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin_after.php";
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/backup.php");

$APPLICATION->SetTitle("Резервные копии на Яндекс.Диске");

//
$context = Application::getInstance()->getContext();
$request = $context->getRequest();

if ($request->get('process') == 'Y') {
    $getParams = $request->getQueryList()->toArray();

    $stringParams = '';
    foreach ($getParams as $key => $param) {
        $stringParams .= $key . '=' . $param . '&';
    }
    $stringParams = trim($stringParams, '&');

    LocalRedirect('/bitrix/admin/dump.php?' . $stringParams);
    ?>
<?php };

if ($_REQUEST['ajax_mode'] == 'Y') {
    if ($_REQUEST['action'] == 'get_table_size') {
        ?>
        <script>
            BX('db_size').innerHTML = "(<?=CFile::FormatSize(getTableSize(""))?>)";
            BX('db_stat_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_stat"))?>)";
            BX('db_search_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_search"))?>)";
            BX('db_event_size').innerHTML = "(<?=CFile::FormatSize(getTableSize("^b_event_log$"))?>)";
        </script>
        <?
        die();
    }
}

function getTableSize($reg)
{
    global $DB;
    if ($DB->type != 'MYSQL')
        return 0;

    static $CACHE;

    if (!$CACHE) {
        $CACHE = array();
        $sql = "SHOW TABLE STATUS";
        $res = $DB->Query($sql);

        while ($row = $res->Fetch())
            $CACHE[$row['Name']] = $row['Data_length'];
    }

    $size = 0;
    foreach ($CACHE as $table => $s)
        if (!$reg || preg_match('#' . $reg . '#i', $table))
            $size += $s;
    return $size;
}

Loader::includeModule('strprofibackupcloud');
CJSCore::Init(['strprofibackupcloud']);

function IntOption($name, $def = 0)
{
    static $CACHE;
    if (!$CACHE[$name])
        $CACHE[$name] = COption::GetOptionInt("main", $name, $def);
    return $CACHE[$name];
}

$bGzip = function_exists('gzcompress');
$bMcrypt = function_exists('mcrypt_encrypt') || function_exists('openssl_encrypt');

global $DB;

$aTabs = [];
$aTabs[] = ['DIV' => 'std', 'TAB' => Loc::getMessage('DUMP_MAIN_MAKE_ARC'), 'ICON' => 'main_user_edit', 'TITLE' => Loc::getMessage('MAKE_DUMP_FULL')];
$aTabs[] = ['DIV' => 'expert', 'TAB' => Loc::getMessage('DUMP_MAIN_PARAMETERS'), 'ICON' => 'main_user_edit', 'TITLE' => Loc::getMessage('DUMP_MAIN_EXPERT_SETTINGS')];
$aTabs[] = ['DIV' => 'journal', 'TAB' => Loc::getMessage('DUMP_MAIN_JOURNAL'), 'ICON' => 'main_user_edit', 'TITLE' => Loc::getMessage('DUMP_MAIN_JOURNAL_TITLE')];

$editTab = new CAdminTabControl("editTab", $aTabs, true, true);

$editTab->Begin();
$editTab->BeginNextTab();
?>

    <tr class="heading">
        <td colspan="2"><?=GetMessage("MAIN_DUMP_SCHEDULE")?></td>
    </tr>
    <tr>
        <td style="width: 50%"><?= Loc::getMessage('MAIN_DUMP_TIME_CREATE_BACKUP')?></td>
        <td style="width: 50%"><input name="time_to_copy" type="time" val="05:45"></td>
    </tr>
    <tr>
        <td style="width: 50%"><?= Loc::getMessage('MAIN_DUMP_PERIOD')?></td>
        <td style="width: 50%">
            <select name="dump_auto_interval">
                <option value="1"><?= Loc::getMessage('MAIN_DUMP_EVERY_DAY')?></option>
                <option value="2"><?= Loc::getMessage('MAIN_DUMP_AFTER_DAY')?></option>
                <option value="3"><?= Loc::getMessage('MAIN_DUMP_EVERY_3_DAY')?></option>
                <option value="7" selected=""><?= Loc::getMessage('MAIN_DUMP_EVERY_WEEK')?></option>
            </select>
        </td>
    </tr>

    <tr class="heading">
        <td colspan="2"><?=GetMessage("MAIN_DUMP_DELETE_OLD")?></td>
    </tr>

    <tr>
        <td style="width: 50%"><?= Loc::getMessage('MAIN_DUMP_IS_DELETE_OLD_COPY')?></td>
        <td style="width: 50%"><input name="is_delete_old_copy" type="checkbox" checked disabled></td>
    </tr>

<?php
$editTab->BeginNextTab();
?>

    <span class="success_message_backup" style="color: green"></span><br><br>

<?php
if ($DB->type == 'MYSQL') {
    ?>
    <tr>
        <td><?= Loc::getMessage('DUMP_MAIN_ARC_DATABASE') ?> <span id="db_size">(<a
                        href="javascript:getTableSize()">?</a> <?= Loc::getMessage('MAIN_DUMP_BASE_SIZE') ?>)</span>:
        </td>
        <td><input type="checkbox" name="dump_base"
                   OnClick="CheckActiveStart()" <?= IntOption("dump_base", 1) ? "checked" : "" ?>></td>
    </tr>
    <tr>
        <td class="adm-detail-valign-top"><?= Loc::getMessage('DUMP_MAIN_DB_EXCLUDE') ?></td>
        <td>
            <div><input type="checkbox"
                        name="dump_base_skip_stat" <?= IntOption('dump_base_skip_stat', 0) ? "checked" : "" ?>
                        id="dump_base_skip_stat"> <label
                        for="dump_base_skip_stat"><?= Loc::getMessage('MAIN_DUMP_BASE_STAT') ?></label> <span
                        id=db_stat_size></span></div>
            <div><input type="checkbox" name="dump_base_skip_search"
                        value="Y" <?= IntOption("dump_base_skip_search", 0) ? "checked" : "" ?>
                        id="dump_base_skip_search"> <label
                        for="dump_base_skip_search"><?= Loc::getMessage('MAIN_DUMP_BASE_SINDEX') ?></label> <span
                        id=db_search_size></span></div>
            <div><input type="checkbox" name="dump_base_skip_log"
                        value="Y"<?= IntOption("dump_base_skip_log", 0) ? "checked" : "" ?>
                        id="dump_base_skip_log"> <label
                        for="dump_base_skip_log"><?= Loc::getMessage('MAIN_DUMP_EVENT_LOG') ?></label> <span
                        id=db_event_size></span></div>
        </td>
    </tr>
    <?
}
?>
    <tr>
        <td><? echo Loc::getMessage('MAIN_DUMP_FILE_KERNEL') ?></td>
        <td><input type="checkbox" name="dump_file_kernel" value="Y"
                   OnClick="CheckActiveStart()" <?= IntOption("dump_file_kernel", 1) ? "checked" : '' ?>></td>
    </tr>
    <tr>
        <td><? echo Loc::getMessage('MAIN_DUMP_FILE_PUBLIC') ?></td>
        <td><input type="checkbox" name="dump_file_public" value="Y"
                   OnClick="CheckActiveStart()" <?= IntOption("dump_file_public", 1) ? "checked" : '' ?>></td>
    </tr>
    <tr>
        <td class="adm-detail-valign-top"><? echo Loc::getMessage('MAIN_DUMP_MASK') ?><span
                    class="required"><sup>1</sup></span>
        </td>
        <td>
            <input type="checkbox" name="skip_mask" value="Y" <?= IntOption('skip_mask', 0) ? " checked" : ''; ?>
                   onclick="CheckActiveStart()">
            <table id="skip_mask_table" cellspacing=0 cellpadding=0>
                <?
                $i = -1;

                $res = unserialize(COption::GetOptionString("main", "skip_mask_array"));
                $skip_mask_array = is_array($res) ? $res : array();

                foreach ($skip_mask_array as $mask) {
                    $i++;
                    echo
                        '<tr><td>
                <input type="text" name="arMask[]" id="mnu_FILES_' . $i . '" value="' . htmlspecialcharsbx($mask) . '" size=30>' .
                        '<input type="button" id="mnu_FILES_btn_' . $i . '" value="..." onclick="showMenu(this, \'' . $i . '\')">' .
                        '</tr>';
                }
                $i++;
                ?>
                <tr>
                    <td><input type="text" name="arMask[]" id="mnu_FILES_<?= $i ?>" size=30><input type="button"
                                                                                                   id="mnu_FILES_btn_<?= $i ?>"
                                                                                                   value="..."
                                                                                                   onclick="showMenu(this, '<?= $i ?>')">
                </tr>
            </table>
            <input type=button id="more_button" value="<?= Loc::getMessage('MAIN_DUMP_MORE') ?>" onclick="AddTableRow()">
        </td>
    </tr>
    <tr>
        <td><? echo Loc::getMessage('MAIN_DUMP_FILE_MAX_SIZE') ?></td>
        <td><input type="text" name="max_file_size" size="10"
                   value="<?= IntOption("dump_max_file_size", 0) ?>" <?= CBackup::CheckDumpFiles() ? '' : "disabled" ?>>
            <? echo Loc::getMessage('MAIN_DUMP_FILE_MAX_SIZE_kb') ?></td>
    </tr>

    <tr>
        <td width=40%><?= Loc::getMessage('INTEGRITY_CHECK_OPTION') ?></td>
        <td><input type="checkbox"
                   name="dump_integrity_check" <?= IntOption('dump_integrity_check', 1) ? 'checked' : '' ?>>
    </tr>
    <tr>
        <td><?= Loc::getMessage('DISABLE_GZIP') ?></td>
        <td><input type="checkbox"
                   name="dump_disable_gzip" <?= IntOption('dump_use_compression', 1) && $bGzip ? '' : 'checked' ?> <?= $bGzip ? '' : 'disabled' ?>>
    </tr>
    <tr>
        <td width=40%><?= Loc::getMessage('STEP_LIMIT') ?></td>
        <td>
            <input type="text" name="dump_max_exec_time" value="<?= IntOption("dump_max_exec_time", 20) ?>" size=2>
            <? echo Loc::getMessage('MAIN_DUMP_FILE_STEP_sec'); ?>,
            <? echo Loc::getMessage('MAIN_DUMP_FILE_STEP_SLEEP') ?>
            <input type="text" name="dump_max_exec_time_sleep"
                   value="<?= IntOption("dump_max_exec_time_sleep", 3) ?>" size=2>
            <? echo Loc::getMessage('MAIN_DUMP_FILE_STEP_sec'); ?>
        </td>
    </tr>

    <tr>
        <td><?= Loc::getMessage('MAIN_DUMP_MAX_ARCHIVE_SIZE') ?></td>
        <td><input type="text" name="dump_archive_size_limit"
                   value="<?= intval(COption::GetOptionString('main', 'dump_archive_size_limit', 100 * 1024 * 1024)) / 1024 / 1024 ?>"
                   size=4> <?= Loc::getMessage('MAIN_DUMP_MAX_ARCHIVE_SIZE_VALUES') ?><span
                    class="required"><sup>2</sup></span>
        </td>
    </tr>

<?php
$editTab->BeginNextTab();


/*$obBackup = new Backup();
$token = $obBackup->getToken();*/
?>

    <tr>
        <td>Журнал:</td>
    </tr>

<?php
$editTab->Buttons();
?>
    <input id="start_reserv_copy" type="button" value="Старт резервного копирования">
    <input id="start_copy_backup" type="button" value="Запустить перенос резервных копий"/>

<?php
$editTab->End();

echo BeginNote();
echo '<div><span class=required><sup>1</sup></span> ' . Loc::getMessage('MAIN_DUMP_FOOTER_MASK') . '</div>';
echo '<div><span class=required><sup>2</sup></span> ' . Loc::getMessage('MAIN_DUMP_MAX_ARCHIVE_SIZE_INFO') . '</div>';
echo '<div><span class=required><sup>3</sup></span> ' . Loc::getMessage('MAIN_DUMP_SHED_TIME_SET') . '</div>';
echo EndNote();
?>

<script>
    var bitrix_sesion_id = '<?=bitrix_sessid_get()?>';
</script>

<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';