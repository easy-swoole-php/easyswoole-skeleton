<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Config as RedisConfig;

trait SerializeTrait
{
    /**
     * 序列化处理value
     *
     * @param     $value
     * @param int $serializeType
     *
     * @return string
     */
    public static function serialize($value, int $serializeType)
    {
        switch ($serializeType) {
            case RedisConfig::SERIALIZE_PHP:
                return serialize($value);
            case RedisConfig::SERIALIZE_JSON:
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            case RedisConfig::SERIALIZE_NONE:
            default:
                return $value;

        }
    }

    /**
     * 反序列化value
     *
     * @param string $value
     * @param int    $serializeType
     *
     * @return mixed|string
     */
    public static function unSerialize(string $value, int $serializeType)
    {
        switch ($serializeType) {
            case RedisConfig::SERIALIZE_PHP:
            {
                $res = unserialize($value);
                return $res !== false ? $res : $value;
            }
            case RedisConfig::SERIALIZE_JSON:
            {
                $res = json_decode($value, true);
                return $res !== null ? $res : $value;
            }
            case RedisConfig::SERIALIZE_NONE:
            default:
            {
                return $value;
            }
        }
    }
}
