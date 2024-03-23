<?php

namespace EasySwooleLib\Context;

use EasySwoole\Component\Context\ContextManager;

class ContextUtil
{
    public static function set(string $key, $value, int $cid = null)
    {
        ContextManager::getInstance()->set($key, $value, $cid);
    }

    public static function get(string $key, int $cid = null)
    {
        return ContextManager::getInstance()->get($key, $cid);
    }
}
