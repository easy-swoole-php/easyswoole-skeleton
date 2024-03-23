<?php

namespace EasySwooleLib\Redis\Utility;

use EasySwooleLib\Redis\Utility\Exception\RedisException;
use EasySwoole\Redis\Redis;
use EasySwoole\RedisPool\RedisPool;

trait KeyTrait
{
    /**
     * 删除一个键
     * Remove specified key.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of keys deleted 删除键的个数
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/del
     * @example
     * <pre>
     * RedisUtil::set('key1', 'val1');
     * RedisUtil::set('key2', 'val2');
     *
     * RedisUtil::del('key1'); // return 1
     * </pre>
     */
    public static function del(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT): int
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->del($key);
        }, $connectionName);

        // handle exception
        $command = "DEL {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 删除多个键
     * Remove specified keys.
     *
     * @param array    $keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of keys deleted 删除键的个数
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/del
     * @example
     * <pre>
     * RedisUtil::set('key1', 'val1');
     * RedisUtil::set('key2', 'val2');
     *
     * RedisUtil::dels(['key1', 'key2']); // return 2
     * </pre>
     */
    public static function dels(array $keys, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT): int
    {
        // 处理key前缀
        $waitKeys = [];
        foreach ($keys as $key) {
            // 处理key前缀
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->del(...$waitKeys);
        }, $connectionName);

        // handle exception
        $command = "DEL " . join(" ", $waitKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 序列化
     * Dump a key out of a redis database, the value of which can later be passed into redis using the RESTORE command.
     * The data that comes out of DUMP is a binary representation of the key as Redis stores it.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string|false The Redis encoded value of the key, or FALSE if the key doesn't exist
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/dump
     * @example
     * <pre>
     * RedisUtil::set('foo', 'bar');
     * $val = RedisUtil::dump('foo'); // $val will be the Redis encoded key value
     * </pre>
     */
    public static function dump(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->dump($key);
        }, $connectionName);

        // handle exception
        $command   = "DUMP {$key}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if (is_null($result)) {
            return false;
        }

        return $result;
    }

    /**
     * 查询某个键是否存在
     * Verify if the specified key exists
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int The number of keys tested that do exist
     *
     * @throws RedisException
     *
     * @since >= 4.0 Returned int, if < 4.0 returned bool
     *
     * @link  https://redis.io/commands/exists
     * @example
     * <pre>
     * RedisUtil::exists('foo'); // 1
     * </pre>
     */
    public static function exists(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->exists($key);
        }, $connectionName);

        // handle exception
        $command = "EXISTS {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 查询多个键是否存在
     * Verify if the specified keys exists
     *
     * @param array    $keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool The number of keys tested that do exist
     *
     * @throws RedisException
     *
     * @since >= 4.0 Returned int, if < 4.0 returned bool
     *
     * @link  https://redis.io/commands/exists
     * @example
     * <pre>
     * RedisUtil::mset(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);
     * RedisUtil::existsMultiple(['foo', 'bar', 'baz']); // 3
     * </pre>
     */
    public static function existsMultiple(array $keys, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        $waitKeys = [];
        foreach ($keys as $key) {
            // 处理key前缀
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            array_unshift($waitKeys, 'EXISTS');
            return $redis->rawCommand($waitKeys);
        }, $connectionName);

        // handle exception
        $command = "EXISTS " . join(" ", $waitKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 给某个键设置过期时间(秒)
     * Sets an expiration date (a timeout) on an item
     *
     * @param string   $key            The key that will disappear
     * @param int      $ttl            The key's remaining Time To Live, in seconds
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/expire
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * RedisUtil::expire('x', 3);  // x will disappear in 3 seconds.
     * \Co::sleep(5);              // wait 5 seconds
     * RedisUtil::get('x');        // will return `FALSE`, as 'x' has expired.
     * </pre>
     */
    public static function expire(string $key, int $ttl = 60, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $ttl, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->expire($key, $ttl);
        }, $connectionName);

        // handle exception
        $command   = "EXPIRE {$key} {$ttl}";
        $allowNull = true;
        self::handleException($command, $connectionName, $result, $allowNull);

        if ($result === 1) {
            return true;
        }

        if ($result === 0 || is_null($result)) {
            return false;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 以UNIX时间戳格式设置键的过期时间
     * Sets an expiration date (a timestamp) on an item.
     *
     * @param string   $key            The key that will disappear.
     * @param int      $timestamp      Unix timestamp. The key's date of death, in seconds from Epoch time.
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/expireat
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * $now = time();                      // current timestamp
     * RedisUtil::expireAt('x', $now + 3); // x will disappear in 3 seconds.
     * \Co::sleep(5);                      // wait 5 seconds
     * RedisUtil::get('x');                // will return `FALSE`, as 'x' has expired.
     * </pre>
     */
    public static function expireAt(string $key, int $timestamp, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $timestamp, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->expireAt($key, $timestamp);
        }, $connectionName);

        // handle exception
        $command = "EXPIREAT {$key} {$timestamp}";
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
     * 匹配键
     * Returns the keys that match a certain pattern.
     *
     * @param string   $pattern        pattern, using '*' as a wildcard
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return array string[] The keys that match a certain pattern.
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/keys
     * @example
     * <pre>
     * $allKeys = RedisUtil::keys('*'); // all keys will match this.
     * $keyWithUserPrefix = RedisUtil::keys('user*');
     * </pre>
     */
    public static function keys(string $pattern, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($pattern, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->keys($pattern);
        }, $connectionName);

        // handle exception
        $command = "KEYS {$pattern}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 移动key
     * Moves a key to a different database.
     *
     * @param string   $key
     * @param int      $dbIndex
     * @param string   $connectionName [optional]
     * @param int|null $currentDbIndex [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/move
     * @example
     * <pre>
     * RedisUtil::select(0);       // switch to DB 0
     * RedisUtil::set('x', '42');  // write 42 to x
     * RedisUtil::move('x', 1);    // move to DB 1
     * RedisUtil::select(1);       // switch to DB 1
     * RedisUtil::get('x');        // will return 42
     * </pre>
     */
    public static function move(string $key, int $dbIndex, string $connectionName = self::DEFAULT_CONNECT, int $currentDbIndex = null)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex, $currentDbIndex) {
            if (!is_null($currentDbIndex)) {
                $redis->select($currentDbIndex);
            }
            return $redis->move($key, $dbIndex);
        }, $connectionName);

        // handle exception
        $command = "MOVE {$key} {$dbIndex}";
        self::handleException($command, $connectionName, $result);

        if ($result === 0) {
            return false;
        }

        if ($result === 1) {
            return true;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 移除key的过期时间
     * Remove the expiration timer from a key.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE if a key was removed, FALSE if the key didn’t exist or didn’t have an expiration timer.
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/persist
     * @example RedisUtil::persist('key');
     */
    public static function persist(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->persist($key);
        }, $connectionName);

        // handle exception
        $command = "PERSIST {$key}";
        self::handleException($command, $connectionName, $result);

        if ($result === 0) {
            return false;
        }

        if ($result === 1) {
            return true;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 给key设定过期时间(毫秒)
     * Sets an expiration date (a timeout in milliseconds) on an item
     *
     * @param string   $key            The key that will disappear.
     * @param int      $ttl            The key's remaining Time To Live, in milliseconds
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/pexpire
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * RedisUtil::pExpire('x', 11500); // x will disappear in 11500 milliseconds.
     * RedisUtil::ttl('x');            // 12
     * RedisUtil::pttl('x');           // 11500
     * </pre>
     */
    public static function pExpire(string $key, int $ttl, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $ttl, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->pExpire($key, $ttl);
        }, $connectionName);

        // handle exception
        $command = "PEXPIRE {$key} {$ttl}";
        self::handleException($command, $connectionName, $result);

        if ($result === 0) {
            return false;
        }

        if ($result === 1) {
            return true;
        }

        throw new RedisException('return value is invalid.');
    }

    /**
     * 返回毫秒过期时间
     * Returns a time to live left for a given key, in milliseconds.
     *
     * If the key doesn't exist, FALSE is returned.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool the time left to live in milliseconds
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/pttl
     * @example
     * <pre>
     * RedisUtil::setex('key', 123, 'test');
     * RedisUtil::pttl('key'); // int(122999)
     * </pre>
     */
    public static function pttl(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->pTTL($key);
        }, $connectionName);

        // handle exception
        $command = "PTTL {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 随机返回一个key
     * Returns a random key
     *
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string an existing key in redis
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/randomkey
     * @example
     * <pre>
     * $key = RedisUtil::randomKey();
     * $surprise = RedisUtil::get($key);  // who knows what's in there.
     * </pre>
     */
    public static function randomKey(int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        $result = RedisPool::invoke(function (Redis $redis) use ($dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->randomKey();
        }, $connectionName);

        // handle exception
        $command = "RANDOMKEY";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 修改key的名字
     * Renames a key
     *
     * @param string   $srcKey
     * @param string   $dstKey
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/rename
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * RedisUtil::rename('x', 'y');
     * RedisUtil::get('y');   // → 42
     * RedisUtil::get('x');   // → `FALSE`
     * </pre>
     */
    public static function rename(
        string $srcKey,
        string $dstKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($srcKey, $connectionName);
        self::handleKeyPrefix($dstKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($srcKey, $dstKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->rename($srcKey, $dstKey);
        }, $connectionName);

        // handle exception
        $command = "RENAME {$srcKey} {$dstKey}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * dstKey不存在时,修改srcKey名字
     * Renames a key
     *
     * Same as rename, but will not replace a key if the destination already exists.
     * This is the same behaviour as setNx.
     *
     * @param string   $srcKey
     * @param string   $dstKey
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return bool TRUE in case of success, FALSE in case of failure
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/renamenx
     * @example
     * <pre>
     * RedisUtil::set('x', '42');
     * RedisUtil::renameNx('x', 'y');
     * RedisUtil::get('y');   // → 42
     * RedisUtil::get('x');   // → `FALSE`
     * </pre>
     */
    public static function renameNx(
        string $srcKey,
        string $dstKey,
        int    $dbIndex = null,
        string $connectionName = self::DEFAULT_CONNECT
    )
    {
        // 处理key前缀
        self::handleKeyPrefix($srcKey, $connectionName);
        self::handleKeyPrefix($dstKey, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($srcKey, $dstKey, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->renameNx($srcKey, $dstKey);
        }, $connectionName);

        // handle exception
        $command = "RENAMENX {$srcKey} {$dstKey}";
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
     * 返回过期时间
     * Returns the time to live left for a given key, in seconds. If the key doesn't exist, FALSE is returned.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int|bool the time left to live in seconds
     *
     * @throws RedisException
     *
     * @link https://redis.io/commands/ttl
     * @example
     * <pre>
     * RedisUtil::setex('key', 123, 'test');
     * RedisUtil::ttl('key'); // int(123)
     * </pre>
     */
    public static function ttl(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->ttl($key);
        }, $connectionName);

        // handle exception
        $command = "TTL {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 返回key储存的数据类型
     * Returns the type of data pointed by a given key.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return string
     * Depending on the type of the data pointed by the key,
     * this method will return the following value:
     * - 'string'
     * - 'set'
     * - 'list'
     * - 'zset'
     * - 'hash'
     * - 'other'
     * - 'none'
     *
     * @throws RedisException
     *
     * @link    https://redis.io/commands/type
     * @example RedisUtil::type('key');
     */
    public static function type(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->type($key);
        }, $connectionName);

        // handle exception
        $command = "TYPE {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 非阻塞删除一个键
     * Delete a key asynchronously in another thread. Otherwise it is just as DEL, but non blocking.
     *
     * @param string   $key
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of keys unlinked.
     *
     * @throws RedisException
     *
     * @see  del()
     * @link https://redis.io/commands/unlink
     * @example
     * <pre>
     * RedisUtil::set('key1', 'val1');
     * RedisUtil::set('key2', 'val2');
     * RedisUtil::set('key3', 'val3');
     * RedisUtil::set('key4', 'val4');
     * RedisUtil::unlink('key1'); // return 1
     * </pre>
     */
    public static function unlink(string $key, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        self::handleKeyPrefix($key, $connectionName);

        $result = RedisPool::invoke(function (Redis $redis) use ($key, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->unlink($key);
        }, $connectionName);

        // handle exception
        $command = "UNLINK {$key}";
        self::handleException($command, $connectionName, $result);

        return $result;
    }

    /**
     * 非阻塞删除多个键
     * Delete many keys asynchronously in another thread. Otherwise it is just as DEL, but non blocking.
     *
     * @param string[] $keys
     * @param int|null $dbIndex        [optional]
     * @param string   $connectionName [optional]
     *
     * @return int Number of keys unlinked.
     *
     * @throws RedisException
     *
     * @see  del()
     * @link https://redis.io/commands/unlink
     * @example
     * <pre>
     * RedisUtil::set('key1', 'val1');
     * RedisUtil::set('key2', 'val2');
     * RedisUtil::set('key3', 'val3');
     * RedisUtil::set('key4', 'val4');
     * RedisUtil::unlinks(['key1', 'key2']); // return 2
     * </pre>
     */
    public static function unlinks(array $keys, int $dbIndex = null, string $connectionName = self::DEFAULT_CONNECT)
    {
        // 处理key前缀
        $waitKeys = [];
        foreach ($keys as $key) {
            $waitKeys[] = self::handleKeyPrefix($key, $connectionName);
        }

        $result = RedisPool::invoke(function (Redis $redis) use ($waitKeys, $dbIndex) {
            if (!is_null($dbIndex)) {
                $redis->select($dbIndex);
            }
            return $redis->unlink(...$waitKeys);
        }, $connectionName);

        // handle exception
        $command = "UNLINK " . join(' ', $waitKeys);
        self::handleException($command, $connectionName, $result);

        return $result;
    }
}
