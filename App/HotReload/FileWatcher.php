<?php

namespace App\HotReload;

use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\ServerManager;

class FileWatcher
{
    public static function onChange()
    {
        Logger::getInstance()->info('file change ,reload!!!');
        ServerManager::getInstance()->getSwooleServer()->reload();
    }
}
