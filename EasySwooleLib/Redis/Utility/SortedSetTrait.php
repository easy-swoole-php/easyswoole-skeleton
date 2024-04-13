<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait SortedSetTrait
{
    /**
     * 向有序集合添加一个成员，或者更新已存在成员的分数
     *
     * Adds the specified member with a given score to the sorted set stored at key
     *
     * @param string             $key            Required key
     * @param float|string|mixed $score          Required score
     * @param string|float|mixed $value          Required value
     * @param int                $serializeType  [optional]
     * @param int|null           $dbIndex        [optional]
     * @param string             $connectionName [optional]
     *
     * @return int Number of values added
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zadd
     * @example
     * <pre>
     * RedisUtil::zAdd('z', 1, 'v1');         // int(1)
     * RedisUtil::zRem('z', 'v1');            // int(1)
     * RedisUtil::zAdd('z', 7, 'v6');         // int(1)
     * RedisUtil::zAdd('z', 7, 'v7');         // int(0)
     *
     * var_dump( RedisUtil::zRange('z', 0, -1) );
     * // Output:
     * // array(1) {
     * //   [0]=> string(2) "v6"
     * // }
     *
     * var_dump( $redis->zRange('z', 0, -1, true) );
     * // Output:
     * // array(1) {
     * //   ["v6"]=> float(7)
     * </pre>
     */
    public static function zAdd(
        string $key,
               $score,
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

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $score, $val, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zAdd($key, $score, $val);
        }, $connectionName);

        // handle exception
        $valStr  = is_numeric($val) ? $val : "\"" . addslashes($val) . "\"";
        $command = "ZADD {$key} {$score} {$valStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 向有序集合添加一个或多个成员，或者更新已存在成员的分数
     *
     * Adds the specified member with a given score to the sorted set stored at key
     *
     * @param string   $key            Required key
     * @param array    $data
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of values added
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zadd
     * @example
     * <pre>
     * RedisUtil::zAdds('keyName', [`score1`, '`value1`', `score2`, '`value2`', `score3`, '`value3`, `score4`, '`value4`']);  // int(4)
     * RedisUtil::zAdds('z', [1, 'v1', 2, 'v2', 3, 'v3', 4, 'v4']);  // int(4)
     * RedisUtil::zRem('z', 'v2', 'v3');                             // int(2)
     *
     * var_dump( RedisUtil::zRange('z', 0, -1) );
     * // Output:
     * // array(4) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v4"
     * //   [2]=> string(2) "v5"
     * //   [3]=> string(2) "v8"
     * // }
     *
     * var_dump( RedisUtil::zRange('z', 0, -1, true) );
     * // Output:
     * // array(4) {
     * //   ["v1"]=> float(1)
     * //   ["v4"]=> float(4)
     * //   ["v5"]=> float(5)
     * //   ["v6"]=> float(8)
     * </pre>
     */
    public static function zAdds(
        string $key,
        array  $data,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理
        $datas = [];
        foreach ($data as $index => $item) {
            if ($index % 2 === 0) {
                $item = self::serialize($item, $serializeType);
            }
            $datas[$index] = $item;
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $datas, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zAdd($key, ...$datas);
        }, $connectionName);

        // handle exception
        $memberStrArr = [];
        foreach ($datas as $index => $item) {
            if (($index + 1) % 2 === 0) {
                $item = is_numeric($item) ? $item : "\"" . addslashes($item) . "\"";
            }
            $memberStrArr[] = $item;
        }
        $command = "ZADD {$key} " . join(' ', $memberStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 获取有序集合的成员数
     *
     * Returns the cardinality of an ordered set.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int the set's cardinality
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zcard
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 0, 'val0');
     * RedisUtil::zAdd('key', 2, 'val2');
     * RedisUtil::zAdd('key', 10, 'val10');
     * RedisUtil::zCard('key');             // 3
     * </pre>
     */
    public static function zCard(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zCard($key);
        }, $connectionName);

        // handle exception
        $command = "ZCARD {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 计算在有序集合中指定区间分数的成员数
     *
     * Returns the number of elements of the sorted set stored at the specified key which have
     * scores in the range [start,end]. Adding a parenthesis before start or end excludes it
     * from the range. +inf and -inf are also valid limits.
     *
     * @param string           $key
     * @param int|string|mixed $start
     * @param int|string|mixed $end
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return int the size of a corresponding zRangeByScore
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zcount
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 0, 'val0');
     * RedisUtil::zAdd('key', 2, 'val2');
     * RedisUtil::zAdd('key', 10, 'val10');
     * RedisUtil::zCount('key', 0, 3); // 2, corresponding to array('val0', 'val2')
     * </pre>
     */
    public static function zCount(
        string $key,
               $start,
               $end,
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
            return $redis->zCount($key, $start, $end);
        }, $connectionName);

        // handle exception
        $command = "ZCOUNT {$key} {$start} {$end}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 有序集合中对指定成员的分数加上增量 $value
     *
     * Increments the score of a member from a sorted set by a given amount.
     *
     * @param string          $key
     * @param float|int|mixed $value          (double) value that will be added to the member's score
     * @param string|mixed    $member
     * @param int             $serializeType  [optional]
     * @param int|null        $dbIndex        [optional]
     * @param string          $connectionName [optional]
     *
     * @return float the new value
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zincrby
     * @example
     * <pre>
     * RedisUtil::del('key');
     * RedisUtil::zIncrBy('key', 2.5, 'member1');  // key or member1 didn't exist, so member1's score is to 0
     *                                             // before the increment and now has the value 2.5
     * RedisUtil::zIncrBy('key', 1, 'member1');    // 3.5
     * </pre>
     */
    public static function zIncrBy(
        string $key,
               $value,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member
        $waitMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $value, $waitMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zInCrBy($key, $value, $waitMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($waitMember) ? $waitMember : "\"" . addslashes($waitMember) . "\"";
        $command   = "ZINCRBY {$key} {$value} {$memberStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 计算给定的一个或多个有序集合的交集并将结果集存储在新的有序集合 output 中
     *
     * Creates an intersection of sorted sets given in second argument.
     * The result of the union will be stored in the sorted set defined by the first argument.
     * The third optional argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted set
     * before applying the aggregation. The forth argument defines the AGGREGATE option which
     * specify how the results of the union are aggregated.
     *
     * @param string   $output
     * @param array    $zSetKeys
     * @param array    $weights
     * @param string   $aggregateFunction Either "SUM", "MIN", or "MAX":
     *                                    defines the behaviour to use on duplicate entries during the zInterStore.
     * @param int|null $dbIndex           [optional]
     * @param string   $connectionName    [optional]
     *
     * @return int The number of values in the new sorted set.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zinterstore
     * @example
     * <pre>
     * RedisUtil::del('k1');
     * RedisUtil::del('k2');
     * RedisUtil::del('k3');
     *
     * RedisUtil::del('ko1');
     * RedisUtil::del('ko2');
     * RedisUtil::del('ko3');
     * RedisUtil::del('ko4');
     *
     * RedisUtil::zAdd('k1', 0, 'val0');
     * RedisUtil::zAdd('k1', 1, 'val1');
     * RedisUtil::zAdd('k1', 3, 'val3');
     *
     * RedisUtil::zAdd('k2', 2, 'val1');
     * RedisUtil::zAdd('k2', 3, 'val3');
     *
     * RedisUtil::zInterStore('ko1', array('k1', 'k2'));               // 2, 'ko1' => array('val1', 'val3')
     * RedisUtil::zInterStore('ko2', array('k1', 'k2'), array(1, 1));  // 2, 'ko2' => array('val1', 'val3')
     *
     * // Weighted zInterStore
     * RedisUtil::zInterStore('ko3', array('k1', 'k2'), array(1, 5), 'min'); // 2, 'ko3' => array('val1', 'val3')
     * RedisUtil::zInterStore('ko4', array('k1', 'k2'), array(1, 5), 'max'); // 2, 'ko4' => array('val3', 'val1')
     * </pre>
     */
    public static function zInterStore(
        string $output,
        array  $zSetKeys,
        array  $weights = [],
        string $aggregateFunction = 'SUM',
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($output, $connectionName);
        $waitZSetKeys = [];
        foreach ($zSetKeys as $key) {
            $waitZSetKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($output, $waitZSetKeys, $weights, $aggregateFunction, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zInTerStore($output, $waitZSetKeys, $weights, $aggregateFunction);
        }, $connectionName);

        // handle exception
        $waitZSetKeysNum = count($waitZSetKeys);
        $waitZSetKeysStr = "\"" . join("\" \"", $waitZSetKeys) . "\"";
        $command         = "ZINTERSTORE {$output} {$waitZSetKeysNum} {$waitZSetKeysStr}";
        if ($weights) {
            $weightsStr = join(' ', $weights);
            $command    .= " WEIGHTS {$weightsStr}";
        }
        $command .= " AGGREGATE {$aggregateFunction}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 在有序集合中计算指定字典区间内成员数量
     *
     * @param string   $key
     * @param          $min
     * @param          $max
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int
     *
     * @throws Exception\RedisException
     *
     * @example
     * @link https://redis.io/commands/zlexcount
     * @example
     * <pre>
     * RedisUtil::zAdd('k1');
     * RedisUtil::del('k2');
     * RedisUtil::del('k3');
     *
     * RedisUtil::del('ko1');
     * RedisUtil::del('ko2');
     * RedisUtil::del('ko3');
     * RedisUtil::del('ko4');
     *
     * RedisUtil::zAdd('k1', 0, 'val0');
     * RedisUtil::zAdd('k1', 1, 'val1');
     * </pre>
     */
    public static function zLexCount(
        string $key,
               $min,
               $max,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zLexCount($key, $min, $max);
        }, $connectionName);

        // handle exception
        $command = "ZLEXCOUNT {$key} {$min} {$max}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 通过索引区间返回有序集合指定区间内的成员
     *
     * Returns a range of elements from the ordered set stored at the specified key,
     * with values in the range [start, end]. start and stop are interpreted as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     *
     * @param string           $key
     * @param int|string|mixed $min
     * @param int|string|mixed $max
     * @param bool             $withScores
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return array Array containing the values in specified range.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrange
     * @example
     * <pre>
     * RedisUtil::zAdd('key1', 0, 'val0');
     * RedisUtil::zAdd('key1', 2, 'val2');
     * RedisUtil::zAdd('key1', 10, 'val10');
     * RedisUtil::zRange('key1', 0, -1); // array('val0', 'val2', 'val10')
     * // with scores
     * RedisUtil::zRange('key1', 0, -1, true); // array('val0' => 0, 'val2' => 2, 'val10' => 10)
     * </pre>
     */
    public static function zRange(
        string $key,
               $min,
               $max,
        bool   $withScores = false,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $withScores, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRange($key, $min, $max, $withScores);
        }, $connectionName);

        // handle exception
        $command = "ZRANGE {$key} {$min} {$max}";
        if ($withScores) {
            $command .= " WITHSCORES";
        }
        self::handleException($command, $connectionName, $result);

        if ($result && is_array($result)) {
            $lastResult = [];
            foreach ($result as $itemIndex => $itemValue) {
                if ($withScores) {
                    $itemIndex           = (string)$itemIndex;
                    $member              = self::unSerialize($itemIndex, $serializeType);
                    $lastResult[$member] = $itemValue;
                } else {
                    $lastResult[] = self::unSerialize($itemValue, $serializeType);
                }
            }
            $result = $lastResult;
        }

        return $result;
    }

    /**
     * 通过字典区间返回有序集合的成员
     *
     * Returns a lexigraphical range of members in a sorted set, assuming the members have the same score. The
     * min and max values are required to start with '(' (exclusive), '[' (inclusive), or be exactly the values
     * '-' (negative inf) or '+' (positive inf).  The command must be called with either three *or* five
     * arguments or will return FALSE.
     *
     * @param string           $key            The ZSET you wish to run against.
     * @param int|string|mixed $min            The minimum alphanumeric value you wish to get.
     * @param int|string|mixed $max            The maximum alphanumeric value you wish to get.
     * @param int|null         $offset         Optional argument if you wish to start somewhere other than the first element.
     * @param int|null         $limit          Optional argument if you wish to limit the number of elements returned.
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return array|false Array containing the values in the specified range.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrangebylex
     * @example
     * <pre>
     * foreach (array('a', 'b', 'c', 'd', 'e', 'f', 'g') as $char) {
     *     RedisUtil::zAdd('key', $char);
     * }
     *
     * RedisUtil::zRangeByLex('key', '-', '[c'); // array('a', 'b', 'c')
     * RedisUtil::zRangeByLex('key', '-', '(c'); // array('a', 'b')
     * RedisUtil::zRangeByLex('key', '[b', '[c'); // array('b', 'c')
     * </pre>
     */
    public static function zRangeByLex(
        string $key,
               $min,
               $max,
        int    $offset = null,
        int    $limit = null,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $offset, $limit, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            $data = [];
            if ($offset) {
                $data[] = $offset;
            }
            if ($limit) {
                $data[] = $limit;
            }
            return $redis->zRangeByLex($key, $min, $max, ...$data);
        }, $connectionName);

        // handle exception
        $command = "ZRANGEBYLEX {$key} {$min} {$max}";
        if ($offset || $limit) {
            $command .= " LIMIT";
            if ($offset) {
                $command .= " {$offset}";
            }
            if ($limit) {
                $command .= " {$limit}";
            }
        }
        self::handleException($command, $connectionName, $result);

        if ($result && is_array($result)) {
            $lastResult = [];
            foreach ($result as $item) {
                if ($item) {
                    $itemValue = self::unSerialize($item, $serializeType);
                } else {
                    $itemValue = $item;
                }
                $lastResult[] = $itemValue;
            }
            $result = $lastResult;
        }

        return $result;
    }

    /**
     * 通过分数返回有序集合指定区间内的成员
     *
     * Returns the elements of the sorted set stored at the specified key which have scores in the
     * range [start,end]. Adding a parenthesis before start or end excludes it from the range.
     * +inf and -inf are also valid limits.
     *
     * zRevRangeByScore returns the same items in reverse order, when the start and end parameters are swapped.
     *
     * @param string           $key
     * @param int|string|mixed $min
     * @param int|string|mixed $max
     * @param array            $options        [optional] Two options are available:
     *                                         - withScores => true,
     *                                         - and limit => array($offset, $count)
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return array Array containing the values in specified range.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrangebyscore
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 0, 'val0');
     * RedisUtil::zAdd('key', 2, 'val2');
     * RedisUtil::zAdd('key', 10, 'val10');
     * RedisUtil::zRangeByScore('key', 0, 3);                                          // array('val0', 'val2')
     * RedisUtil::zRangeByScore('key', 0, 3, array('withScores' => true);              // array('val0' => 0, 'val2' => 2)
     * RedisUtil::zRangeByScore('key', 0, 3, array('limit' => array(1, 1));                        // array('val2')
     * RedisUtil::zRangeByScore('key', 0, 3, array('withScores' => true, 'limit' => array(1, 1));  // array('val2' => 2)
     * </pre>
     */
    public static function zRangeByScore(
        string $key,
               $min,
               $max,
        array  $options = array(),
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $options, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRangeByScore($key, $min, $max, $options);
        }, $connectionName);

        $isWithScores = false;

        // handle exception
        $command = "ZRANGEBYSCORE {$key} {$min} {$max}";
        // withscores
        if (isset($options['withScores']) && $options['withScores']) {
            $isWithScores = true;
            $command      .= " WITHSCORES";
        }
        // limit array
        if (isset($options['limit']) && $options['limit']) {
            $command    .= " LIMIT";
            $limitArray = $options['limit'];
            // offset
            if (isset($limitArray[0])) {
                $command .= " {$limitArray[0]}";
            }
            // limit
            if (isset($limitArray[1])) {
                $command .= " {$limitArray[1]}";
            }
        }
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result && is_array($result)) {
            foreach ($result as $itemIndex => $itemValue) {
                if ($isWithScores) {
                    $member              = self::unSerialize($itemIndex, $serializeType);
                    $lastResult[$member] = $itemValue;
                } else {
                    $lastResult[] = self::unSerialize($itemValue, $serializeType);
                }
            }
        }

        return $lastResult;
    }

    /**
     * 返回有序集合中指定成员的排名
     *
     * Returns the rank of a given member in the specified sorted set, starting at 0 for the item
     * with the smallest score. zRevRank starts at 0 for the item with the largest score.
     *
     * @param string       $key
     * @param string|mixed $member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|false the item's rank, or false if key or member is not exists
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrank
     * @example
     * <pre>
     * RedisUtil::del('key');
     * RedisUtil::zAdd('key', 1, 'one');
     * RedisUtil::zAdd('key', 2, 'two');
     * RedisUtil::zRank('key', 'one');     // 0
     * RedisUtil::zRank('key', 'two');     // 1
     * RedisUtil::zRevRank('key', 'one');  // 1
     * RedisUtil::zRevRank('key', 'two');  // 0
     * </pre>
     */
    public static function zRank(
        string $key,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member
        $waitMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRank($key, $waitMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($waitMember) ? $waitMember : "\"" . addslashes($waitMember) . "\"";
        $command   = "ZRANK {$key} {$memberStr}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 移除有序集合中的一个成员
     *
     * Delete a specified member from the ordered set.
     *
     * @param string       $key
     * @param string|mixed $member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int Number of deleted values
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrem
     * @example
     * <pre>
     * RedisUtil::zAdd('z', 1, 'v1', 2, 'v2', 3, 'v3', 4, 'v4' );  // int(4)
     * RedisUtil::zRem('z', 'v2');                                 // int(1)
     * var_dump( RedisUtil::zRange('z', 0, -1) );
     * //// Output:
     * // array(2) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v4"
     * // }
     * </pre>
     */
    public static function zRem(
        string $key,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member
        $waitMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRem($key, $waitMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($waitMember) ? $waitMember : "\"" . addslashes($waitMember) . "\"";
        $command   = "ZREM {$key} {$memberStr}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移除有序集合中的一个或多个成员
     *
     * Deletes many specified member from the ordered set.
     *
     * @param string   $key
     * @param array    $members
     * @param int      $serializeType  [optional]
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of deleted values
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrem
     * @example
     * <pre>
     * RedisUtil::zAdd('z', 1, 'v1', 2, 'v2', 3, 'v3', 4, 'v4' );  // int(2)
     * RedisUtil::zRem('z', 'v2', 'v3');                           // int(2)
     * var_dump( RedisUtil::zRange('z', 0, -1) );
     * //// Output:
     * // array(2) {
     * //   [0]=> string(2) "v1"
     * //   [1]=> string(2) "v4"
     * // }
     * </pre>
     */
    public static function zRems(
        string $key,
        array  $members,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理members
        $waitMembers = [];
        foreach ($members as $member) {
            $waitMembers[] = self::serialize($member, $serializeType);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMembers, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRem($key, ...$waitMembers);
        }, $connectionName);

        // handle exception
        $membersStrArr = [];
        foreach ($waitMembers as $item) {
            $membersStrArr[] = is_numeric($item) ? $item : "\"" . addslashes($item) . "\"";
        }
        $command = "ZREM {$key} " . join(' ', $membersStrArr);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移除有序集合中给定的字典区间的所有成员
     *
     * @param string   $key
     * @param          $min
     * @param          $max
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zremrangebylex
     * @example
     * <pre>
     * RedisUtil::zAdds("myzset", [0, "aaaa", 0, "b", 0, "c", 0, "d", 0, "e"]); // 5
     * RedisUtil::zAdds("myzset", [0, "foo", 0, "zap", 0, "zip", 0, "ALPHA", 0, "alpha"]); // 5
     * RedisUtil::zRange("myzset", 0, -1);
     * RedisUtil::zRemRangeByLex("myzset", "[alpha", "[omega"); // 6
     * RedisUtil::zRange("myzset", 0, -1);
     * </pre>
     */
    public static function zRemRangeByLex(
        string $key,
               $min,
               $max,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRemRangeByLex($key, $min, $max);
        }, $connectionName);

        // handle exception
        $command = "ZREMRANGEBYLEX {$key} {$min} {$max}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移除有序集合中给定的排名区间的所有成员
     *
     * Deletes the elements of the sorted set stored at the specified key which have rank in the range [start,end].
     *
     * @param string   $key
     * @param int      $start
     * @param int      $stop
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int The number of values deleted from the sorted set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zremrangebyrank
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 1, 'one');
     * RedisUtil::zAdd('key', 2, 'two');
     * RedisUtil::zAdd('key', 3, 'three');
     * RedisUtil::zRemRangeByRank('key', 0, 1); // 2
     * RedisUtil::zRange('key', 0, -1, true); // array('three' => 3)
     * </pre>
     */
    public static function zRemRangeByRank(
        string $key,
        int    $start,
        int    $stop,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $stop, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRemRangeByRank($key, $start, $stop);
        }, $connectionName);

        // handle exception
        $command = "ZREMRANGEBYRANK {$key} {$start} {$stop}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移除有序集合中给定的分数区间的所有成员
     *
     * Deletes the elements of the sorted set stored at the specified key which have scores in the range [start,end].
     *
     * @param string       $key
     * @param float|string $min            double or "+inf" or "-inf" string
     * @param float|string $max            double or "+inf" or "-inf" string
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int The number of values deleted from the sorted set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zremrangebyscore
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 0, 'val0');
     * RedisUtil::zAdd('key', 2, 'val2');
     * RedisUtil::zAdd('key', 10, 'val10');
     * RedisUtil::zRemRangeByScore('key', 0, 3); // 2
     * </pre>
     */
    public static function zRemRangeByScore(
        string $key,
               $min,
               $max,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $min, $max, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRemRangeByScore($key, $min, $max);
        }, $connectionName);

        // handle exception
        $command = "ZREMRANGEBYSCORE {$key} {$min} {$max}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回有序集合中指定区间内的成员，通过索引，分数从高到低
     *
     * Returns the elements of the sorted set stored at the specified key in the range [start, end]
     * in reverse order. start and stop are interpretated as zero-based indices:
     * 0 the first element,
     * 1 the second ...
     * -1 the last element,
     * -2 the penultimate ...
     *
     * @param string    $key
     * @param int|mixed $start
     * @param int|mixed $stop
     * @param bool      $withScore
     * @param int|null  $dbIndex        [optional]
     * @param string    $connectionName [optional]
     *
     * @return array Array containing the values in specified range.
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zrevrange
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 0, 'val0');
     * RedisUtil::zAdd('key', 2, 'val2');
     * RedisUtil::zAdd('key', 10, 'val10');
     * RedisUtil::zRevRange('key', 0, -1); // array('val10', 'val2', 'val0')
     *
     * // with scores
     * RedisUtil::zRevRange('key', 0, -1, true); // array('val10' => 10, 'val2' => 2, 'val0' => 0)
     * </pre>
     */
    public static function zRevRange(
        string $key,
               $start,
               $stop,
        bool   $withScore = false,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $start, $stop, $withScore, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRevRange($key, $start, $stop, $withScore);
        }, $connectionName);

        // handle exception
        $command = "ZREVRANGE {$key} {$start} {$stop}";
        if ($withScore) {
            $command .= ' WITHSCORES';
        }
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回有序集中指定分数区间内的成员，分数从高到低排序
     *
     * @param string           $key
     * @param int|string|mixed $max
     * @param int|string|mixed $min
     * @param array            $options        [optional]
     * @param int              $serializeType  [optional]
     * @param int|null         $dbIndex        [optional]
     * @param string           $connectionName [optional]
     *
     * @return array
     *
     * @throws Exception\RedisException
     *
     * @see  zRangeByScore()
     *
     * @link https://redis.io/commands/zrevrangebyscore
     */
    public static function zRevRangeByScore(
        string $key,
               $max,
               $min,
        array  $options = array(),
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $max, $min, $options, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRevRangeByScore($key, $max, $min, $options);
        }, $connectionName);

        $isWithScores = false;

        // handle exception
        $command = "ZREVRANGEBYSCORE {$key} {$max} {$min}";
        // withscores
        if (isset($options['withScores']) && $options['withScores']) {
            $isWithScores = true;
            $command      .= " WITHSCORES";
        }
        // limit array
        if (isset($options['limit']) && $options['limit']) {
            $command    .= " LIMIT";
            $limitArray = $options['limit'];
            // offset
            if (isset($limitArray[0])) {
                $command .= " {$limitArray[0]}";
            }
            // limit
            if (isset($limitArray[1])) {
                $command .= " {$limitArray[1]}";
            }
        }
        self::handleException($command, $connectionName, $result);

        $lastResult = [];
        if ($result && is_array($result)) {
            foreach ($result as $itemIndex => $itemValue) {
                if ($isWithScores) {
                    $member              = self::unSerialize($itemIndex, $serializeType);
                    $lastResult[$member] = $itemValue;
                } else {
                    $lastResult[] = self::unSerialize($itemValue, $serializeType);
                }
            }
        }

        return $lastResult;
    }

    /**
     * 返回有序集合中指定成员的排名，有序集合成员按分数值递减(从大到小)排序
     *
     * @param string       $key
     * @param string|mixed $member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return int|false the item's score, false - if key or member is not exists
     *
     * @throws Exception\RedisException
     *
     * @see  zRank()
     * @link https://redis.io/commands/zrevrank
     */
    public static function zRevRank(
        string $key,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member
        $waitMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zRevRank($key, $waitMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($waitMember) ? $waitMember : "\"" . addslashes($waitMember) . "\"";
        $command   = "ZREVRANK {$key} {$memberStr}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 返回有序集中，给定成员的分数值
     *
     * Returns the score of a given member in the specified sorted set.
     *
     * @param string       $key
     * @param string|mixed $member
     * @param int          $serializeType  [optional]
     * @param int|null     $dbIndex        [optional]
     * @param string       $connectionName [optional]
     *
     * @return float|bool false if member or key not exists
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zscore
     * @example
     * <pre>
     * RedisUtil::zAdd('key', 2.5, 'val2');
     * RedisUtil::zScore('key', 'val2'); // 2.5
     * </pre>
     */
    public static function zScore(
        string $key,
               $member,
        int    $serializeType = RedisConfig::SERIALIZE_NONE,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        // 序列化处理member
        $waitMember = self::serialize($member, $serializeType);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $waitMember, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->zScore($key, $waitMember);
        }, $connectionName);

        // handle exception
        $memberStr = is_numeric($waitMember) ? $waitMember : "\"" . addslashes($waitMember) . "\"";
        $command   = "ZSCORE {$key} {$memberStr}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            $result = false;
        }

        return $result;
    }

    /**
     * 计算给定的一个或多个有序集合的并集，并存储在新的 output 键中
     *
     * Creates an union of sorted sets given in second argument.
     * The result of the union will be stored in the sorted set defined by the first argument.
     * The third optionnel argument defines weights to apply to the sorted sets in input.
     * In this case, the weights will be multiplied by the score of each element in the sorted set
     * before applying the aggregation. The forth argument defines the AGGREGATE option which
     * specify how the results of the union are aggregated.
     *
     * @param string     $output
     * @param string[]   $zSetKeys
     * @param null|array $weights
     * @param string     $aggregateFunction Either "SUM", "MIN", or "MAX": defines the behaviour to use on
     *                                      duplicate entries during the zUnionStore
     * @param int|null   $dbIndex           [optional]
     * @param string     $connectionName    [optional]
     *
     * @return int The number of values in the new sorted set
     *
     * @throws Exception\RedisException
     *
     * @link https://redis.io/commands/zunionstore
     * @example
     * <pre>
     * RedisUtil::del('k1');
     * RedisUtil::del('k2');
     * RedisUtil::del('k3');
     * RedisUtil::del('ko1');
     * RedisUtil::del('ko2');
     * RedisUtil::del('ko3');
     *
     * RedisUtil::zAdd('k1', 0, 'val0');
     * RedisUtil::zAdd('k1', 1, 'val1');
     *
     * RedisUtil::zAdd('k2', 2, 'val2');
     * RedisUtil::zAdd('k2', 3, 'val3');
     *
     * RedisUtil::zUnionStore('ko1', array('k1', 'k2')); // 4, 'ko1' => array('val0', 'val1', 'val2', 'val3')
     *
     * // Weighted zUnionStore
     * RedisUtil::zUnionStore('ko2', array('k1', 'k2'), array(1, 1)); // 4, 'ko2' => array('val0', 'val1', 'val2', 'val3')
     * RedisUtil::zUnionStore('ko3', array('k1', 'k2'), array(5, 1)); // 4, 'ko3' => array('val0', 'val2', 'val3', 'val1')
     * </pre>
     */
    public static function zUnionStore(
        string $output,
        array  $zSetKeys,
        ?array $weights = null,
        string $aggregateFunction = 'SUM',
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($output, $connectionName);
        $waitZSetKeys = [];
        foreach ($zSetKeys as $key) {
            $waitZSetKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($output, $waitZSetKeys, $weights, $aggregateFunction, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            $weightsParam = [];
            if ($weights) {
                $weightsParam = $weights;
            }
            return $redis->zUnionStore($output, $waitZSetKeys, $weightsParam, $aggregateFunction);
        }, $connectionName);

        // handle exception
        $waitZSetKeysNum = count($waitZSetKeys);
        $waitZSetKeysStr = "\"" . join("\" \"", $waitZSetKeys) . "\"";
        $command         = "ZUNIONSTORE {$output} {$waitZSetKeysNum} {$waitZSetKeysStr}";
        if ($weights) {
            $weightsStr = join(' ', $weights);
            $command    .= " WEIGHTS {$weightsStr}";
        }
        $command .= " AGGREGATE {$aggregateFunction}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
