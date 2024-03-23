<?php

namespace EasySwooleLib\Config;

use EasySwoole\EasySwoole\Config;
use EasySwoole\Utility\File;

class ConfigManager
{
    public static function loadConfig(string $mode)
    {
        $loadDir  = EASYSWOOLE_ROOT . '/Config/' . $mode;
        $fileDirs = File::scanDirectory(EASYSWOOLE_ROOT . '/Config');
        $files    = $fileDirs['files'];
        $dirs     = $fileDirs['dirs'];

        // load common config
        foreach ($files as $file) {
            if (strpos($file, $loadDir) !== false) {
                continue;
            }

            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (!file_exists($file)) {
                continue;
            }

            if (!$filename) {
                continue;
            }

            $config = require_once $file;

            if (!is_array($config)) {
                continue;
            }

            Config::getInstance()->merge([$filename => $config]);
        }

        // load mode config
        $fileDirs = File::scanDirectory($loadDir);
        foreach ($fileDirs['files'] as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);

            if (!file_exists($file)) {
                continue;
            }

            if (!$filename) {
                continue;
            }

            $config = require_once $file;

            if (!is_array($config)) {
                continue;
            }

            Config::getInstance()->merge([$filename => $config]);
        }
    }
}
