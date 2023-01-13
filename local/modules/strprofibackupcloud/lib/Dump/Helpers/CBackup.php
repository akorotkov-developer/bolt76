<?php

namespace StrprofiBackupCloud\Dump\Helpers;

use CModule;
use CCloudStorageBucket;
use COption;
use CTar;
use Exception;
use CDBResult;

class CBackup
{
    static $DOCUMENT_ROOT_SITE;
    static $REAL_DOCUMENT_ROOT_SITE;

    protected $strLastFile;
    protected $LastFileSize;

    public static function CheckDumpClouds()
    {
        $arRes = array();
        if (IntOption('dump_do_clouds') && $arAllBucket = CBackup::GetBucketList())
        {
            foreach($arAllBucket as $arBucket)
                if (IntOption('dump_cloud_'.$arBucket['ID']))
                    $arRes[] = $arBucket['ID'];
            if (count($arRes))
                return $arRes;
        }
        return false;
    }

    public static function CheckDumpFiles()
    {
        return IntOption("dump_file_public") || IntOption("dump_file_kernel");
    }

    public static function GetBucketList($arFilter = array())
    {
        if (CModule::IncludeModule('clouds'))
        {
            $arBucket = array();
            $rsData = CCloudStorageBucket::GetList(
                array("SORT"=>"DESC", "ID"=>"ASC"),
                array_merge(array('ACTIVE'=>'Y','READ_ONLY'=>'N'), $arFilter)
            );
            while($f = $rsData->Fetch())
            {
                $arBucket[] = $f;
            }
            return count($arBucket) ? $arBucket : false;
        }
        return false;
    }

    public static function ignorePath($path, $skipArr = false)
    {
        if (!file_exists($path)) // in case of wrong symlinks
            return true;

        if (!self::$REAL_DOCUMENT_ROOT_SITE)
            self::$REAL_DOCUMENT_ROOT_SITE = realpath(self::$DOCUMENT_ROOT_SITE);

        ## Ignore paths
        static $ignore_path;
        if (!$ignore_path)
            $ignore_path = array(
                BX_PERSONAL_ROOT."/cache",
                BX_PERSONAL_ROOT."/cache_image",
                BX_PERSONAL_ROOT."/managed_cache",
                BX_PERSONAL_ROOT."/managed_flags",
                BX_PERSONAL_ROOT."/stack_cache",
                BX_PERSONAL_ROOT."/html_pages",
                BX_PERSONAL_ROOT."/tmp",
                BX_ROOT."/tmp",
                BX_ROOT."/help",
                BX_ROOT."/updates",
                '/'.COption::GetOptionString("main", "upload_dir", "upload")."/tmp",
                '/'.COption::GetOptionString("main", "upload_dir", "upload")."/resize_cache",
            );

        foreach($ignore_path as $value)
            if(self::$DOCUMENT_ROOT_SITE.$value == $path)
                return true;

        ## Clouds
        if (IntOption('dump_do_clouds'))
        {
            $clouds = self::$DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/clouds/';
            if (strpos($path, $clouds) === 0 || strpos($clouds, $path) === 0)
                return false;
        }

        ## Backups
        if (strpos($path, self::$DOCUMENT_ROOT_SITE.BX_ROOT.'/backup/') === 0)
            return true;

        ## Symlinks
        if (is_dir($path))
        {
            if (is_link($path))
            {
                if (strpos(realpath($path), self::$REAL_DOCUMENT_ROOT_SITE) !== false) // если симлинк ведет на папку внутри структуры сайта
                    return true;
            }
        } ## File size
        elseif (($max_file_size = IntOption("dump_max_file_size")) > 0 && filesize($path) > $max_file_size * 1024)
            return true;

        ## Skip mask
        if (CBackup::skipMask($path, $skipArr))
            return true;

        ## Kernel vs Public
        $dump_file_public = IntOption('dump_file_public');
        $dump_file_kernel = IntOption('dump_file_kernel');

        if ($dump_file_public == $dump_file_kernel) // если обе опции либо включены либо выключены
            return !$dump_file_public;

        if (strpos(self::$DOCUMENT_ROOT_SITE.BX_ROOT, $path) !== false) // на пути к /bitrix
            return false;

        if (strpos($path, self::$DOCUMENT_ROOT_SITE.BX_ROOT) === false) // за пределами /bitrix
            return !$dump_file_public;

        $path_root = substr($path, strlen(self::$DOCUMENT_ROOT_SITE));
        if (preg_match('#^/bitrix/(.settings.php|php_interface|templates)/([^/]*)#',$path_root.'/',$regs))
            return !$dump_file_public;

        if (preg_match('#^/bitrix/(activities|components|gadgets|wizards)/([^/]*)#',$path_root.'/',$regs))
        {
            if (!$regs[2])
                return false;
            if ($regs[2] == 'bitrix')
                return !$dump_file_kernel;
            return !$dump_file_public;
        }

        // всё остальное в папке bitrix - ядро
        return !$dump_file_kernel;
    }

