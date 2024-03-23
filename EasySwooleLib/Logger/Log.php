<?php

namespace EasySwooleLib\Logger;

use EasySwoole\EasySwoole\Logger;

/**
 * Class Log
 *
 * @package App\Utility\Log
 * @method static void debug(?string $msg, string $category = 'debug');
 * @method static void info(?string $msg, string $category = 'info');
 * @method static void notice(?string $msg, string $category = 'notice');
 * @method static void waring(?string $msg, string $category = 'waring');
 * @method static void error(?string $msg, string $category = 'error');
 */
class Log
{
    public static function __callStatic($name, $arguments)
    {
        $logger = Logger::getInstance();
        $logger->$name(...$arguments);
    }
}
