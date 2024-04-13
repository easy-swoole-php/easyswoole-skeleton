<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Config as RedisConfig;
use EasySwooleLib\Redis\Utility\Exception\RedisException;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait HashTrait
{
    /**
     * Removes a values from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string   $key
     * @param string   $hashKey
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool Number of deleted fields
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hdel
     * @example
     * <pre>
     * RedisUtil::hMSet('h',
     *               array(
     *                    'f1' => 'v1',
     *                    'f2' => 'v2',
     *               ));
     *
     * var_dump( RedisUtil::hDel('h', 'f1') );        // int(1)
     * s
     * var_dump( RedisUtil::hGetAll('h') );
     * //// Output:
     * //  array(1) {
     * //    ["f2"]=> string(2) "v2"
     * //  }
     * </pre>
     */
    public static function hDel(
        string $key,
        string $hashKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hDel($key, $hashKey);
        }, $connectionName);

        // handle exception
        $command = "HDEL {$key} {$hashKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * @param string   $key
     * @param array    $hashKeys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool Number of deleted fields
     *
     * @throws RedisException
     *
     * @see  hDel()
     *
     * Removes a values from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @link https://redis.io/commands/hdel
     * @example
     * <pre>
     * RedisUtil::hMSet('h',
     *               array(
     *                    'f1' => 'v1',
     *                    'f2' => 'v2',
     *                    'f3' => 'v3',
     *                    'f4' => 'v4',
     *               ));
     *
     * var_dump( RedisUtil::hDel('h', ['f1', 'f2', 'f3']) );  // int(2)
     * s
     * var_dump( RedisUtil::hGetAll('h') );
     * //// Output:
     * //  array(1) {
     * //    ["f4"]=> string(2) "v4"
     * //  }
     * </pre>
     */
    public static function hDels(
        string $key,
        array  $hashKeys,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hDel($key, ...$hashKeys);
        }, $connectionName);

        // handle exception
        $command = "HDEL {$key} " . join(" ", $hashKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Verify if the specified member exists in a key.
     *
     * @param string   $key
     * @param string   $hashKey
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool If the member exists in the hash table, return TRUE, otherwise return FALSE.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/hexists
     * @example
     * <pre>
     * RedisUtil::hSet('h', 'a', 'x');
     * RedisUtil::hExists('h', 'a');               //  TRUE
     * RedisUtil::hExists('h', 'NonExistingKey');  // FALSE
     * </pre>
     */
    public static function hExists(
        string $key,
        string $hashKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hExists($key, $hashKey);
        }, $connectionName);

        // handle exception
        $command = "HEXISTS {$key} {$hashKey}";
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
     * Gets a value from the hash stored at key.
     * If the hash table doesn't exist, or the key doesn't exist, FALSE is returned.
     *
     * @param string   $key
     * @param string   $hashKey
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|false The value, if the command executed successfully BOOL FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hget
     */
    public static function hGet(
        string $key,
        string $hashKey,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hGet($key, $hashKey);
        }, $connectionName);

        // handle exception
        $command   = "HGET {$key} {$hashKey}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            return false;
        }

        if ($result) {
            $result = self::unSerialize($result, $serializeType);
        }

        return $result;
    }

    /**
     * Returns the whole hash, as an array of strings indexed by strings.
     *
     * @param string   $key
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array An array of elements, the contents of the hash.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hgetall
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSet('h', 'a', 'x');
     * RedisUtil::hSet('h', 'b', 'y');
     * RedisUtil::hSet('h', 'c', 'z');
     * RedisUtil::hSet('h', 'd', 't');
     * var_dump(RedisUtil::hGetAll('h'));
     *
     * // Output:
     * // array(4) {
     * //   ["a"]=>
     * //   string(1) "x"
     * //   ["b"]=>
     * //   string(1) "y"
     * //   ["c"]=>
     * //   string(1) "z"
     * //   ["d"]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hGetAll(
        string $key,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hGetAll($key);
        }, $connectionName);

        // handle exception
        $command = "HGETALL {$key}";
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result) {
            foreach ($result as $itemKey => $itemValue) {
                $lastResult[$itemKey] = self::unSerialize($itemValue, $serializeType);
            }
        }

        return $lastResult;
    }

    /**
     * Increments the value of a member from a hash by a given amount.
     *
     * @param string   $key
     * @param string   $hashKey
     * @param int      $value          (integer) value that will be added to the member's value
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the new value
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hincrby
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hIncrBy('h', 'x', 2); // returns 2: h[x] = 2 now.
     * RedisUtil::hIncrBy('h', 'x', 1); // h[x] ← 2 + 1. Returns 3
     * </pre>
     */
    public static function hIncrBy(
        string $key,
        string $hashKey,
        int    $value,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hIncrBy($key, $hashKey, $value);
        }, $connectionName);

        // handle exception
        $command = "HINCRBY {$key} {$hashKey} {$value}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Increment the float value of a hash field by the given amount
     *
     * @param string   $key
     * @param string   $field
     * @param float    $increment
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return float
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hincrbyfloat
     * @example
     * <pre>
     * RedisUtil::hset('h', 'float', 3);
     * RedisUtil::hset('h', 'int',   3);
     * var_dump( RedisUtil::hIncrByFloat('h', 'float', 1.5) ); // float(4.5)
     *
     * var_dump( RedisUtil::hGetAll('h') );
     *
     * // Output
     *  array(2) {
     *    ["float"]=>
     *    string(3) "4.5"
     *    ["int"]=>
     *    string(1) "3"
     *  }
     * </pre>
     */
    public static function hIncrByFloat(
        string $key,
        string $field,
        float  $increment,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $field, $increment, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hIncrByFloat($key, $field, $increment);
        }, $connectionName);

        // handle exception
        $command = "HINCRBYFLOAT {$key} {$field} {$increment}";
        self::handleException($command, $connectionName, $result);

        if (is_numeric($result)) {
            $result = floatval($result);
        }

        return $result;
    }

    /**
     * Returns the keys in a hash, as an array of strings.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array An array of elements, the keys of the hash. This works like PHP's array_keys().
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hkeys
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSet('h', 'a', 'x');
     * RedisUtil::hSet('h', 'b', 'y');
     * RedisUtil::hSet('h', 'c', 'z');
     * RedisUtil::hSet('h', 'd', 't');
     * var_dump(RedisUtil::hKeys('h'));
     *
     * // Output:
     * // array(4) {
     * // [0]=>
     * // string(1) "a"
     * // [1]=>
     * // string(1) "b"
     * // [2]=>
     * // string(1) "c"
     * // [3]=>
     * // string(1) "d"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hKeys(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hKeys($key);
        }, $connectionName);

        // handle exception
        $command = "HKEYS {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Returns the length of a hash, in number of items
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false the number of items in a hash, FALSE if the key doesn't exist or isn't a hash
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hlen
     * @example
     * <pre>
     * RedisUtil::del('h')
     * RedisUtil::hSet('h', 'key1', 'hello');
     * RedisUtil::hSet('h', 'key2', 'plop');
     * RedisUtil::hLen('h'); // returns 2
     * </pre>
     */
    public static function hLen(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hLen($key);
        }, $connectionName);

        // handle exception
        $command = "HLEN {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Retrieve the values associated to the specified fields in the hash.
     *
     * @param string   $key
     * @param array    $hashKeys
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array Array An array of elements, the values of the specified fields in the hash,
     * with the hash keys as array keys.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hmget
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSet('h', 'field1', 'value1');
     * RedisUtil::hSet('h', 'field2', 'value2');
     * RedisUtil::hMGet('h', array('field1', 'field2')); // returns array('field1' => 'value1', 'field2' => 'value2')
     * </pre>
     */
    public static function hMGet(
        string $key,
        array  $hashKeys,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hMGet($key, $hashKeys);
        }, $connectionName);

        // handle exception
        $command = "HMGET {$key} " . join(" ", $hashKeys);
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result) {
            foreach ($result as $itemKey => $itemValue) {
                if (is_null($itemValue)) {
                    $itemValue = false;
                } else {
                    $itemValue = self::unSerialize($itemValue, $serializeType);
                }
                $lastResult[$itemKey] = $itemValue;
            }
        }

        return $lastResult;
    }

    /**
     * Fills in a whole hash. Non-string values are converted to string, using the standard (string) cast.
     * NULL values are stored as empty strings
     *
     * @param string   $key
     * @param array    $hashKeys       key → value array
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hmset
     * @example
     * <pre>
     * RedisUtil::del('user:1');
     * RedisUtil::hMSet('user:1', array('name' => 'Joe', 'salary' => 2000));
     * RedisUtil::hIncrBy('user:1', 'salary', 100); // Joe earns 100 more now.
     * </pre>
     */
    public static function hMSet(
        string $key,
        array  $hashKeys,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理hashKeys
        $waitHashKeys = [];
        foreach ($hashKeys as $itemKey => $itemValue) {
            $waitHashKeys[$itemKey] = self::serialize($itemValue, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitHashKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hMSet($key, $waitHashKeys);
        }, $connectionName);

        // handle exception
        $fieldStrArr = [];
        foreach ($hashKeys as $itemKey => $valueKey) {
            $fieldStrArr[] = "{$itemKey} \"{$valueKey}\"";
        }
        $command = "HMSET {$key} " . join(" ", $fieldStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Adds a value to the hash stored at key. If this value is already in the hash, FALSE is returned.
     *
     * @param string           $key
     * @param string           $hashKey
     * @param string|int|mixed $value
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return int|bool
     * - 1 if value didn't exist and was added successfully,
     * - 0 if the value was already present and was replaced, FALSE if there was an error
     *
     * @throws Exception\RedisException.
     *
     * @link https://redis.io/commands/hset
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSet('h', 'key1', 'hello');  // 1, 'key1' => 'hello' in the hash at "h"
     * RedisUtil::hGet('h', 'key1');           // returns "hello"
     *
     * RedisUtil::hSet('h', 'key1', 'plop');   // 0, value was replaced.
     * RedisUtil::hGet('h', 'key1');           // returns "plop"
     * </pre>
     */
    public static function hSet(
        string $key,
        string $hashKey,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理value
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hSet($key, $hashKey, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "HSET {$key} {$hashKey} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Adds a value to the hash stored at key only if this field isn't already in the hash.
     *
     * @param string           $key
     * @param string           $hashKey
     * @param string|int|mixed $value
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return bool TRUE if the field was set, FALSE if it was already present.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hsetnx
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSetNx('h', 'key1', 'hello'); // TRUE, 'key1' => 'hello' in the hash at "h"
     * RedisUtil::hSetNx('h', 'key1', 'world'); // FALSE, 'key1' => 'hello' in the hash at "h". No change since the field
     * wasn't replaced.
     * </pre>
     */
    public static function hSetNx(
        string $key,
        string $hashKey,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理value
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $hashKey, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hSetNx($key, $hashKey, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "HSETNX {$key} {$hashKey} {$valStr}";
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
     * Returns the values in a hash, as an array of strings.
     *
     * @param string   $key
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array An array of elements, the values of the hash. This works like PHP's array_values().
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/hvals
     * @example
     * <pre>
     * RedisUtil::del('h');
     * RedisUtil::hSet('h', 'a', 'x');
     * RedisUtil::hSet('h', 'b', 'y');
     * RedisUtil::hSet('h', 'c', 'z');
     * RedisUtil::hSet('h', 'd', 't');
     * var_dump(RedisUtil::hVals('h'));
     *
     * // Output
     * // array(4) {
     * //   [0]=>
     * //   string(1) "x"
     * //   [1]=>
     * //   string(1) "y"
     * //   [2]=>
     * //   string(1) "z"
     * //   [3]=>
     * //   string(1) "t"
     * // }
     * // The order is random and corresponds to redis' own internal representation of the set structure.
     * </pre>
     */
    public static function hVals(
        string $key,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->hVals($key);
        }, $connectionName);

        // handle exception
        $command = "HVALS {$key}";
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result) {
            foreach ($result as $item) {
                $lastResult[] = self::unSerialize($item, $serializeType);
            }
        }

        return $lastResult;
    }
}