    public static function GetBucketFileList($BUCKET_ID, $path)
    {
        static $CACHE;

        if ($CACHE[$BUCKET_ID])
            $obBucket = $CACHE[$BUCKET_ID];
        else
            $CACHE[$BUCKET_ID] = $obBucket = new CCloudStorageBucket($BUCKET_ID);

        if ($obBucket->Init())
            return $obBucket->ListFiles($path);
        return false;
    }

    public static function _preg_escape($str)
    {
        $search = array('#','[',']','.','?','(',')','^','$','|','{','}');
        $replace = array('\#','\[','\]','\.','\?','\(','\)','\^','\$','\|','\{','\}');
        return str_replace($search, $replace, $str);
    }

    public static function skipMask($abs_path, $skipArr = false)
    {
        if (!IntOption('skip_mask'))
            return false;

        if ($skipArr) {
            $skip_mask_array = $skipArr;
        } else {
            global $skip_mask_array;
        }

        $path = substr($abs_path,strlen(self::$DOCUMENT_ROOT_SITE));
        $path = str_replace('\\','/',$path);

        static $preg_mask_array;
        if (!$preg_mask_array)
        {
            $preg_mask_array = array();
            foreach($skip_mask_array as $a)
                $preg_mask_array[] = CBackup::_preg_escape($a);
        }

        reset($skip_mask_array);
        foreach($skip_mask_array as $k => $mask)
        {
            if (strpos($mask,'/')===0) // absolute path
            {
                if (strpos($mask,'*') === false) // нет звездочки
                {
                    if (strpos($path.'/',$mask.'/') === 0)
                        return true;
                }
                elseif (preg_match('#^'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
                    return true;
            }
            elseif (strpos($mask, '/')===false)
            {
                if (strpos($mask,'*')===false)
                {
                    if (substr($path,-strlen($mask)) == $mask)
                        return true;
                }
                elseif (preg_match('#/[^/]*'.str_replace('*','[^/]*?',$preg_mask_array[$k]).'$#i',$path))
                    return true;
            }
        }
    }

    public static function GetArcName($prefix = '')
    {
        $arc_name = DOCUMENT_ROOT.BX_ROOT."/backup/".$prefix.date("Ymd_His");

        $k = IntOption('dump_file_kernel');
        $p = IntOption('dump_file_public');
        $b = IntOption('dump_base');

        if ($k && $p && $b)
            $arc_name .= '_full';
        elseif (!($p xor $b))
            $arc_name .= '_'.($k ? '' : 'no').'core';
        elseif (!($k xor $b))
            $arc_name .= '_'.($p ? '' : 'no').'pub';
        elseif (!($k xor $p))
            $arc_name .= '_'.($b ? '' : 'no').'sql';

        $arc_name .= '_'.substr(md5(uniqid(rand(), true)), 0, 8);
        return $arc_name;
    }

    public static function MakeDump($strDumpFile, &$arState)
    {
        global $DB;

        $B = new CBackup;

        if (!$arState)
        {
            if(!$B->file_put_contents_ex($strDumpFile, "-- Started: ".date('Y-m-d H:i:s')."\n"))
                return false;

            $rs = $DB->Query('SHOW VARIABLES LIKE "character_set_results"');
            if (($f = $rs->Fetch()) && array_key_exists ('Value', $f))
                if (!$B->file_put_contents_ex($strDumpFile, "SET NAMES '".$f['Value']."';\n"))
                    return false;

            $arState = array('TABLES' => array());
            $arTables = array();
            $rsTables = $DB->Query("SHOW FULL TABLES WHERE TABLE_TYPE NOT LIKE 'VIEW'", false, '', array("fixed_connection"=>true));
            while($arTable = $rsTables->Fetch())
            {
                list($key, $table) = each($arTable);

                $rsIndexes = $DB->Query("SHOW INDEX FROM `".$DB->ForSql($table)."`", true, '', array("fixed_connection"=>true));
                if($rsIndexes)
                {
                    $arIndexes = array();
                    while($ar = $rsIndexes->Fetch())
                        if($ar["Non_unique"] == "0")
                            $arIndexes[$ar["Key_name"]][$ar["Seq_in_index"]-1] = $ar["Column_name"];

                    foreach($arIndexes as $IndexName => $arIndexColumns)
                        if(count($arIndexColumns) != 1)
                            unset($arIndexes[$IndexName]);

                    if(count($arIndexes) > 0)
                    {
                        foreach($arIndexes as $IndexName => $arIndexColumns)
                        {
                            foreach($arIndexColumns as $SeqInIndex => $ColumnName)
                                $key_column = $ColumnName;
                            break;
                        }
                    }
                    else
                    {
                        $key_column = false;
                    }
                }
                else
                {
                    $key_column = false;
                }

                $arState['TABLES'][$table] = array(
                    "TABLE_NAME" => $table,
                    "KEY_COLUMN" => $key_column,
                    "LAST_ID" => 0
                );
            }
            $rsTables = $DB->Query("SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW'", false, '', array("fixed_connection"=>true));
            while($arTable = $rsTables->Fetch())
            {
                list($key, $table) = each($arTable);

                $arState['TABLES'][$table] = array(
                    "TABLE_NAME" => $table,
                    "KEY_COLUMN" => false,
                    "LAST_ID" => 0
                );
            }
            $arState['TableCount'] = count($arState['TABLES']);
            if (!haveTime())
                return true;
        }

        foreach($arState['TABLES'] as $table => $arTable)
        {
            if(!$arTable["LAST_ID"])
            {
                $rs = $DB->Query("SHOW CREATE TABLE `".$DB->ForSQL($table)."`", true);
                if ($rs === false)
                    throw new Exception(GetMessage('DUMP_TABLE_BROKEN', array('#TABLE#' => $table)));

                $row = $rs->Fetch();
                $string = $row['Create Table'];
                if (!$string) // VIEW
                {
                    $string = $row['Create View'];
                    if (!$B->file_put_contents_ex($strDumpFile,
                        "-- -----------------------------------\n".
                        "-- Creating view ".$DB->ForSQL($table)."\n".
                        "-- -----------------------------------\n".
                        "DROP VIEW IF EXISTS `".$DB->ForSQL($table)."`;\n".
                        $string.";\n\n"))
                        return false;
                    unset($arState['TABLES'][$table]);
                    continue;
                }
                elseif (CBackup::SkipTableData($table))
                {
                    $string = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $string);
                    if (!$B->file_put_contents_ex($strDumpFile,
                        "-- -----------------------------------\n".
                        "-- Creating empty table ".$DB->ForSQL($table)."\n".
                        "-- -----------------------------------\n".
                        $string.";\n\n"))
                        return false;
                    unset($arState['TABLES'][$table]);
                    continue;
                }


                if (!$B->file_put_contents_ex($strDumpFile,
                    "-- -----------------------------------\n".
                    "-- Dumping table ".$DB->ForSQL($table)."\n".
                    "-- -----------------------------------\n".
                    "DROP TABLE IF EXISTS `".$DB->ForSQL($table)."`;\n".
                    $string.";\n\n"))
                    return false;

                $arState['TABLES'][$table]['COLUMNS'] = $arTable["COLUMNS"] = CBackup::GetTableColumns($table);
                if (($k = $arTable['KEY_COLUMN']) && $arTable['COLUMNS'][$k] > 0) // check if promary key is not numeric
                {
                    unset($arTable['KEY_COLUMN']);
                    unset($arState['TABLES'][$table]['KEY_COLUMN']);
                }
            }

            $strInsert = "";
            $cnt = $LIMIT = 10000;
            while($cnt == $LIMIT)
            {
                $i = $arTable['LAST_ID'];
                if($arTable["KEY_COLUMN"])
                {
                    $strSelect = "
						SELECT *
						FROM `".$arTable["TABLE_NAME"]."`
						".($arTable["LAST_ID"] ? "WHERE `".$arTable["KEY_COLUMN"]."` > '".$arTable["LAST_ID"]."'": "")."
						ORDER BY `".$arTable["KEY_COLUMN"]."`
						LIMIT ".$LIMIT;
                }
                else
                {
                    $strSelect = "
						SELECT *
						FROM `".$arTable["TABLE_NAME"]."`
						LIMIT ".($arTable["LAST_ID"] ? $arTable["LAST_ID"].", ": "").$LIMIT;
                }

                if (!$rsSource = self::QueryUnbuffered($strSelect))
                    throw new Exception('SQL Query Error');
                while($arSource = $rsSource->Fetch())
                {
                    if(!$strInsert)
                        $strInsert = "INSERT INTO `".$arTable["TABLE_NAME"]."` VALUES";
                    else
                        $strInsert .= ",";

                    foreach($arSource as $key => $value)
                    {
                        if(!isset($value) || is_null($value))
                            $arSource[$key] = 'NULL';
                        elseif($arTable["COLUMNS"][$key] == 0)
                            $arSource[$key] = $value;
                        elseif($arTable["COLUMNS"][$key] == 1)
                        {
                            if(empty($value) && $value != '0')
                                $arSource[$key] = '\'\'';
                            else
                                $arSource[$key] = '0x' . bin2hex($value);
                        }
                        elseif($arTable["COLUMNS"][$key] == 2)
                        {
                            $arSource[$key] = "'".$DB->ForSql($value)."'";
                        }
                    }

                    $strInsert .= "\n(".implode(", ", $arSource).")";

                    $arState['TABLES'][$table]['LAST_ID'] = $arTable['LAST_ID'] = $arTable["KEY_COLUMN"] ? $arSource[$arTable["KEY_COLUMN"]] : ++$i;

                    if (CTar::strlen($strInsert) > 1000000)
                    {
                        if(!$B->file_put_contents_ex($strDumpFile, $strInsert.";\n"))
                            return false;
                        $strInsert = "";
                    }

                    if (!haveTime())
                    {
                        self::FreeResult();
                        return $strInsert ? $B->file_put_contents_ex($strDumpFile, $strInsert.";\n") : true;
                    }
                }
                $cnt = $rsSource->SelectedRowsCount();
                self::FreeResult();
            }

            if($strInsert && !$B->file_put_contents_ex($strDumpFile, $strInsert.";\n"))
                return false;

            if ($cnt < $LIMIT)
                unset($arState['TABLES'][$table]);
        }

        if(!$B->file_put_contents_ex($strDumpFile, "-- Finished: ".date('Y-m-d H:i:s')))
            return false;

        $arState['end'] = true;
        return true;
    }

    public function QueryUnbuffered($q)
    {
        global $DB;
        if (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI === true)
            $DB->result = mysqli_query($DB->db_Conn, $q, MYSQLI_USE_RESULT);
        else
            $DB->result = mysql_unbuffered_query($q, $DB->db_Conn);
        $rsSource = new CDBResult($DB->result);
        $rsSource->DB = $DB;
        return $rsSource;
    }

    public function FreeResult()
    {
        global $DB;
        if (defined('BX_USE_MYSQLI') && BX_USE_MYSQLI === true)
            mysqli_free_result($DB->result);
        else
            mysql_free_result($DB->result);
    }

    public function file_put_contents_ex($strDumpFile, $str)
    {
        $LIMIT = 2000000000;
        if (!$this->strLastFile)
        {
            $this->strLastFile = $strNextFile = $strDumpFile;
            $this->LastFileSize = 0;
            while(file_exists($strNextFile))
            {
                $this->LastFileSize = filesize($this->strLastFile = $strNextFile);
                $strNextFile = self::getNextName($strNextFile);
            }
        }

        $c = CTar::strlen($str);
        if ($this->LastFileSize + $c >= $LIMIT)
        {
            $this->strLastFile = self::getNextName($this->strLastFile);
            $this->LastFileSize = 0;
        }
        $this->LastFileSize += $c;
        return file_put_contents($this->strLastFile, $str, 8);
    }

    public static function GetTableColumns($TableName)
    {
        global $DB;
        $arResult = array();

        $sql = "SHOW COLUMNS FROM `".$TableName."`";
        $res = $DB->Query($sql, false, '', array("fixed_connection"=>true));
        while($row = $res->Fetch())
        {
            if(preg_match("/^(\w*int|year|float|double|decimal)/", $row["Type"]))
                $arResult[$row["Field"]] = 0;
            elseif(preg_match("/^(\w*(binary|blob))/", $row["Type"]))
                $arResult[$row["Field"]] = 1;
            else
                $arResult[$row["Field"]] = 2;
        }

        return $arResult;
    }

    public static function SkipTableData($table)
    {
        $table = strtolower($table);
        if (preg_match("#^b_stat#", $table) && IntOption('dump_base_skip_stat'))
            return true;
        elseif (preg_match("#^b_search_#", $table) && !preg_match('#^(b_search_custom_rank|b_search_phrase)$#', $table) && IntOption('dump_base_skip_search'))
            return true;
        elseif($table == 'b_event_log' && IntOption('dump_base_skip_log'))
            return true;
        return false;
    }

    public static function getNextName($file)
    {
        static $CACHE;
        $c = &$CACHE[$file];

        if (!$c)
        {
            $l = strrpos($file, '.');
            $num = CTar::substr($file,$l+1);
            if (is_numeric($num))
                $file = CTar::substr($file,0,$l+1).++$num;
            else
                $file .= '.1';
            $c = $file;
        }
        return $c;
    }
}