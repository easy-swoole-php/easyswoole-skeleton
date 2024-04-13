<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwooleLib\Redis\Utility\Exception\RedisException;
use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait StringTrait
{
    /**
     * 追加字符串
     * Append specified string to the string stored in specified key.
     *
     * @param string   $key
     * @param string   $value
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Size of the value after the append
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/append
     * @example
     * <pre>
     * RedisUtil::set('key', 'value1');
     * RedisUtil::append('key', 'value2'); // 12
     * RedisUtil::get('key');              // 'value1value2'
     * </pre>
     */
    public static function append(
        string $key,
        string $value,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->appEnd($key, $value);
        }, $connectionName);

        // handle exception
        $command = "APPEND {$key} \"" . addslashes($value) . "\"";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 自减1
     * Decrement the number stored at key by one.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the new value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/decr
     * @example
     * <pre>
     * RedisUtil::decr('key1'); // key1 didn't exists, set to 0 before the increment and now has the value -1
     * RedisUtil::decr('key1'); // -2
     * RedisUtil::decr('key1'); // -3
     * </pre>
     */
    public static function decr(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->decr($key);
        }, $connectionName);

        // handle exception
        $command = "DECR {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 自减value
     * Decrement the number stored at key by one.
     * If the second argument is filled, it will be used as the integer value of the decrement.
     *
     * @param string   $key
     * @param int      $value          that will be subtracted to key (only for decrBy)
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the new value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/decrby
     * @example
     * <pre>
     * RedisUtil::decr('key1');        // key1 didn't exists, set to 0 before the increment and now has the value -1
     * RedisUtil::decr('key1');        // -2
     * RedisUtil::decr('key1');        // -3
     * RedisUtil::decrBy('key1', 10);  // -13
     * </pre>
     */
    public static function decrBy(string $key, int $value, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->decrBy($key, $value);
        }, $connectionName);

        // handle exception
        $command = "DECRBY {$key} {$value}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取一个键
     * Get the value related to the specified key
     *
     * @param string   $key
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|mixed|false If key didn't exist, FALSE is returned.
     * Otherwise, the value related to this key is returned
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/get
     * @example
     * <pre>
     * RedisUtil::set('key', 'hello');
     * RedisUtil::get('key'); // hello
     *
     * // set and get with serializer
     * RedisUtil::set('key', ['asd' => 'as', 'dd' => 123, 'b' => true], 0, RedisConfig::SERIALIZE_PHP);
     * RedisUtil::get('key', RedisConfig::SERIALIZE_PHP));
     * // Output:
     * array(3) {
     *  'asd' => string(2) "as"
     *  'dd' => int(123)
     *  'b' => bool(true)
     * }
     * </pre>
     */
    public static function get(
        string $key,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $redisConfig   = config('redis.' . $connectionName);
        $originDbIndex = $redisConfig['db'];

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex, $originDbIndex) {
            $isResetDb = false;
            if (!is_null($dbIndex)) {
                $isResetDb = true;
                $redis->select($dbIndex);
            }
            $result = $redis->get($key);
            if ($isResetDb) {
                $redis->select($originDbIndex);
            }
            return $result;
        }, $connectionName);

        // handle exception
        $command   = "GET {$key}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if ($result) {
            $result = self::unSerialize($result, $serializeType);
        } else if (is_null($result)) {
            return false;
        }

        return $result;
    }

    /**
     * 返回key旧值并设置新值
     * Sets a value and returns the previous entry at that key.
     *
     * @param string       $key
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return string|mixed A string (mixed, if used serializer), the previous value located at this key
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/getset
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * $exValue = RedisUtil::getSet('x', 'lol');   // return '42', replaces x by 'lol'
     * $newValue = RedisUtil::get('x');            // return 'lol'
     * </pre>
     */
    public static function getSet(
        string $key,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->getSet($key, $val);
        }, $connectionName);

        // handle exception
        $command = "GETSET {$key} \"" . addslashes($val) . "\"";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 自增1
     * Increment the number stored at key by one.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the new value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/incr
     * @example
     * <pre>
     * RedisUtil::incr('key1'); // key1 didn't exists, set to 0 before the increment and now has the value 1
     * RedisUtil::incr('key1'); // 2
     * RedisUtil::incr('key1'); // 3
     * RedisUtil::incr('key1'); // 4
     * </pre>
     */
    public static function incr(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->incr($key);
        }, $connectionName);

        // handle exception
        $command = "INCR {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 自增value
     * Increment the number stored at key by one.
     * If the second argument is filled, it will be used as the integer value of the increment.
     *
     * @param string   $key            key
     * @param int      $value          value that will be added to key (only for incrBy)
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the new value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/incrby
     * @example
     * <pre>
     * RedisUtil::incr('key1');        // key1 didn't exists, set to 0 before the increment and now has the value 1
     * RedisUtil::incr('key1');        // 2
     * RedisUtil::incr('key1');        // 3
     * RedisUtil::incr('key1');        // 4
     * RedisUtil::incrBy('key1', 10);  // 14
     * </pre>
     */
    public static function incrBy(
        string $key,
        int    $value,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->incrBy($key, $value);
        }, $connectionName);

        // handle exception
        $command = "INCRBY {$key} {$value}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 自增value浮点值
     * Increment the float value of a key by the given amount
     *
     * @param string   $key
     * @param float    $increment
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return float
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/incrbyfloat
     * @example
     * <pre>
     * RedisUtil::set('x', 3);
     * RedisUtil::incrByFloat('x', 1.5);   // float(4.5)
     * RedisUtil::get('x');                // float(4.5)
     * </pre>
     */
    public static function incrByFloat(
        string $key,
        float  $increment,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 预处理key
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $increment, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->incrByFloat($key, $increment);
        }, $connectionName);

        // handle exception
        $command = "INCRBYFLOAT {$key} {$increment}";
        self::handleException($command, $connectionName, $result);

        if (is_numeric($result)) {
            $result = floatval($result);
        }

        return $result;
    }

    /**
     * 获取多个key的值(参数数组)
     * Returns the values of all specified keys.
     *
     * For every key that does not hold a string value or does not exist,
     * the special value false is returned. Because of this, the operation never fails.
     *
     * @param array    $keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/mget
     * @example
     * <pre>
     * RedisUtil::del('x', 'y', 'z', 'h');  // remove x y z h
     * RedisUtil::mset(array('x' => 'a', 'y' => 'b', 'z' => 'c'));
     * RedisUtil::hset('h', 'field', 'value');
     * var_dump(RedisUtil::mget(array('x', 'y', 'z', 'h')));
     * // Output:
     * // array(3) {
     * //   [0]=> string(1) "a"
     * //   [1]=> string(1) "b"
     * //   [2]=> string(1) "c"
     * //   [3]=> bool(false)
     * // }
     * </pre>
     */
    public static function mget(array $keys, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理key
        $waitKeys = [];
        foreach ($keys as $key) {
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->mGet($waitKeys);
        }, $connectionName);

        // handle exception
        $command = "MGET " . join(" ", $waitKeys);
        self::handleException($command, $connectionName, $result);

        // handle when $retValue is null
        foreach ($result as &$retValue) {
            if (is_null($retValue)) {
                $retValue = false;
            }
        }
        unset($retValue);

        return $result;
    }

    /**
     * 设置多个key的值,参数为关联数组
     * Sets multiple key-value pairs in one atomic command.
     * MSETNX only returns TRUE if all the keys were set (see SETNX).
     *
     * @param array    $array          Pairs: array(key => value, ...)
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/mset
     * @example
     * <pre>
     * RedisUtil::mset(array('key0' => 'value0', 'key1' => 'value1'));
     * var_dump(RedisUtil::get('key0'));
     * var_dump(RedisUtil::get('key1'));
     * // Output:
     * // string(6) "value0"
     * // string(6) "value1"
     * </pre>
     */
    public static function mset(array $array, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理数据的key
        $data = [];
        foreach ($array as $key => $value) {
            $itemKey        = self::handleKeyPrefix($key, $connectionName);
            $data[$itemKey] = $value;
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($data, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->mSet($data);
        }, $connectionName);

        // handle exception
        $keyValueStrArr = [];
        foreach ($data as $dataKey => $dataValue) {
            $keyValueStrArr[] = "{$dataKey} \"" . addslashes($dataValue) . "\"";
        }
        $command = "MSET " . join(" ", $keyValueStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 当所有key不存在时,设置多个key值,参数和mSet一样
     *
     * @param array    $array
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int 1 (if the keys were set) or 0 (no key was set)
     *
     * @throws Exception\RedisException
     *
     * @see  mset()
     *
     * @link https://redis.io/commands/msetnx
     */
    public static function msetnx(array $array, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 预处理数据的key
        $data = [];
        foreach ($array as $itemKey => $itemValue) {
            $key        = self::handleKeyPrefix($itemKey, $connectionName);
            $data[$key] = $itemValue;
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($data, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->mSetNx($data);
        }, $connectionName);

        // handle exception
        $keyValueStrArr = [];
        foreach ($data as $dataKey => $dataValue) {
            $keyValueStrArr[] = "{$dataKey} \"" . addslashes($dataValue) . "\"";
        }
        $command = "MSETNX " . join(" ", $keyValueStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 和setEx类似,过期时间为毫秒
     * Set the value and expiration in milliseconds of a key.
     *
     * @param string       $key
     * @param int          $ttl            , in milliseconds.
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool TRUE if the command is successful
     *
     * @throws Exception\RedisException
     *
     * @see     setex()
     *
     * @link    https://redis.io/commands/psetex
     * @example RedisUtil::psetex('key', 1000, 'value'); // sets key → value, with 1sec TTL.
     */
    public static function psetex(
        string $key,
        int    $ttl,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理
        $val    = self::serialize($value, $serializeType);
        $result = RedisPool::invoke(function (Redis $redis) use ($key, $ttl, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->pSetEx($key, $ttl, $val);
        }, $connectionName);

        // handle exception
        $command    = "PSETEX {$key} {$ttl} \"" . addslashes($val) . "\"";
        $allowFalse = true;
        $allowNull  = true;
        self::handleException($command, $connectionName, $result, $allowNull, $allowFalse);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 设置一个键,以及设置过期时间（单位秒）
     * Set the string value in argument as value of the key.
     *
     * @param string          $key
     * @param string|mixed    $value
     * @param int|array|mixed $timeout        [optional] Calling setex() is preferred if you want a timeout.<br>
     * @param int             $serializeType  [optional]
     *                                        对值做处理，支持RedisConfig::SERIALIZE_NONE/RedisConfig::SERIALIZE_PHP/RedisConfig::SERIALIZE_JSON
     * @param int|null        $dbIndex        [optional]
     * @param string          $connectionName [optional]
     *                                        RedisConfig::SERIALIZE_NONE: 不做任何处理，默认方式
     *                                        RedisConfig::SERIALIZE_PHP: 使用serialize()函数对value处理
     *                                        RedisConfig::SERIALIZE_JSON: 使用json_encode()函数对value处理
     *                                        - EX seconds -- Set the specified expire time, in seconds.<br>
     *                                        - PX milliseconds -- Set the specified expire time, in milliseconds.<br>
     *                                        - NX -- Only set the key if it does not already exist.<br>
     *                                        - XX -- Only set the key if it already exist.<br>
     *                                        <pre>
     *                                        // Simple key -> value set
     *                                        RedisUtil::set('key', 'value');
     *                                        RedisUtil::set('key', ['name' => 'lava'], 0, RedisConfig::SERIALIZE_PHP);
     *                                        // SET key serialize(['name' => 'lava']) RedisUtil::set('key', ['name' =>
     *                                        'lava'], 0, RedisConfig::SERIALIZE_JSON); // SET key json_encode(['name'
     *                                        => 'lava'])
     *
     * // Will set the key, if it doesn't exist, with a ttl of 10 seconds
     * RedisUtil::set('key', 'value', ['NX', 'EX' => 10]);
     *
     * // Will set a key, if it does exist, with a ttl of 1000 milliseconds
     * RedisUtil::set('key', 'value', ['XX', 'PX' => 1000]);
     * </pre>
     *
     * @return bool TRUE if the command is successful
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/set
     */
    public static function set(
        string $key,
               $value,
               $timeout = 0,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理value
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $timeout, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->set($key, $val, $timeout);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "SET {$key} {$valStr}";
        if (!is_numeric($timeout)) {
            $command = "SET {$key} {$valStr}";
            $nxOrXx  = $timeout[0] ?? '';
            $px      = $timeout['PX'] ?? '';
            $ex      = $timeout['EX'] ?? '';
            if ($nxOrXx) {
                $command .= ' ' . strtoupper($nxOrXx);
            }
            if ($px) {
                $command .= " PX {$px}";
            } else if ($ex) {
                $command .= " EX {$ex}";
            }
        }
        $allowFalse = true;
        $allowNull  = true;
        self::handleException($command, $connectionName, $result, $allowNull, $allowFalse);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 返回子字符串
     * Return a substring of a larger string
     *
     * @param string   $key
     * @param int      $start
     * @param int      $end
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string the substring
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/getrange
     * @example
     * <pre>
     * RedisUtil::set('key', 'string value');
     * RedisUtil::getRange('key', 0, 5);   // 'string'
     * RedisUtil::getRange('key', -5, -1); // 'value'
     * </pre>
     */
    public static function getRange(
        string $key,
        int    $start,
        int    $end,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $end, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->getRange($key, $start, $end);
        }, $connectionName);

        // handle exception
        $command = "GETRANGE {$key} {$start} {$end}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取指定偏移量上的bit值
     * Return a single bit out of a larger string
     *
     * @param string   $key
     * @param int      $offset
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the bit value (0 or 1)
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/getbit
     * @example
     * <pre>
     * RedisUtil::set('key', "\x7f");  // this is 0111 1111
     * RedisUtil::getBit('key', 0);    // 0
     * RedisUtil::getBit('key', 1);    // 1
     * </pre>
     */
    public static function getBit(
        string $key,
        int    $offset,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $offset, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->getBit($key, $offset);
        }, $connectionName);

        // handle exception
        $command = "GETBIT {$key} {$offset}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 设置偏移量的bit值
     * Changes a single bit of a string.
     *
     * @param string   $key
     * @param int      $offset
     * @param bool|int $value          bool or int (1 or 0)
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int 0 or 1, the value of the bit before it was set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/setbit
     * @example
     * <pre>
     * RedisUtil::set('key', "*");     // ord("*") = 42 = 0x2f = "0010 1010"
     * RedisUtil::setBit('key', 5, 1); // returns 0
     * RedisUtil::setBit('key', 7, 1); // returns 0
     * RedisUtil::get('key');          // chr(0x2f) = "/" = b("0010 1111")
     * </pre>
     */
    public static function setBit(
        string $key,
        int    $offset,
               $value,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $offset, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->setBit($key, $offset, $value);
        }, $connectionName);

        // handle exception
        $command = "SETBIT {$key} {$offset} {$value}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * Count bits in a string
     *
     * @param string   $key
     * @param int|null $start
     * @param int|null $end
     * @param int|null $dbIndex
     * @param string   $connectionName
     *
     * @return int The number of bits set to 1 in the value behind the input key
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/bitcount
     * @example
     * <pre>
     * RedisUtil::set('bit', '345'); // // 11 0011  0011 0100  0011 0101
     * var_dump( RedisUtil::bitCount('bit', 0, 0) ); // int(4)
     * var_dump( RedisUtil::bitCount('bit', 1, 1) ); // int(3)
     * var_dump( RedisUtil::bitCount('bit', 2, 2) ); // int(4)
     * var_dump( RedisUtil::bitCount('bit', 0, 2) ); // int(11)
     * </pre>
     */
    public static function bitCount(string $key,
                                    int    $start = null,
                                    int    $end = null,
                                    int    $dbIndex = null,
                                    string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $end, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->bitCount($key, $start, $end);
        }, $connectionName);

        // handle exception
        $command = "BITCOUNT {$key}";
        if ($start) {
            $command .= " " . $start;
        }
        if ($end) {
            $command .= " " . $end;
        }
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 设置键、值以及值的过期时间(秒)
     * Set the string value in argument as value of the key, with a time to live.
     *
     * @param string       $key
     * @param int          $ttl
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool TRUE if the command is successful
     *
     * @throws Exception\RedisException
     *
     * @link    https://redis.io/commands/setex
     * @example RedisUtil::setex('key', 3600, 'value'); // sets key → value, with 1h TTL.
     */
    public static function setex(
        string $key,
        int    $ttl,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $val    = self::serialize($value, $serializeType);
        $result = RedisPool::invoke(function (Redis $redis) use ($key, $ttl, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->setEx($key, $ttl, $val);
        }, $connectionName);

        // handle exception
        $command = "SETEX {$key} {$ttl} \"" . addslashes($val) . "\"";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * key不存在时设置key的值
     * Set the string value in argument as value of the key if the key doesn't already exist in the database.
     *
     * @param string       $key
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/setnx
     * @example
     * <pre>
     * RedisUtil::setnx('key', 'value');   // return TRUE
     * RedisUtil::setnx('key', 'value');   // return FALSE
     * </pre>
     */
    public static function setnx(
        string $key,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $val    = self::serialize($value, $serializeType);
        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->setNx($key, $val);
        }, $connectionName);

        // handle exception
        $command = "SETNX {$key} \"" . addslashes($val) . "\"";
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
     * 更改较大字符串的子字符串。
     * Changes a substring of a larger string.
     *
     * @param string   $key
     * @param int      $offset
     * @param string   $value
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the length of the string after it was modified
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/setrange
     * @example
     * <pre>
     * RedisUtil::set('key', 'Hello world');
     * RedisUtil::setRange('key', 6, "redis"); // returns 11
     * RedisUtil::get('key');                  // "Hello redis"
     * </pre>
     */
    public static function setRange(
        string $key,
        int    $offset,
        string $value,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $offset, $value, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->setRange($key, $offset, $value);
        }, $connectionName);

        // handle exception
        $command = "SETRANGE {$key} {$offset} \"" . addslashes($value) . "\"";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回key所储存的字符串值的长度
     * Get the length of a string value.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/strlen
     * @example
     * <pre>
     * RedisUtil::set('key', 'value');
     * RedisUtil::strlen('key'); // 5
     * </pre>
     */
    public static function strlen(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->strLen($key);
        }, $connectionName);

        // handle exception
        $command = "STRLEN {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
