<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwooleLib\Redis\Utility\Exception\ExceptionTrait;

class RedisUtil
{
    public const DEFAULT_CONNECT = 'default';

    use ExceptionTrait;
    use SerializeTrait;
    use ConnectTrait;
    use KeyTrait;
    use StringTrait;
    use HashTrait;
    use ListTrait;
    use SetTrait;
    use SortedSetTrait;


    /**
     * 处理key前缀
     *
     * @param        $key
     * @param string $connectionName
     *
     * @return mixed|string
     */
    public static function handleKeyPrefix(&$key, string $connectionName = self::DEFAULT_CONNECT)
    {
        $prefix = config("redis.{$connectionName}.prefix");
        $prefix = $prefix ?: '';
        return $prefix . $key;
    }
}
