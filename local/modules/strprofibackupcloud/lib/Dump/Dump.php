<?php

namespace StrprofiBackupCloud\Dump;

use CBackup;
use Exception;
use Bitrix\Main\Localization\Loc;
use COption;
use CSite;
use CTar;
use CDiskQuota;
use CloudDownload;
use StrprofiBackupCloud\Dump\Helpers\CDirRealScan;
use CTarCheck;
use CFile;
use StrprofiBackupCloud\Option as ModuleOption;
use CArchiver;

class Dump
{
    private $skip_mask_array;
    private CArchiver $arch;

    public function CreateDump2($params, $dbParams)
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];

        // Определяем кодировку БД
        $charset = (BX_UTF) ? 'utf8' : 'cp1251';
        system('
            dbconn=' . $documentRoot . '/bitrix/php_interface/dbconn.php; 
            echo "$dbconn";
            
            host=' . $params['HOST'] . ';
            username=' . $params['LOGIN'] . ';
            password=' . $params['PASSWORD'] . ';
            database=' . $params['DB_NAME'] . ';
            
            doc_root=' . $documentRoot . ';
            charset=' . $charset . ';
            backup_dir=bitrix/backup;
            nameSql=mysqldump;
            
            cd $doc_root && mysqldump --host=$host --user=$username --password=$password --default-character-set=$charset $database > $backup_dir/$nameSql.sql
        ', $output);










        //$this->dumpFiles();

        //$this->dumpDB($dbParams);
    }

    private function dumpFiles()
    {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/tar_gz.php');

        $backup_dir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/backup/strprofi';
        $dumpSqlName = 'dump.sql';
        $siteBackupName = 'sitedump';

        $this->arch = new CArchiver($backup_dir . '/' . $siteBackupName . '.tar.gz', true);

        $this->arch->AddFile("$backup_dir/$dumpSqlName",'', $_SERVER['DOCUMENT_ROOT']);
        $this->Store($_SERVER['DOCUMENT_ROOT'] . '/about');

        echo '<pre>';
        var_dump('Здесь');
        echo '</pre>';

        // unlink("$backup_dir/$dumpSqlName");
    }

    private function Store($path)
    {
       $path = str_replace('\\','/',$path);
       if (preg_match('#^' . $_SERVER['DOCUMENT_ROOT'].'/bitrix/backup#', $path) ||
          preg_match('#^' . $_SERVER['DOCUMENT_ROOT'].'/bitrix/[^/]*cache/#', $path)) {
           return;
       }

       $this->arch->AddFile($path,'',$_SERVER['DOCUMENT_ROOT']);
       if (is_dir($path))
       {
          $dir = opendir($path);
          while(false !== $file=readdir($dir))
          {
             if ($file=='.' || $file=='..') {
                 continue;
             }

             self::Store($path . '/' . $file);
          }
          closedir($dir);
       }
    }

    /**
     * Дамп базы данных
     */
    private function dumpDB($dbParams)
    {
        $pathToBackup = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/backup/strprofi/';

        if (!file_exists($pathToBackup)) {
            mkdir($pathToBackup, 0777, true);
        }
        exec('mysqldump --user=' . $dbParams['LOGIN'] . ' --password=' . $dbParams['PASSWORD'] . ' --host=' . $dbParams['HOST'] . ' ' . $dbParams['DB_NAME'] . ' > ' . $pathToBackup . 'dump.sql');

        echo 'Закончился дамп';
    }

    /**
     * Создание резервной копии сайта
     * @param $params
     * @throws Exception
     */
    public function createDump($params, $arrSkipMask = false)
    {
        global $DB;
        define('NO_TIME', true);

        // Уберем ограничение времени выполнения скрипта
        set_time_limit(0);
        // Отключаем прерывание скрипта при отключении клиента
        ignore_user_abort(true);

        if (!defined("START_EXEC_TIME")) {
            define("START_EXEC_TIME", microtime(true));
        }

        $this->skip_mask_array = $arrSkipMask;

        require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ModuleOption::ADMIN_MODULE_NAME . '/src/php/initoption.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ModuleOption::ADMIN_MODULE_NAME . '/src/php/havetime.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ModuleOption::ADMIN_MODULE_NAME . '/src/php/worktime.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . ModuleOption::ADMIN_MODULE_NAME . '/src/php/raiseerroranddie.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/backup.php');

        $GLOBALS['tar'] = new CTar();
        $tar = new CTar;

        $strBXError = '';
        $bGzip = function_exists('gzcompress');
        $bMcrypt = function_exists('mcrypt_encrypt') || function_exists('openssl_encrypt');
        $bHash = function_exists('hash');

        if (function_exists('mb_internal_encoding'))
            mb_internal_encoding('ISO-8859-1');

        define('DOCUMENT_ROOT', rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/'));

        $arAllBucket = CBackup::GetBucketList();
        $status_title = '';

        $NS =& $_SESSION['BX_DUMP_STATE'];

        if ($params['action'] == 'start') {
            /*define('NO_TIME', true);*/

            $bFull = $params['dump_all'] == 'Y';

            if (!bitrix_sessid()) {
                throw new Exception(Loc::getMessage("DUMP_MAIN_SESISON_ERROR"));
            }

            if(!file_exists(DOCUMENT_ROOT.BX_ROOT . '/backup')) {
                mkdir(DOCUMENT_ROOT . BX_ROOT . '/backup', BX_DIR_PERMISSIONS);
            }

            if(!is_dir(DOCUMENT_ROOT.BX_ROOT . '/backup') || !is_writable(DOCUMENT_ROOT.BX_ROOT . '/backup')) {
                throw new Exception(Loc::getMessage('MAIN_DUMP_FOLDER_ERR', ['#FOLDER#' => DOCUMENT_ROOT . BX_ROOT . '/backup']));
            }

            $NS = [];
            $NS['finished_steps'] = 0;
            $NS['dump_state'] = '';
            $NS['BUCKET_ID'] = intval($params['dump_bucket_id']);
            COption::SetOptionInt('main', 'dump_bucket_id', $NS['BUCKET_ID']);
            COption::SetOptionInt('main', 'dump_encrypt', 0);

            $bUseCompression = $bGzip && ($params['dump_disable_gzip'] != 'Y' || $bFull);
            COption::SetOptionInt('main', 'dump_use_compression', $bUseCompression);

            if ($bFull)
            {
                $NS['total_steps'] = 4; // dump, tar dump, tar files, integrity

                COption::SetOptionInt('main', 'dump_max_exec_time', 20);
                COption::SetOptionInt('main', 'dump_max_exec_time_sleep', 1);
                COption::SetOptionInt('main', 'dump_archive_size_limit', 100 * 1024 * 1024);
                COption::SetOptionInt('main', 'dump_integrity_check', 1);
                COption::SetOptionInt('main', 'dump_max_file_size', 0);

                COption::SetOptionInt('main', 'dump_file_public', 1);
                COption::SetOptionInt('main', 'dump_file_kernel', 1);
                COption::SetOptionInt('main', 'dump_base', $DB->type == 'MYSQL' ? 1 : 0);
                COption::SetOptionInt('main', 'dump_base_skip_stat', 0);
                COption::SetOptionInt('main', 'dump_base_skip_search', 0);
                COption::SetOptionInt('main', 'dump_base_skip_log', 0);

                if ($arAllBucket)
                {
                    $bDumpCloud = 1;
                    $NS['total_steps']++;
                    COption::SetOptionInt('main', 'dump_do_clouds', 1);
                    foreach($arAllBucket as $arBucket)
                        COption::SetOptionInt('main', 'dump_cloud_'.$arBucket['ID'], 1);
                }

                COption::SetOptionInt('main', 'skip_mask', 0);
            }
            else
            {
                COption::SetOptionInt('main', 'dump_max_exec_time', intval($params['dump_max_exec_time']) < 5 ? 5 : $params['dump_max_exec_time']);
                COption::SetOptionInt('main', 'dump_max_exec_time_sleep', $params['dump_max_exec_time_sleep']);
                $dump_archive_size_limit = intval($params['dump_archive_size_limit']);
                if ($dump_archive_size_limit > 2047 || $dump_archive_size_limit <= 10)
                    $dump_archive_size_limit = 100;
                COption::SetOptionInt('main', 'dump_archive_size_limit', $dump_archive_size_limit * 1024 * 1024);
                COption::SetOptionInt('main', 'dump_max_file_size', $params['max_file_size']);

                $NS['total_steps'] = 0;
                if ($r = $params['dump_file_public'] == 'Y')
                    $NS['total_steps'] = 1;
                COption::SetOptionInt('main', 'dump_file_public', $r);

                if ($r = $params['dump_file_kernel'] == 'Y')
                    $NS['total_steps'] = 1;

                COption::SetOptionInt('main', 'dump_file_kernel', $r);

                if ($r = $DB->type == 'MYSQL' ? ($params['dump_base'] == 'Y') : 0)
                    $NS['total_steps'] += 2;
                COption::SetOptionInt('main', 'dump_base', $r);
                COption::SetOptionInt('main', 'dump_base_skip_stat', $params['dump_base_skip_stat'] == 'Y');
                COption::SetOptionInt('main', 'dump_base_skip_search', $params['dump_base_skip_search'] == 'Y');
                COption::SetOptionInt('main', 'dump_base_skip_log', $params['dump_base_skip_log'] == 'Y');

                if ($r = $params['dump_integrity_check'] == 'Y')
                    $NS['total_steps']++;
                COption::SetOptionInt('main', 'dump_integrity_check', $r);

                $bDumpCloud = false;
                if ($arAllBucket)
                {
                    foreach($arAllBucket as $arBucket)
                    {
                        if ($res = $params['dump_cloud'][$arBucket['ID']] == 'Y')
                            $bDumpCloud = true;
                        COption::SetOptionInt('main', 'dump_cloud_'.$arBucket['ID'], $res);
                    }
                    if ($bDumpCloud)
                        $NS['total_steps']++;
                }
                COption::SetOptionInt('main', 'dump_do_clouds', $bDumpCloud);

                $skip_mask = $params['skip_mask'] == 'Y';
                COption::SetOptionInt('main', 'skip_mask', $skip_mask);

                $skip_mask_array = array();
                if ($skip_mask && is_array($params['arMask']))
                {
                    $arMask = array_unique($params['arMask']);
                    foreach($arMask as $mask)
                        if (trim($mask))
                        {
                            $mask = rtrim(str_replace('\\','/',trim($mask)),'/');
                            $skip_mask_array[] = $mask;
                        }

                    $this->skip_mask_array = $skip_mask_array;
                    COption::SetOptionString('main', 'skip_mask_array', serialize($skip_mask_array));
                }
            }

            $NS['step'] = 1;

            if ($NS['BUCKET_ID']) // send to the [bitrix]cloud
                $NS['total_steps']++;

            $NS['dump_site_id'] = $params['dump_site_id'];
            if (!is_array($NS['dump_site_id']))
                $NS['dump_site_id'] = array();
            COption::SetOptionString('main', 'dump_site_id', serialize($NS['dump_site_id']));

            if ($NS['BUCKET_ID'] == -1) // Bitrixcloud
            {
                $name = DOCUMENT_ROOT.BX_ROOT.'/backup/'.date('Ymd_His_').rand(11111111,99999999);
                $NS['arc_name'] = $name.'.enc'.($bUseCompression ? ".gz" : '');
                $NS['dump_name'] = $name.'.sql';
            }
            else
            {
                $prefix = '';
                if (count($NS['dump_site_id']) == 1)
                {
                    $rs = CSite::GetList($by='sort', $order='asc', array('ID' => $NS['dump_site_id'][0], 'ACTIVE' => 'Y'));
                    if ($f = $rs->Fetch())
                        $prefix = str_replace('/', '', $f['SERVER_NAME']);
                }
                else
                    $prefix = str_replace('/', '', COption::GetOptionString("main", "server_name", ""));

                $arc_name = CBackup::GetArcName(preg_match('#^[a-z0-9\.\-]+$#i', $prefix) ? substr($prefix, 0, 20).'_' : '');
                $NS['dump_name'] = $arc_name.".sql";
                $NS['arc_name'] = $arc_name.($NS['dump_encrypt_key'] ? ".enc" : ".tar").($bUseCompression ? ".gz" : '');
            }

            $params = $this->getProcessParams();
            $this->createDump($params, $this->skip_mask_array);
        } else {
            $ar = unserialize(COption::GetOptionString("main","skip_mask_array"));
            $skip_mask_array = is_array($ar) ? $ar : [];
        }

        $after_file = str_replace('.sql','_after_connect.sql',preg_replace('#\.[0-9]+$#', '', $NS['dump_name']));

        $FinishedTables = 0;

        // Step 1: Dump
        if($NS['step'] == 1)
        {
            $step_done = 0;
            if (IntOption('dump_base'))
            {
                if (!CBackup::MakeDump($NS['dump_name'], $NS['dump_state'])) {
                    throw new Exception(Loc::getMessage('DUMP_NO_PERMS'));
                }

                $TotalTables = $NS['dump_state']['TableCount'];
                $FinishedTables = $TotalTables - count($NS['dump_state']['TABLES']);

                $status_title = Loc::getMessage('DUMP_DB_CREATE');
                $status_details = Loc::getMessage('MAIN_DUMP_TABLE_FINISH')." <b>".(intval($FinishedTables))."</b> ".Loc::getMessage('MAIN_DUMP_FROM').' <b>'.$TotalTables.'</b>';
                $step_done = $FinishedTables / $TotalTables;

                if ($NS['dump_state']['end'])
                {
                    $rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
                    if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
                        file_put_contents($after_file, "SET NAMES '".$f['Value']."';\n");

                    $rs = $DB->Query('SHOW VARIABLES LIKE "collation_database"');
                    if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
                        file_put_contents($after_file, "ALTER DATABASE `<DATABASE>` COLLATE ".$f['Value'].";\n",8);

                    clearstatcache();
                    $NS['step']++;
                    $NS['finished_steps']++;
                }
            } else {
                $NS['step']++;
            }
        }

        // Step 2: pack dump
        if($NS["step"] == 2)
        {
            $step_done = 0;
            if (IntOption('dump_base'))
            {
                if (haveTime())
                {
                    $tar->EncryptKey = $NS['dump_encrypt_key'];
                    $tar->ArchiveSizeLimit = IntOption('dump_archive_size_limit');
                    $tar->gzip = IntOption('dump_use_compression');
                    $tar->path = DOCUMENT_ROOT;
                    $tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);

                    if (!$tar->openWrite($NS["arc_name"])) {
                        throw new Exception(Loc::getMessage('DUMP_NO_PERMS'));
                    }

                    if (!$tar->ReadBlockCurrent && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php'))
                        $tar->addFile($f);

                    $Block = $tar->Block;
                    $r = null;
                    while(haveTime() && ($r = $tar->addFile($NS['dump_name'])) && $tar->ReadBlockCurrent > 0);
                    $NS["data_size"] += 512 * ($tar->Block - $Block);

                    if ($r === false) {
                        throw new Exception(implode('<br>', $tar->err));
                    }

                    $NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;

                    if (!$NS['dump_size'])
                    {
                        $next_part = $NS['dump_name'];
                        $NS['dump_size'] = filesize($next_part);
                        while(file_exists($next_part = CBackup::getNextName($next_part)))
                            $NS['dump_size'] += filesize($next_part);
                    }

                    $status_title = Loc::getMessage("MAIN_DUMP_DB_PROC");
                    $status_details = Loc::getMessage('CURRENT_POS').' <b>'.round(100 * $NS['data_size'] / $NS['dump_size']).'%</b>';
                    $step_done = $NS['data_size'] / $NS['dump_size'];

                    if($tar->ReadBlockCurrent == 0)
                    {
                        unlink($NS["dump_name"]);

                        if (file_exists($next_part = CBackup::getNextName($NS['dump_name'])))
                        {
                            $NS['dump_name'] = $next_part;
                        }
                        else
                        {
                            if (file_exists($after_file))
                            {
                                $tar->addFile($after_file);
                                unlink($after_file);
                            }

                            $NS['arc_size'] = 0;
                            $name = $NS["arc_name"];
                            while(file_exists($name))
                            {
                                $size = filesize($name);
                                $NS['arc_size'] += $size;
                                if (IntOption("disk_space") > 0) {
                                    CDiskQuota::updateDiskQuota("file", $size, "add");
                                }
                                $name = CTar::getNextName($name);
                            }

                            $NS["step"]++;
                            $NS['finished_steps']++;
                        }
                    }
                    $tar->close();
                } else {
                    sleep(2);
                    $params = $this->getProcessParams();
                    $this->createDump($params, $this->skip_mask_array);
                }
            } else {
                $NS["step"]++;
            }
        }

        // Step 3: Download Cloud Files
        $arDumpClouds = false;
        if($NS['step'] == 3)
        {
            $step_done = 0;
            if ($arDumpClouds = CBackup::CheckDumpClouds())
            {
                if (haveTime())
                {
                    $res = null;
                    foreach($arDumpClouds as $id)
                    {
                        if ($NS['bucket_finished_'.$id]) {
                            continue;
                        }

                        $obCloud = new CloudDownload($id);
                        $obCloud->last_bucket_path = $NS['last_bucket_path'];
                        if ($res = $obCloud->Scan(''))
                        {
                            $NS['bucket_finished_'.$id] = true;
                        }
                        else // partial
                        {
                            $NS['last_bucket_path'] = $obCloud->path;
                            $NS['download_cnt'] += $obCloud->download_cnt;
                            $NS['download_size'] += $obCloud->download_size;
                            if ($c = count($obCloud->arSkipped)) {
                                $NS['download_skipped'] += $c;
                            }
                            break;
                        }
                    }

                    $status_title = Loc::getMessage("MAIN_DUMP_CLOUDS_DOWNLOAD");
                    $status_details = Loc::getMessage("MAIN_DUMP_FILES_DOWNLOADED").': <b>'.intval($NS["download_cnt"]).'</b>';
//				if ($NS['download_skipped'])
//					$status_title .= GetMessage("MAIN_DUMP_DOWN_ERR_CNT").': <b>'.$NS['download_skipped'].'</b><br>';

                    if ($res) // finish
                    {
                        $NS['step']++;
                        $NS['finished_steps']++;
                    }
                } else {
                    sleep(2);
                    $params = $this->getProcessParams();
                    $this->createDump($params, $this->skip_mask_array);
                }
            } else {
                $NS["step"]++;
            }
        }

        // Step 4: Tar Files
        if($NS['step'] == 4)
        {
            $step_done = 0;
            if (CBackup::CheckDumpFiles())
            {
                if (haveTime())
                {
                    $DirScan = new CDirRealScan;

                    $DOCUMENT_ROOT_SITE = DOCUMENT_ROOT;
                    if (is_array($NS['dump_site_id']))
                    {
                        $SITE_ID = reset($NS['dump_site_id']);
                        $rs = CSite::GetList($by='sort', $order='asc', array('ID' => $SITE_ID, 'ACTIVE' => 'Y'));
                        if ($f = $rs->Fetch())
                        {
                            $DOCUMENT_ROOT_SITE = rtrim(str_replace('\\','/',$f['ABS_DOC_ROOT']),'/');
                            if ($NS['multisite'])
                            {
                                $tar->prefix = 'bitrix/backup/sites/'.$f['LID'].'/';
                                $DirScan->arSkip[$DOCUMENT_ROOT_SITE.'/bitrix'] = true;
                                $DirScan->arSkip[$DOCUMENT_ROOT_SITE.'/upload'] = true;
                            }
                        }
                    }

                    CBackup::$DOCUMENT_ROOT_SITE = $DOCUMENT_ROOT_SITE;
                    CBackup::$REAL_DOCUMENT_ROOT_SITE = realpath($DOCUMENT_ROOT_SITE);

                    $tar->EncryptKey = $NS['dump_encrypt_key'];
                    $tar->ArchiveSizeLimit = IntOption('dump_archive_size_limit');
                    $tar->gzip = IntOption('dump_use_compression');
                    $tar->path = $DOCUMENT_ROOT_SITE;
                    $tar->ReadBlockCurrent = intval($NS['ReadBlockCurrent']);
                    $tar->ReadFileSize = intval($NS['ReadFileSize']);

                    if (!$tar->openWrite($NS["arc_name"])) {
                        throw new Exception(Loc::getMessage('DUMP_NO_PERMS'));
                    }

                    $Block = $tar->Block;

                    if (!$NS['startPath'])
                    {
                        if (!IntOption('dump_base') && file_exists($f = DOCUMENT_ROOT.BX_ROOT.'/.config.php')) {
                            $tar->addFile($f);
                        }
                    } else {
                        $DirScan->startPath = $NS['startPath'];
                    }

                    $DirScan->setTar($tar);

                    $r = $DirScan->Scan($DOCUMENT_ROOT_SITE, $this->skip_mask_array);
                    $NS["data_size"] += 512 * ($tar->Block - $Block);
                    $tar->close();

                    if ($r === false) {
                        throw new Exception(implode('<br>', array_merge($tar->err, $DirScan->err)));
                    }

                    $NS["ReadBlockCurrent"] = $tar->ReadBlockCurrent;
                    $NS["ReadFileSize"] = $tar->ReadFileSize;
                    $NS["startPath"] = $DirScan->nextPath;
                    $NS["cnt"] += $DirScan->FileCount;

                    $status_title = Loc::getMessage("MAIN_DUMP_SITE_PROC");
                    $status_details = Loc::getMessage("MAIN_DUMP_FILE_CNT")." <b>".intval($NS["cnt"])."</b>";
                    $last_files_count = IntOption('last_files_count');
                    if (!$last_files_count)
                        $last_files_count = 200000;
                    $step_done = $NS['cnt'] / $last_files_count;
                    if ($step_done > 1)
                        $step_done = 1;

                    if ($r !== 'BREAK')
                    {
                        if (count($NS['dump_site_id']) > 1)
                        {
                            array_shift($NS['dump_site_id']);
                            $NS['multisite'] = true;
                            unset($NS['startPath']);
                        }
                        else // finish
                        {
                            $NS['arc_size'] = 0;
                            $name = $NS["arc_name"];
                            while(file_exists($name))
                            {
                                $size = filesize($name);
                                $NS['arc_size'] += $size;
                                if (IntOption("disk_space") > 0)
                                    CDiskQuota::updateDiskQuota("file", $size, "add");
                                $name = CTar::getNextName($name);
                            }
                            DeleteDirFilesEx(BX_ROOT.'/backup/clouds');
                            $NS["step"]++;
                            $NS['finished_steps']++;
                        }
                    }
                } else {
                    sleep(2);
                    $params = $this->getProcessParams();
                    $this->createDump($params, $this->skip_mask_array);
                }
            } else {
                $NS['step']++;
            }
        }

        // Step 5: Integrity check
        if($NS['step'] == 5)
        {
            $step_done = 0;
            if (IntOption('dump_integrity_check') || $NS['check_archive'])
            {
                if (haveTime())
                {
                    $tar = new CTarCheck;
                    $tar->EncryptKey = $NS['dump_encrypt_key'];

                    if (!$tar->openRead($NS["arc_name"])) {
                        throw new Exception(Loc::getMessage('DUMP_NO_PERMS_READ') . '<br>' . implode('<br>', $tar->err));
                    } else {
                        if(($Block = intval($NS['Block'])) && !$tar->SkipTo($Block)) {
                            throw new Exception(implode('<br>', $tar->err));
                        }
                        while(($r = $tar->extractFile()) && haveTime());

                        $NS["Block"] = $tar->Block;
                        $status_title = Loc::getMessage('INTEGRITY_CHECK');
                        $status_details = Loc::getMessage('CURRENT_POS').' <b>'.CFile::FormatSize($NS['Block'] * 512).'</b> '.Loc::getMessage('MAIN_DUMP_FROM').' <b>'.CFile::FormatSize($NS['data_size']).'</b>';
                        $step_done = $NS['Block'] * 512 / $NS['data_size'];

                        if ($r === false) {
                            throw new Exception(implode('<br>', $tar->err));
                        }

                        if ($r === 0)
                        {
                            $NS["step"]++;
                            $NS['finished_steps']++;
                        }
                    }
                    $tar->close();
                } else {
                    sleep(2);
                    $params = $this->getProcessParams();
                    $this->createDump($params, $this->skip_mask_array);
                }
            } else {
                $NS["step"]++;
            }
        }

        $NS["time"] += workTime();

        // Step 6: Send to the cloud
        if ($NS['step'] == 6) {
            if ($NS['BUCKET_ID']) {
                // Send to Cloud
            } else {
                $NS["step"]++;
            }
        }

        // partial
        if ($NS["step"] <= 6) // partial
        {
            $progressMessages = [
                'TYPE' => 'PROGRESS',
                'MESSAGE' => $status_title,
                'DETAILS' => $status_details .
                    Loc::getMessage('TIME_SPENT').' <span id="counter_field">'.sprintf('%02d',floor($NS["time"]/60)).':'.sprintf('%02d', $NS['time']%60).'</span><!--'.intval($NS['time']).'-->',
                'PROGRESS_TOTAL' => 100,
                'PROGRESS_VALUE' => ($NS['finished_steps'] + $step_done) * 100 / $NS['total_steps'],
            ];

            // рекурсивный вызов функции с параметрами $params заново
            sleep(2);
            $params = $this->getProcessParams();
            $this->createDump($params, $this->skip_mask_array);
        } else {
            $title = ($NS['cloud_send'] ? Loc::getMessage("MAIN_DUMP_SUCCESS_SENT") : Loc::getMessage("MAIN_DUMP_FILE_FINISH")).'<br><br>';
            $status_msg = '';

            if ($NS["arc_size"])
            {
                $status_msg .= Loc::getMessage("MAIN_DUMP_ARC_NAME").": <b>".basename(CTar::getFirstName($NS["arc_name"]))."</b><br>";
                $status_msg .= Loc::getMessage("MAIN_DUMP_ARC_SIZE")." <b>".CFile::FormatSize($NS["arc_size"])."</b><br>";
                if ($NS['BUCKET_ID'] > 0)
                    $l = ''; //htmlspecialcharsbx($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')');
                elseif ($NS['BUCKET_ID'] == -1)
                    $l = Loc::getMessage('DUMP_MAIN_BITRIX_CLOUD');
                else
                    $l = Loc::getMessage("MAIN_DUMP_LOCAL");

                if ($l)
                    $status_msg .= Loc::getMessage("MAIN_DUMP_LOCATION").": <b>".$l."</b><br>";
            }

            if ($FinishedTables)
                $status_msg .= Loc::getMessage("MAIN_DUMP_TABLE_FINISH")." <b>".$FinishedTables."</b><br>";
            if ($NS["cnt"])
            {
                $status_msg .= Loc::getMessage("MAIN_DUMP_FILE_CNT")." <b>".$NS["cnt"]."</b><br>";
                if (IntOption("dump_file_public") && IntOption("dump_file_kernel"))
                    COption::SetOptionInt("main", "last_files_count", $NS['cnt']);
            }

            if ($NS["data_size"])
                $status_msg .= GetMessage("MAIN_DUMP_FILE_SIZE")." <b>".CFile::FormatSize($NS["data_size"])."</b><br>";

            $status_msg .= GetMessage('TIME_SPENT').' <b>'.HumanTime($NS["time"]).'</b>';

            $progressMessages = [
                'MESSAGE' => $title,
                'DETAILS' => $status_msg,
                'TYPE' => 'OK',
                'HTML' => true
            ];

            // TODO здесь должен быть вызов функции переноса дампа на Яндекс.Диск
            // Yadisk->upload();
            \Bitrix\Main\Diag\Debug::dumpToFile(['$progressMessages_finish' => $progressMessages], '', 'log.txt');
        }
    }

    /**
     * Получить параметры для продолжения процесса
     * @return array
     */
    private function getProcessParams(): array
    {
        return [
            'process' => 'Y',
            'sessid' => bitrix_sessid()
        ];
    }
}