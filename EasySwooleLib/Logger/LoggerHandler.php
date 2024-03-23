<?php

namespace EasySwooleLib\Logger;

use EasySwoole\Log\LoggerInterface;
use EasySwoole\Utility\File;

class LoggerHandler implements LoggerInterface
{
    private $logDir;

    public function __construct(string $logDir = null)
    {
        if (empty($logDir)) {
            $logDir = EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Log';

            if (!is_dir($logDir)) {
                File::createDirectory($logDir);
            }
        }

        $this->logDir = $logDir;
    }

    public function log(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'): string
    {
        $prefix   = date('Ymd');
        $date     = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $year     = date('Y');
        $month    = date('m');

        $logDir = $this->logDir . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;

        if (!is_dir($logDir)) {
            File::createDirectory($logDir);
        }

        $filePath = $logDir . "/log_{$prefix}.log";
        $str      = "[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
        file_put_contents($filePath, "{$str}", FILE_APPEND | LOCK_EX);
        return $str;
    }

    public function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'console')
    {
        $date     = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        echo "[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
    }

    private function levelMap(int $level)
    {
        switch ($level) {
            case self::LOG_LEVEL_DEBUG:
                return 'debug';
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}
