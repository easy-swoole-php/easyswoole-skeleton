<?php

namespace EasySwooleLib\Redis\Utility\Exception;

use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\Logger;

trait ExceptionTrait
{
    public static function handleException(string $command, string $connectionName, $result, bool $allowNull = false, bool $allowFalse = false)
    {
        $debug = config("redis.{$connectionName}.debug");
        if ($debug) {
            $commandMsg = "[REDIS][COMMAND: {$command}]";
            Logger::getInstance()->info(Color::green($commandMsg));
        }

        $error = "[Redis][Pool:{$connectionName}}][Exception:an exception was encountered while executing the command [{$command}], ";

        // false or null

        if (!$allowNull) {
            if (is_null($result)) {
                $error .= 'result is null.';
                throw new RedisException($error);
            }
        }

        if (!$allowFalse) {
            if ($result === false) {
                $error .= 'result is false.';
                throw new RedisException($error);
            }
        }
    }
}
