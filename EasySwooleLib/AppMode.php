<?php

namespace EasySwooleLib;

use EasySwoole\EasySwoole\Core;

class AppMode
{
    public const MODE_DEV     = 'dev';
    public const MODE_TEST    = 'test';
    public const MODE_UAT     = 'uat';
    public const MODE_PRODUCE = 'produce';

    public static function currentAppMode()
    {
        return Core::getInstance()->runMode();
    }

    public static function isDevMode()
    {
        return self::currentAppMode() === self::MODE_DEV;
    }

    public static function isTestMode()
    {
        return self::currentAppMode() === self::MODE_TEST;
    }

    public static function isUatMode()
    {
        return self::currentAppMode() === self::MODE_UAT;
    }

    public static function isProduceMode()
    {
        return self::currentAppMode() === self::MODE_PRODUCE;
    }
}
