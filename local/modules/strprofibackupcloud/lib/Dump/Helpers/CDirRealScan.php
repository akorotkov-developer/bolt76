<?php

namespace StrprofiBackupCloud\Dump\Helpers;

use StrprofiBackupCloud\Dump\Helpers\CBackup;

class CDirRealScan extends CDirScan
{
    var $arSkip = array();
    private $tar;

    private $skipArr;

    function ProcessFile($f)
    {
        while(haveTime())
        {
            $f = str_replace('\\', '/', $f);
            if (preg_match('#/bitrix/(php_interface/dbconn.php|.settings.php)$#', $f, $regs))
            {
                if (!$arInfo = $this->tar->getFileInfo($f))
                    return false;

                if ($regs[1] == '.settings.php')
                {
                    if (!is_array($ar = include($f)))
                    {
                        $this->err[] = 'Can\'t parse file: '.$f;
                        return false;
                    }

                    if (is_array($ar['connections']['value']))
                    {
                        foreach($ar['connections']['value'] as $k => $arTmp)
                        {
                            $ar['connections']['value'][$k]['login'] = '******';
                            $ar['connections']['value'][$k]['password'] = '******';
                            $ar['connections']['value'][$k]['database'] = '******';
                        }
                    }

                    $strFile = "<"."?php\nreturn ".var_export($ar, true).";\n";
                }
                else // dbconn.php
                {
                    if (false === $arFile = file($f))
                    {
                        $this->err[] = 'Can\'t read file: '.$f;
                        return false;
                    }

                    $strFile = '';
                    foreach($arFile as $line)
                    {
                        if (preg_match("#^[ \t]*".'\$'."(DB(Login|Password|Name))#",$line,$regs))
                            $strFile .= '$'.$regs[1].' = "******";'."\n";
                        else
                            $strFile .= str_replace("\r\n","\n",$line);
                    }
                }

                $arInfo['size'] = CTar::strlen($strFile);
                if (!$this->tar->writeHeader($arInfo))
                    return false;

                $i = 0;
                while($i < $arInfo['size'])
                {
                    if (!$this->tar->writeBlock(pack("a512",CTar::substr($strFile,$i,512))))
                        return false;
                    $i += 512;
                }

                return true;
            }

            if ($this->tar->addFile($f) === false)
                return false; // error
            if ($this->tar->ReadBlockCurrent == 0)
                return true; // finished
        }
        return 'BREAK';
    }

    function ProcessDirBefore($f)
    {
        return $this->tar->addFile($f);
    }

    function Skip($f, $skipArr = false)
    {
        static $bFoundDocumentRoot;
        $res = false;
        if ($this->startPath)
        {
            if (strpos($this->startPath.'/', $f.'/') === 0)
            {
                if ($this->startPath == $f)
                    unset($this->startPath);
                return false;
            }
            else
                return true;
        }
        elseif ($this->arSkip[$f]) {
            return true;
        } elseif ($bFoundDocumentRoot) {
            $res = CBackup::ignorePath($f, $skipArr);
        }

        $bFoundDocumentRoot = true;
        return $res;
    }

    function setTar($tar)
    {
        $this->tar = $tar;
    }
}