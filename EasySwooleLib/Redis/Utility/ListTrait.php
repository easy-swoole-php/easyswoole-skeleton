<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait ListTrait
{
    /**
     * 移出并获取keys列表的第一个元素，如果keys列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止
     *
     * Is a blocking lPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, blPop will block
     * during the specified timeout until an element is pushed to one of those lists. This element will be popped.
     *
     * @param string[] $keys           String array containing the keys of the lists OR variadic list of strings
     * @param int      $timeout        Timeout is always the required final parameter
     * @param int      $serializeType  [optional] [optional]
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return array ['listName', 'element']
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/blpop
     * @example
     * <pre>
     * // Non blocking feature
     * RedisUtil::lPush('key1', 'A');
     * RedisUtil::del('key2');
     *
     * RedisUtil::blPop(['key1', 'key2'], 10); // array('key1', 'A')
     *
     * RedisUtil::brPop(['key1', 'key2'], 10); // array('key1', 'A')
     *
     * // Blocking feature
     * // process 1
     * RedisUtil::>del('key1');
     * $redis->blPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * RedisUtil::lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public static function blPop(
        array  $keys,
        int    $timeout,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        $waitKeys = [];
        foreach ($keys as $key) {
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $timeout, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->bLPop($waitKeys, $timeout);
        }, $connectionName);

        // handle exception
        $command   = "BLPOP " . join(' ', $waitKeys);
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            return [];
        }

        $lastResult = [];
        if ($result) {
            foreach ($result as $key => $item) {
                if ($item) {
                    $theItem = self::unSerialize($item, $serializeType);
                } else {
                    $theItem = $item;
                }
                $lastResult[$key] = $theItem;
            }
        }

        return $lastResult;
    }

    /**
     * 移出并获取keys列表的最后一个元素，如果$eys列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     *
     * Is a blocking rPop primitive. If at least one of the lists contains at least one element,
     * the element will be popped from the head of the list and returned to the caller.
     * Il all the list identified by the keys passed in arguments are empty, brPop will
     * block during the specified timeout until an element is pushed to one of those lists. T
     * his element will be popped.
     *
     * @param string|string[] $keys           String array containing the keys of the lists OR variadic list of strings
     * @param int             $timeout        Timeout is always the required final parameter
     * @param int             $serializeType  [optional] [optional]
     * @param int|null        $dbIndex        [optional] [optional]
     * @param string          $connectionName [optional] [optional]
     *
     * @return array ['listName', 'element']
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/brpop
     * @example
     * <pre>
     * // Non blocking feature
     * RedisUtil::lPush('key1', 'A');
     * RedisUtil::del('key2');
     *
     * RedisUtil::blPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * RedisUtil::brPop(array('key1', 'key2'), 10); // array('key1', 'A')
     *
     * // Blocking feature
     *
     * // process 1
     * RedisUtil::del('key1');
     * RedisUtil::blPop('key1', 10);
     * // blocking for 10 seconds
     *
     * // process 2
     * RedisUtil::lPush('key1', 'A');
     *
     * // process 1
     * // array('key1', 'A') is returned
     * </pre>
     */
    public static function brPop(
        array  $keys,
        int    $timeout,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        $waitKeys = [];
        foreach ($keys as $key) {
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $timeout, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->bRPop($waitKeys, $timeout);
        }, $connectionName);

        // handle exception
        $command   = "BRPOP " . join(' ', $waitKeys);
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            return [];
        }

        $lastResult = [];
        if ($result) {
            foreach ($result as $key => $item) {
                if ($item) {
                    $theItem = self::unSerialize($item, $serializeType);
                } else {
                    $theItem = $item;
                }
                $lastResult[$key] = $theItem;
            }
        }

        return $lastResult;
    }

    /**
     * 从列表中弹出一个值，将弹出的元素插入到另外一个列表中并返回它；
     * 如果列表没有元素会阻塞列表直到等待超时或发现可弹出元素为止。
     *
     * A blocking version of rpoplpush, with an integral timeout in the third parameter.
     *
     * @param string   $srcKey
     * @param string   $dstKey
     * @param int      $timeout
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return string|mixed|bool The element that was moved in case of success, FALSE in case of timeout
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/brpoplpush
     */
    public static function brpoplpush(
        string $srcKey,
        string $dstKey,
        int    $timeout,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($srcKey, $connectionName);
        self::handleKeyPrefix($dstKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($srcKey, $dstKey, $timeout, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->bRPopLPush($srcKey, $dstKey, $timeout);
        }, $connectionName);

        // handle exception
        $command   = "BRPOPLPUSH {$srcKey} {$dstKey} {$timeout}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 通过索引获取列表中的元素
     *
     * Return the specified element of the list stored at the specified key.
     * 0 the first element, 1 the second ... -1 the last element, -2 the penultimate ...
     * Return FALSE in case of a bad index or a key that doesn't point to a list.
     *
     * @param string   $key
     * @param int      $index
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return mixed|bool the element at this index
     * Bool FALSE if the key identifies a non-string data type, or no value corresponds to this index in the list Key.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lindex
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * RedisUtil::lIndex('key1', 0);   // 'A'
     * RedisUtil::lIndex('key1', -1);  // 'C'
     * RedisUtil::lIndex('key1', 10);  // `FALSE`
     * </pre>
     */
    public static function lIndex(
        string $key,
        int    $index,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $index, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lIndex($key, $index);
        }, $connectionName);

        // handle exception
        $command   = "LINDEX {$key} {$index}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 在列表的元素前或者后插入元素
     *
     * Insert value in the list before or after the pivot value. the parameter options
     * specify the position of the insert (before or after). If the list didn't exists,
     * or the pivot didn't exists, the value is not inserted.
     *
     * @param string       $key
     * @param bool         $isBefore       false/true
     * @param string       $pivot
     * @param string|mixed $value
     * @param int          $serializeType  [optional] [optional]
     * @param int|null     $dbIndex        [optional] [optional]
     * @param string       $connectionName [optional] [optional]
     *
     * @return int The number of the elements in the list, -1 if the pivot didn't exists.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/linsert
     * @example
     * <pre>
     * RedisUtil::del('key1');
     * RedisUtil::lInsert('key1', false, 'A', 'X');     // 0
     *
     * RedisUtil::lPush('key1', 'A');
     * RedisUtil::lPush('key1', 'B');
     * RedisUtil::lPush('key1', 'C');
     *
     * RedisUtil::lInsert('key1', true, 'C', 'X');      // 4
     * RedisUtil::lRange('key1', 0, -1);                // array('A', 'B', 'X', 'C')
     *
     * RedisUtil::lInsert('key1', false, 'C', 'Y');     // 5
     * RedisUtil::lRange('key1', 0, -1);                // array('A', 'B', 'X', 'C', 'Y')
     *
     * RedisUtil::lInsert('key1', false, 'W', 'value'); // -1
     * </pre>
     */
    public static function lInsert(
        string $key,
        bool   $isBefore,
        string $pivot,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $isBefore, $pivot, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lInsert($key, $isBefore, $pivot, $val);
        }, $connectionName);

        // handle exception
        $valStr      = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $positionStr = $isBefore ? 'BEFORE' : 'AFTER';
        $command     = "LINSERT {$key} {$positionStr} {$pivot} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取列表长度
     *
     * Returns the size of a list identified by Key. If the list didn't exist or is empty,
     * the command returns 0. If the data type identified by Key is not a list, the command return FALSE.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return int|bool The size of the list identified by Key exists.
     * bool FALSE if the data type identified by Key is not list
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/llen
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C'); // key1 => [ 'A', 'B', 'C' ]
     * RedisUtil::lLen('key1');       // 3
     * RedisUtil::rPop('key1');
     * RedisUtil::lLen('key1');       // 2
     * </pre>
     */
    public static function lLen(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lLen($key);
        }, $connectionName);

        // handle exception
        $command   = "LLEN {$key}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 移出并获取列表的第一个元素
     *
     * Returns and removes the first element of the list.
     *
     * @param string   $key
     * @param int      $serializeType  [optional] [optional]
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return mixed|bool if command executed successfully BOOL FALSE in case of failure (empty list)
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lpop
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * RedisUtil::lPop('key1');        // key1 => [ 'B', 'C' ]
     * </pre>
     */
    public static function lPop(
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
            return $redis->lPop($key);
        }, $connectionName);

        // handle exception
        $command   = "LPOP {$key}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if ($result) {
            $result = self::unSerialize($result, $serializeType);
        } else if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 将一个值插入到列表头部
     *
     * Adds the string values to the head (left) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string       $key
     * @param string|mixed $value          value to push in key, if dont used serialized, used string
     * @param int          $serializeType  [optional] [optional]
     * @param int|null     $dbIndex        [optional] [optional]
     * @param string       $connectionName [optional] [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lpush
     * @example
     * <pre>
     * RedisUtil::lPush('l', 'v1') // int(1)
     * var_dump( RedisUtil::lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [1]=> string(2) "v1"
     * // }
     * </pre>
     */
    public static function lPush(
        string $key,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lPush($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "LPUSH {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 将多个值插入到列表头部
     *
     * Adds the string values to the head (left) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string   $key
     * @param array    $values         Variadic list of values to push in key, if dont used serialized, used string
     * @param int      $serializeType  [optional] [optional]
     * @param int|null $dbIndex        [optional] [optional]
     * @param string   $connectionName [optional] [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lpush
     * @example
     * <pre>
     * $redis->lPushs('l', ['v1', 'v2', 'v3', 'v4'])   // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v4"
     * //   [1]=> string(2) "v3"
     * //   [2]=> string(2) "v2"
     * //   [3]=> string(2) "v1"
     * // }
     * </pre>
     */
    public static function lPushs(
        string $key,
        array  $values,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理values
        $waitValues = [];
        foreach ($values as $value) {
            $waitValues[] = self::serialize($value, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitValues, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lPush($key, ...$waitValues);
        }, $connectionName);

        // handle exception
        $valuesStrArr = [];
        foreach ($waitValues as $waitValue) {
            $valuesStrArr[] = is_numeric($waitValue) ? $waitValue : "\"" . addslashes($waitValue) . "\"";
        }
        $command = "LPUSH {$key} " . join(' ', $valuesStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 将一个值插入到已存在的列表头部
     *
     * Adds the string value to the head (left) of the list if the list exists.
     *
     * @param string       $key
     * @param string|mixed $value          String, value to push in key
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lpushx
     * @example
     * <pre>
     * RedisUtil::del('key1');
     * RedisUtil::lPushx('key1', 'A');     // returns 0
     * RedisUtil::lPush('key1', 'A');      // returns 1
     * RedisUtil::lPushx('key1', 'B');     // returns 2
     * RedisUtil::lPushx('key1', 'C');     // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public static function lPushx(
        string $key,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lPuShx($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "LPUSHX {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取列表指定范围内的元素
     *
     * Returns the specified elements of the list stored at the specified key in
     * the range [start, end]. start and stop are interpretated as indices: 0 the first element,
     * 1 the second ... -1 the last element, -2 the penultimate ...
     *
     * @param string   $key
     * @param int      $start
     * @param int      $end
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array containing the values in specified range.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lrange
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');
     * RedisUtil::lRange('key1', 0, -1); // array('A', 'B', 'C')
     * </pre>
     */
    public static function lRange(
        string $key,
        int    $start,
        int    $end,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $end, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lRange($key, $start, $end);
        }, $connectionName);

        // handle exception
        $command = "LRANGE {$key} {$start} {$end}";
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result) {
            foreach ($result as $item) {
                if ($item) {
                    $item = self::unSerialize($item, $serializeType);
                }
                $lastResult[] = $item;
            }
        }

        return $lastResult;
    }

    /**
     * 移除列表元素
     *
     * Removes the first count occurrences of the value element from the list.
     * If count is zero, all the matching elements are removed. If count is negative,
     * elements are removed from tail to head.
     *
     * @param string       $key
     * @param string|mixed $value
     * @param int          $count
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|bool the number of elements to remove
     * bool FALSE if the value identified by key is not a list.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lrem
     * @example
     * <pre>
     * RedisUtil::lPush('key1', 'A');
     * RedisUtil::lPush('key1', 'B');
     * RedisUtil::lPush('key1', 'C');
     * RedisUtil::lPush('key1', 'A');
     * RedisUtil::lPush('key1', 'A');
     *
     * RedisUtil::lRange('key1', 0, -1);   // array('A', 'A', 'C', 'B', 'A')
     * RedisUtil::lRem('key1', 'A', 2);    // 2
     * RedisUtil::lRange('key1', 0, -1);   // array('C', 'B', 'A')
     * </pre>
     */
    public static function lRem(
        string $key,
               $value,
        int    $count,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理value
        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $count, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lRem($key, $count, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "LREM {$key} {$count} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 通过索引设置列表元素的值
     *
     * Set the list at index with the new value.
     *
     * @param string       $key
     * @param int          $index
     * @param string|mixed $value
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return bool TRUE if the new value is setted.
     * FALSE if the index is out of range, or data type identified by key is not a list.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/lset
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');    // key1 => [ 'A', 'B', 'C' ]
     * RedisUtil::lIndex('key1', 0);     // 'A'
     * RedisUtil::lSet('key1', 0, 'X');
     * RedisUtil::lIndex('key1', 0);     // 'X'
     * </pre>
     */
    public static function lSet(
        string $key,
        int    $index,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $index, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lSet($key, $index, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "LSET {$key} {$index} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 对一个列表进行修剪(trim)，即：让列表只保留指定区间内的元素，不在指定区间之内的元素都将被删除。
     *
     * Trims an existing list so that it will contain only a specified range of elements.
     *
     * @param string   $key
     * @param int      $start
     * @param int      $stop
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array|false Bool return FALSE if the key identify a non-list value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/ltrim
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');
     * RedisUtil::lRange('key1', 0, -1); // array('A', 'B', 'C')
     * RedisUtil::lTrim('key1', 0, 1);
     * RedisUtil::lRange('key1', 0, -1); // array('A', 'B')
     * </pre>
     */
    public static function lTrim(
        string $key,
        int    $start,
        int    $stop,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $stop, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->lTrim($key, $start, $stop);
        }, $connectionName);

        // handle exception
        $command = "LTRIM {$key} {$start} {$stop}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移出并获取列表的最后一个元素
     *
     * Returns and removes the last element of the list.
     *
     * @param string   $key
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return mixed|bool if command executed successfully BOOL FALSE in case of failure (empty list)
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/rpop
     * @example
     * <pre>
     * RedisUtil::rPush('key1', 'A');
     * RedisUtil::rPush('key1', 'B');
     * RedisUtil::rPush('key1', 'C');  // key1 => [ 'A', 'B', 'C' ]
     * RedisUtil::rPop('key1');        // key1 => [ 'A', 'B' ]
     * </pre>
     */
    public static function rPop(
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
            return $redis->rPop($key);
        }, $connectionName);

        // handle exception
        $command   = "RPOP {$key}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if ($result) {
            $result = self::unSerialize($result, $serializeType);
        } else if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 移除列表的最后一个元素，并将该元素添加到另一个列表并返回
     *
     * Pops a value from the tail of a list, and pushes it to the front of another list.
     * Also return this value.
     *
     * @param string   $srcKey
     * @param string   $dstKey
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|mixed|false The element that was moved in case of success, FALSE in case of failure.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/rpoplpush
     * @example
     * <pre>
     * RedisUtil::del('x', 'y');
     *
     * RedisUtil::lPush('x', 'abc');
     * RedisUtil::lPush('x', 'def');
     * RedisUtil::lPush('y', '123');
     * RedisUtil::lPush('y', '456');
     *
     * // move the last of x to the front of y.
     * var_dump(RedisUtil::rpoplpush('x', 'y'));
     * var_dump(RedisUtil::lRange('x', 0, -1));
     * var_dump(RedisUtil::lRange('y', 0, -1));
     *
     * //Output:
     * //
     * //string(3) "abc"
     * //array(1) {
     * //  [0]=>
     * //  string(3) "def"
     * //}
     * //array(3) {
     * //  [0]=>
     * //  string(3) "abc"
     * //  [1]=>
     * //  string(3) "456"
     * //  [2]=>
     * //  string(3) "123"
     * //}
     * </pre>
     */
    public static function rpoplpush(
        string $srcKey,
        string $dstKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($srcKey, $connectionName);
        self::handleKeyPrefix($dstKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($srcKey, $dstKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->rPopLPush($srcKey, $dstKey);
        }, $connectionName);

        // handle exception
        $command   = "RPOPLPUSH {$srcKey} {$dstKey}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 在列表中添加一个值
     *
     * Adds the string values to the tail (right) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string       $key
     * @param string|mixed $value          value to push in key, if dont used serialized, used string
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/rpush
     * @example
     * <pre>
     * RedisUtil::rPush('l', 'v1'); // int(1)
     * $redis->rPush('l', 'v1', 'v2', 'v3', 'v4');    // int(4)
     * var_dump( $redis->lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v2"
     * //   [2]=> string(2) "v3"
     * //   [3]=> string(2) "v4"
     * // }
     * </pre>
     */
    public static function rPush(
        string $key,
               $value,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        $val = self::serialize($value, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->rPush($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "RPUSH {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 在列表中添加多个值
     *
     * Adds the string values to the tail (right) of the list.
     * Creates the list if the key didn't exist.
     * If the key exists and is not a list, FALSE is returned.
     *
     * @param string   $key
     * @param array    $values         Variadic list of values to push in key, if dont used serialized, used string
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/rpush
     * @example
     * <pre>
     * RedisUtil::rPushs('l', ['v1', 'v2', 'v3', 'v4']); // int(4)
     * var_dump( RedisUtil::lRange('l', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v2"
     * //   [2]=> string(2) "v3"
     * //   [3]=> string(2) "v4"
     * // }
     * </pre>
     */
    public static function rPushs(
        string $key,
        array  $values,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理 key 前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理values
        $waitValues = [];
        foreach ($values as $value) {
            $waitValues[] = self::serialize($value, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitValues, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->rPush($key, ...$waitValues);
        }, $connectionName);

        // handle exception
        $waitValuesStrArr = [];
        foreach ($waitValues as $waitValue) {
            $waitValuesStrArr[] = is_numeric($waitValue) ? $waitValue : "\"" . addslashes($waitValue) . "\"";
        }
        $command = "RPUSH {$key} " . join(' ', $waitValuesStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 为已存在的列表添加值
     *
     * Adds the string value to the tail (right) of the list if the ist exists. FALSE in case of Failure.
     *
     * @param string       $key
     * @param string|mixed $value          String, value to push in key
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|false The new length of the list in case of success, FALSE in case of Failure.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/rpushx
     * @example
     * <pre>
     * RedisUtil::del('key1');
     * RedisUtil::rPushx('key1', 'A'); // returns 0
     * RedisUtil::rPush('key1', 'A'); // returns 1
     * RedisUtil::rPushx('key1', 'B'); // returns 2
     * RedisUtil::rPushx('key1', 'C'); // returns 3
     * // key1 now points to the following list: [ 'A', 'B', 'C' ]
     * </pre>
     */
    public static function rPushx(
        string $key,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->rPuShx($key, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "RPUSHX {$key} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
