<?php

namespace StrprofiBackupCloud\Dump\Helpers;

class CDirScan {
    var $DirCount = 0;
    var $FileCount = 0;
    var $err= array();

    var $bFound = false;
    var $nextPath = '';
    var $startPath = '';
    var $arIncludeDir = false;

    function __construct()
    {
    }

    function ProcessDirBefore($f)
    {
        return true;
    }

    function ProcessDirAfter($f)
    {
        return true;
    }

    function ProcessFile($f)
    {
        return true;
    }

    function Skip($f)
    {
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
        return false;
    }

    function Scan($dir, $skipArr = false)
    {
        $dir = str_replace('\\','/',$dir);

        if ($this->Skip($dir, $skipArr))
        {
            // echo $dir."<br>\n";
            return;
        }

        $this->nextPath = $dir;

        if (is_dir($dir))
        {
            #############################
            # DIR
            #############################
            if (!$this->startPath) // если начальный путь найден или не задан
            {
                $r = $this->ProcessDirBefore($dir);

                if ($r === false) {
                    return false;
                }
            }

            if (!($handle = opendir($dir)))
            {
                $this->err[] = 'Error opening dir: '.$dir;
                return false;
            }

            while (($item = readdir($handle)) !== false)
            {
                if ($item == '.' || $item == '..' || false !== strpos($item,'\\'))
                    continue;

                $f = $dir."/".$item;
                $r = $this->Scan($f, $skipArr);
                if ($r === false || $r === 'BREAK')
                {
                    closedir($handle);
                    return $r;
                }
            }
            closedir($handle);

            if (!$this->startPath) // если начальный путь найден или не задан
            {
                if ($this->ProcessDirAfter($dir) === false) {
                    return false;
                }
                $this->DirCount++;
            }
        }
        else
        {
            #############################
            # FILE
            #############################
            $r = $this->ProcessFile($dir);
            if ($r === false) {
                return false;
            }
            elseif ($r === 'BREAK') { // если файл обработан частично
                return $r;
            }
            $this->FileCount++;
        }
        return true;
    }
}