<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait SetTrait
{
    /**
     * 向集合添加一个成员
     * Adds a values to the set value stored at key.
     *
     * @param string       $key            Required key
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|bool The number of elements added to the set.
     * If this value is already in the set, FALSE is returned
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sadd
     * @example
     * <pre>
     * RedisUtil::sAdd('k', 'v1'); // int(1)
     * </pre>
     */
    public static function sAdd(
        string $key,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sAdd($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "SADD {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 向集合添加多个成员
     * Adds a values to the set value stored at key.
     *
     * @param string   $key            Required key
     * @param array    $values         Variadic list of values
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool The number of elements added to the set.
     * If this value is already in the set, FALSE is returned
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sadd
     * @example
     * <pre>
     * RedisUtil::sAdds('k', ['v1', 'v2', 'v3']); // int(3)
     * </pre>
     */
    public static function sAdds(
        string $key,
        array  $values,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理values
        $data = [];
        foreach ($values as $value) {
            $data[] = self::serialize($value, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $data, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sAdd($key, ...$data);
        }, $connectionName);

        // handle exception
        $valuesStrArr = [];
        foreach ($data as $item) {
            $valuesStrArr[] = is_numeric($item) ? $item : "\"" . addslashes($item) . "\"";
        }
        $command = "SADD {$key} " . join(' ', $valuesStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取集合的成员数
     * Returns the cardinality of the set identified by key.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the cardinality of the set identified by key, 0 if the set doesn't exist.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/scard
     * @example
     * <pre>
     * RedisUtil::sAdd('key1', 'set1');
     * RedisUtil::sAdd('key1', 'set2');
     * RedisUtil::sAdd('key1', 'set3');   // 'key1' => {'set1', 'set2', 'set3'}
     * RedisUtil::sCard('key1');          // 3
     * RedisUtil::sCard('keyX');          // 0
     * </pre>
     */
    public static function sCard(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sCard($key);
        }, $connectionName);

        // handle exception
        $command = "SCARD {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定2个集合的差集
     * Performs the difference between N sets and returns it.
     *
     * @param string   $key1           first key for diff
     * @param string   $otherKey       key corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array string[] The difference of the first set will all the others
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sdiff
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s0', '4');
     *
     * RedisUtil::sAdd('s1', '1');
     *
     * var_dump(RedisUtil::sDiff('s0', 's1'));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sDiff(
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sDiff($key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SDIFF {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定所有集合的差集
     * Performs the difference between N sets and returns it.
     *
     * @param string   $key1           first key for diff
     * @param array    $otherKeys      variadic list of keys corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array string[] The difference of the first set will all the others
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sdiff
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s0', '3');
     * RedisUtil::sAdd('s0', '4');
     *
     * RedisUtil::sAdd('s1', '1');
     * RedisUtil::sAdd('s2', '3');
     *
     * var_dump(RedisUtil::sDiffs('s0', ['s1', 's2']));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sDiffs(
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sDiff($key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SDIFF {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定2个集合的差集并存储在 dstKey 中
     * Performs the same action as sDiff, but stores the result in the first key
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           first key for diff
     * @param string   $otherKey       key corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false The cardinality of the resulting set, or FALSE in case of a missing key
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sdiffstore
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s0', '4');
     *
     * RedisUtil::sAdd('s1', '1');
     *
     * var_dump(RedisUtil::sDiffStore('dst', 's0', 's1'));
     * var_dump(RedisUtil::sMembers('dst'));
     *
     * //int(2)
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sDiffStore(
        string $dstKey,
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sDiffStore($dstKey, $key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SDIFFSTORE {$dstKey} {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定所有集合的差集并存储在 dstKey 中
     * Performs the same action as sDiff, but stores the result in the first key
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           first key for diff
     * @param array    $otherKeys      variadic list of keys corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false The cardinality of the resulting set, or FALSE in case of a missing key
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sdiffstore
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s0', '3');
     * RedisUtil::sAdd('s0', '4');
     *
     * RedisUtil::sAdd('s1', '1');
     *
     * var_dump(RedisUtil::sDiffStores('dst', 's0', ['s1', 's2']));
     * var_dump(RedisUtil::sMembers('dst'));
     *
     * //int(2)
     * //array(2) {
     * //  [0]=>
     * //  string(1) "4"
     * //  [1]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sDiffStores(
        string $dstKey,
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);

        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sDiffStore($dstKey, $key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SDIFFSTORE {$dstKey} {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定2个集合的交集
     * Returns the members of a set resulting from the intersection of all the sets
     * held at the specified keys. If just a single key is specified, then this command
     * produces the members of this set. If one of the keys is missing, FALSE is returned.
     *
     * @param string   $key1           keys identifying the different sets on which we will apply the intersection.
     * @param string   $otherKey       key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array|false contain the result of the intersection between those keys
     * If the intersection between the different sets is empty, the return value will be empty array.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sinter
     * @example
     * <pre>
     * RedisUtil::sAdd('key1', 'val1');
     * RedisUtil::sAdd('key1', 'val2');
     * RedisUtil::sAdd('key1', 'val3');
     * RedisUtil::sAdd('key1', 'val4');
     *
     * RedisUtil::sAdd('key2', 'val3');
     * RedisUtil::sAdd('key2', 'val4');
     *
     * var_dump(RedisUtil::sInter('key1', 'key2'));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public static function sInter(
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sInter($key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SINTER {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定所有集合的交集
     * Returns the members of a set resulting from the intersection of all the sets
     * held at the specified keys. If just a single key is specified, then this command
     * produces the members of this set. If one of the keys is missing, FALSE is returned.
     *
     * @param string   $key1           keys identifying the different sets on which we will apply the intersection.
     * @param array    $otherKeys      variadic list of keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array|false contain the result of the intersection between those keys
     * If the intersection between the different sets is empty, the return value will be empty array.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sinter
     * @example
     * <pre>
     * RedisUtil::sAdd('key1', 'val1');
     * RedisUtil::sAdd('key1', 'val2');
     * RedisUtil::sAdd('key1', 'val3');
     * RedisUtil::sAdd('key1', 'val4');
     *
     * RedisUtil::sAdd('key2', 'val3');
     * RedisUtil::sAdd('key2', 'val4');
     *
     * RedisUtil::sAdd('key3', 'val3');
     * RedisUtil::sAdd('key3', 'val4');
     *
     * var_dump(RedisUtil::sInters('key1', ['key2', 'key3']));
     *
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public static function sInters(
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sInter($key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SINTER {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定2个集合的交集并存储在 dstKey 中
     * Performs a sInter command and stores the result in a new set.
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           keys identifying the different sets on which we will apply the intersection.
     * @param string   $otherKey       key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false The cardinality of the resulting set, or FALSE in case of a missing key
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sinterstore
     * @example
     * <pre>
     * RedisUtil::sAdd('key1', 'val1');
     * RedisUtil::sAdd('key1', 'val2');
     * RedisUtil::sAdd('key1', 'val3');
     * RedisUtil::sAdd('key1', 'val4');
     *
     * RedisUtil::sAdd('key2', 'val3');
     * RedisUtil::sAdd('key2', 'val4');
     *
     * var_dump(RedisUtil::sInterStore('output', 'key1', 'key2'));
     * var_dump(RedisUtil::sMembers('output'));
     *
     * //int(2)
     * //
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public static function sInterStore(
        string $dstKey,
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sInterStore($dstKey, $key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SINTERSTORE {$dstKey} {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回给定所有集合的交集并存储在 dstKey 中
     * Performs a sInter command and stores the result in a new set.
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           keys identifying the different sets on which we will apply the intersection.
     * @param array    $otherKeys      variadic list of keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false The cardinality of the resulting set, or FALSE in case of a missing key
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sinterstore
     * @example
     * <pre>
     * RedisUtil::sAdd('key1', 'val1');
     * RedisUtil::sAdd('key1', 'val2');
     * RedisUtil::sAdd('key1', 'val3');
     * RedisUtil::sAdd('key1', 'val4');
     *
     * RedisUtil::sAdd('key2', 'val3');
     * RedisUtil::sAdd('key2', 'val4');
     *
     * RedisUtil::sAdd('key3', 'val3');
     * RedisUtil::sAdd('key3', 'val4');
     *
     * var_dump(RedisUtil::sInterStores('output', 'key1', ['key2', 'key3']));
     * var_dump(RedisUtil::sMembers('output'));
     *
     * //int(2)
     * //
     * //array(2) {
     * //  [0]=>
     * //  string(4) "val4"
     * //  [1]=>
     * //  string(4) "val3"
     * //}
     * </pre>
     */
    public static function sInterStores(
        string $dstKey,
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);

        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sInterStore($dstKey, $key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SINTERSTORE {$dstKey} {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 判断 value 元素是否是集合 key 的成员
     * Checks if value is a member of the set stored at the key key.
     *
     * @param string       $key
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool TRUE if value is a member of the set at key key, FALSE otherwise
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sismember
     * @example
     * <pre>
     * RedisUtil::sAdd('key1' , 'set1');
     * RedisUtil::sAdd('key1' , 'set2');
     * RedisUtil::sAdd('key1' , 'set3'); // 'key1' => {'set1', 'set2', 'set3'}
     *
     * RedisUtil::sIsMember('key1', 'set1'); // TRUE
     * RedisUtil::sIsMember('key1', 'setX'); // FALSE
     * </pre>
     */
    public static function sIsMember(
        string $key,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理value
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sIsMember($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"{$val}\"";
        $command = "SISMEMBER {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        if ($result === 1) {
            return true;
        }

        if ($result === 0) {
            return false;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 返回集合中的所有成员
     * Returns the contents of a set.
     *
     * @param string   $key
     * @param int      $serializeType
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array An array of elements, the contents of the set
     *
     * @throws Exception\RedisException
     *
     * @link    https://redis.io/commands/smembers
     * @example
     * <pre>
     * RedisUtil::del('s');
     * RedisUtil::sAdd('s', 'a');
     * RedisUtil::sAdd('s', 'b');
     * RedisUtil::sAdd('s', 'a');
     * RedisUtil::sAdd('s', 'c');
     * var_dump(RedisUtil::sMembers('s'));
     *
     * //array(3) {
     * //  [0]=>
     * //  string(1) "c"
     * //  [1]=>
     * //  string(1) "a"
     * //  [2]=>
     * //  string(1) "b"
     * //}
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function sMembers(
        string $key,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sMembers($key);
        }, $connectionName);

        // handle exception
        $command = "SMEMBERS {$key}";
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result) {
            foreach ($result as $item) {
                $lastResult[] = self::unSerialize($item, $serializeType);
            }
        }

        return $lastResult;
    }

    /**
     * 将 member 元素从 srcKey 集合移动到 dstKey 集合
     * Moves the specified member from the set at srcKey to the set at dstKey.
     *
     * @param string       $srcKey
     * @param string       $dstKey
     * @param string|mixed $member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool If the operation is successful, return TRUE.
     * If the srcKey and/or dstKey didn't exist, and/or the member didn't exist in srcKey, FALSE is returned.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/smove
     * @example
     * <pre>
     * RedisUtil::sAdd('key1' , 'set11');
     * RedisUtil::sAdd('key1' , 'set12');
     * RedisUtil::sAdd('key1' , 'set13');          // 'key1' => {'set11', 'set12', 'set13'}
     * RedisUtil::sAdd('key2' , 'set21');
     * RedisUtil::sAdd('key2' , 'set22');          // 'key2' => {'set21', 'set22'}
     * RedisUtil::sMove('key1', 'key2', 'set13');  // 'key1' =>  {'set11', 'set12'}
     *                                             // 'key2' =>  {'set21', 'set22', 'set13'}
     * </pre>
     */
    public static function sMove(
        string $srcKey,
        string $dstKey,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($srcKey, $connectionName);
        self::handleKeyPrefix($dstKey, $connectionName);

        // 序列化处理member
        $aMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($srcKey, $dstKey, $aMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sMove($srcKey, $dstKey, $aMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($aMember) ? $aMember : "\"" . addslashes($aMember) . "\"";
        $command   = "SMOVE {$srcKey} {$dstKey} {$memberStr}";
        self::handleException($command, $connectionName, $result);

        if ($result === 1) {
            return true;
        }

        if ($result === 0) {
            return false;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 移除并返回集合中的一个或者多个随机元素
     * Removes and returns a random element from the set value at Key.
     *
     * @param string   $key
     * @param int      $count          [optional]
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|mixed|array|bool "popped" values
     * bool FALSE if set identified by key is empty or doesn't exist.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/spop
     * @example
     * <pre>
     * RedisUtil::sAdd('key1' , 'set1');
     * RedisUtil::sAdd('key1' , 'set2');
     * RedisUtil::sAdd('key1' , 'set3');   // 'key1' => {'set3', 'set1', 'set2'}
     * RedisUtil::sPop('key1');            // 'set1', 'key1' => {'set3', 'set2'}
     * RedisUtil::sPop('key1');            // 'set3', 'key1' => {'set2'}
     *
     * // With count
     * RedisUtil::sAdd('key2', 'set1', 'set2', 'set3');
     * var_dump( RedisUtil::sPop('key2', 3) ); // Will return all members but in no particular order
     *
     * // array(3) {
     * //   [0]=> string(4) "set2"
     * //   [1]=> string(4) "set3"
     * //   [2]=> string(4) "set1"
     * // }
     * </pre>
     */
    public static function sPop(
        string $key,
        int    $count = 1,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $count, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sPop($key, $count);
        }, $connectionName);

        // handle exception
        $command = "SPOP {$key}";
        if ($count !== 1) {
            $command .= " {$count}";
        }
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        // 反序列化处理
        if ($result) {
            if ($count === 1) {
                $result = self::unSerialize($result, $serializeType);
            } else {
                $lastResult = [];
                foreach ($result as $item) {
                    $lastResult[] = self::unSerialize($item, $serializeType);
                }
                $result = $lastResult;
            }
        }

        if (is_null($result) || (is_array($result) && empty($result))) {
            $result = false;
        }

        return $result;
    }

    /**
     * 返回集合中一个或多个随机数
     * Returns a random element(s) from the set value at Key, without removing it.
     *
     * @param string   $key
     * @param int      $count          [optional]
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|mixed|array|bool value(s) from the set
     * bool FALSE if set identified by key is empty or doesn't exist and count argument isn't passed.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/srandmember
     * @example
     * <pre>
     * RedisUtil::sAdd('key1' , 'one');
     * RedisUtil::sAdd('key1' , 'two');
     * RedisUtil::sAdd('key1' , 'three');              // 'key1' => {'one', 'two', 'three'}
     *
     * var_dump( RedisUtil::sRandMember('key1') );     // 'key1' => {'one', 'two', 'three'}
     *
     * // string(5) "three"
     *
     * var_dump( RedisUtil::sRandMember('key1', 2) );  // 'key1' => {'one', 'two', 'three'}
     *
     * // array(2) {
     * //   [0]=> string(2) "one"
     * //   [1]=> string(5) "three"
     * // }
     * </pre>
     */
    public static function sRandMember(
        string $key,
        int    $count = 1,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $count, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sRandMember($key, $count);
        }, $connectionName);

        // handle exception
        $command = "SRANDMEMBER {$key}";
        if ($count !== 1) {
            $command .= " {$count}";
        }
        self::handleException($command, $connectionName, $result);

        // 反序列化处理
        if ($result) {
            $lastResult = [];
            foreach ($result as $item) {
                $lastResult[] = self::unSerialize($item, $serializeType);
            }
            $result = $lastResult;
        }

        if ($count === 1) {
            if (!empty($result) && is_array($result)) {
                $result = array_pop($result);
            } else {
                $result = false;
            }
        }

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 移除集合中一个成员
     * Removes the specified members from the set value stored at key.
     *
     * @param string       $key
     * @param string|mixed $member1        a member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int The number of elements removed from the set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/srem
     * @example
     * <pre>
     * var_dump( RedisUtil::sAdd('k', 'v1', 'v2', 'v3') );    // int(3)
     * var_dump( RedisUtil::sRem('k', 'v2', 'v3') );          // int(2)
     * var_dump( RedisUtil::sMembers('k') );
     * //// Output:
     * // array(1) {
     * //   [0]=> string(2) "v1"
     * // }
     * </pre>
     */
    public static function sRem(
        string $key,
               $member1,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT

    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member1
        $member = self::serialize($member1, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $member, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sRem($key, $member);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($member) ? $member : "\"" . addslashes($member) . "\"";
        $command   = "SREM {$key} {$memberStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移除集合中一个或多个成员
     * Removes the specified members from the set value stored at key.
     *
     * @param string   $key
     * @param array    $members        Variadic list of members
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int The number of elements removed from the set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/srem
     * @example
     * <pre>
     * var_dump( RedisUtil::sAdd('k', 'v1', 'v2', 'v3') );    // int(3)
     * var_dump( RedisUtil::sRems('k', ['v2', 'v3']) );          // int(2)
     * var_dump( RedisUtil::sMembers('k') );
     * //// Output:
     * // array(1) {
     * //   [0]=> string(2) "v1"
     * // }
     * </pre>
     */
    public static function sRems(
        string $key,
        array  $members,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member1
        $waitMembers = [];
        foreach ($members as $member) {
            $waitMembers[] = self::serialize($member, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMembers, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sRem($key, ...$waitMembers);
        }, $connectionName);

        // handle exception
        $membersStrArr = [];
        foreach ($waitMembers as $item) {
            $membersStrArr[] = "\"" . addslashes($item) . "\"";
        }
        $command = "SREM {$key} " . join(' ', $membersStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回2个给定集合的并集
     * Performs the union between N sets and returns it.
     *
     * @param string   $key1           first key for union
     * @param string   $otherKey       key corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array string[] The union of all these sets
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sunion
     * @example
     * <pre>
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s1', '3');
     * RedisUtil::sAdd('s1', '1');
     *
     * var_dump(RedisUtil::sUnion('s0', 's1'));
     *
     * array(3) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "1"
     * //  [2]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sUnion(
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sUnion($key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SUNION {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回所有给定集合的并集
     * Performs the union between N sets and returns it.
     *
     * @param string   $key1           first key for union
     * @param array    $otherKeys      variadic list of keys corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array string[] The union of all these sets
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sunion
     * @example
     * <pre>
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s1', '3');
     * RedisUtil::sAdd('s1', '1');
     * RedisUtil::sAdd('s2', '3');
     * RedisUtil::sAdd('s2', '4');
     *
     * var_dump($redis->sUnions('s0', ['s1', 's2']));
     *
     * array(4) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "4"
     * //  [2]=>
     * //  string(1) "1"
     * //  [3]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sUnions(
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key1, $connectionName);
        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sUnion($key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SUNION {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 2个给定集合的并集存储在 dstKey 集合中
     * Performs the same action as sUnion, but stores the result in the first key
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           first key for union
     * @param string   $otherKey       key corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Any number of keys corresponding to sets in redis
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sunionstore
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s1', '3');
     * RedisUtil::sAdd('s1', '1');
     *
     * var_dump(RedisUtil::sUnionStore('dst', 's0', 's1'));
     * var_dump(RedisUtil::sMembers('dst'));
     *
     * //int(3)
     * //array(3) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "1"
     * //  [2]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sUnIonStore(
        string $dstKey,
        string $key1,
        string $otherKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);
        self::handleKeyPrefix($otherKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $otherKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sUnIonStore($dstKey, $key1, $otherKey);
        }, $connectionName);

        // handle exception
        $command = "SUNIONSTORE {$dstKey} {$key1} {$otherKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 所有给定集合的并集存储在 destination 集合中
     * Performs the same action as sUnion, but stores the result in the first key
     *
     * @param string   $dstKey         the key to store the diff into.
     * @param string   $key1           first key for union
     * @param array    $otherKeys      variadic list of keys corresponding to sets in redis
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Any number of keys corresponding to sets in redis
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/sunionstore
     * @example
     * <pre>
     * RedisUtil::del('s0', 's1', 's2');
     *
     * RedisUtil::sAdd('s0', '1');
     * RedisUtil::sAdd('s0', '2');
     * RedisUtil::sAdd('s1', '3');
     * RedisUtil::sAdd('s1', '1');
     * RedisUtil::sAdd('s2', '3');
     * RedisUtil::sAdd('s2', '4');
     *
     * var_dump(RedisUtil::sUnionStores('dst', 's0', ['s1', 's2']));
     * var_dump(RedisUtil::sMembers('dst'));
     *
     * //int(4)
     * //array(4) {
     * //  [0]=>
     * //  string(1) "3"
     * //  [1]=>
     * //  string(1) "4"
     * //  [2]=>
     * //  string(1) "1"
     * //  [3]=>
     * //  string(1) "2"
     * //}
     * </pre>
     */
    public static function sUnIonStores(
        string $dstKey,
        string $key1,
        array  $otherKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($dstKey, $connectionName);
        self::handleKeyPrefix($key1, $connectionName);

        $waitOtherKeys = [];
        foreach ($otherKeys as $otherKey) {
            $waitOtherKeys[] = self::handleKeyPrefix($otherKey, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($dstKey, $key1, $waitOtherKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->sUnIonStore($dstKey, $key1, ...$waitOtherKeys);
        }, $connectionName);

        // handle exception
        $command = "SUNIONSTORE {$dstKey} {$key1} " . join(' ', $waitOtherKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
