<?php

namespace StrprofiBackupCloud;

/**
 * Класс для загрузки библиотек composer
 */
class PackageLoader
{
    public $dir;

    /**
     * @return mixed
     */
    public function getComposerFile()
    {
        return json_decode(file_get_contents($this->dir."/composer.json"), 1);
    }

    /**
     * @param string $dir
     */
    public function load(string $dir): void
    {
        $this->dir = $dir;

        $composer = $this->getComposerFile();
        if(isset($composer["autoload"]["psr-4"])){
            $this->loadPSR4($composer['autoload']['psr-4']);
        }
        if(isset($composer["autoload"]["psr-0"])){
            $this->loadPSR0($composer['autoload']['psr-0']);
        }
        if(isset($composer["autoload"]["files"])){
            $this->loadFiles($composer["autoload"]["files"]);
        }
    }

    /**
     * @param array $files
     */
    public function loadFiles(array $files): void
    {
        foreach($files as $file){
            $includeFiles = implode(',', get_included_files());

            $moduleDirTree = explode('/', $this->dir);
            $moduleDirName = $moduleDirTree[count($moduleDirTree) - 1];

            if (strpos($includeFiles, $moduleDirName . '/' . $file) === false) {
                $fullpath = $this->dir . "/" . $file;
                if (file_exists($fullpath)) {
                    include_once($fullpath);
                }
            }
        }
    }

    /**
     * @param array $namespaces
     */
    public function loadPSR4(array $namespaces): void
    {
        $this->loadPSR($namespaces, true);
    }

    /**
     * @param array $namespaces
     */
    public function loadPSR0(array $namespaces): void
    {
        $this->loadPSR($namespaces, false);
    }

    /**
     * @param array $namespaces
     * @param bool $psr4
     */
    public function loadPSR(array $namespaces, bool $psr4): void
    {
        $dir = $this->dir;

        // Foreach namespace specified in the composer, load the given classes
        foreach ($namespaces as $namespace => $classpaths) {
            $classpaths = trim($classpaths, '/');

            if (!is_array($classpaths)) {
                $classpaths = array($classpaths);
            }

            spl_autoload_register(function ($classname) use ($namespace, $classpaths, $dir, $psr4) {
                // Check if the namespace matches the class we are looking for
                if (preg_match("#^".preg_quote($namespace)."#", $classname)) {
                    // Remove the namespace from the file path since it's psr4
                    if ($psr4) {
                        $classname = str_replace($namespace, "", $classname);
                    }
                    $filename = preg_replace("#\\\\#", "/", $classname).".php";

                    foreach ($classpaths as $classpath) {
                        $fullpath = $this->dir."/".$classpath."/$filename";

                        if (file_exists($fullpath)) {
                            include_once $fullpath;
                        }
                    }
                }
            });
        }
    }
}