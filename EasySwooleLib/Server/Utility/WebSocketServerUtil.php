<?php

namespace EasySwooleLib\Server\Utility;

use EasySwoole\Command\Color;
use EasySwoole\EasySwoole\ServerManager;
use EasySwooleLib\Logger\Log;
use Swoole\WebSocket\Frame;

class WebSocketServerUtil
{
    /**
     * 发送给某个客户端
     * Send a message to the specified user
     *
     * @param int          $receiver The receiver fd 接收者的 fd
     * @param Frame|string $data     要发送的消息数据
     * @param int          $opcode
     * @param bool         $finish
     *
     * @return bool
     */
    public static function sendTo(
        int $receiver,
        $data,
        int $opcode = WEBSOCKET_OPCODE_TEXT,
        bool $finish = true
    ): bool
    {
        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        if (!$swooleServer->isEstablished($receiver)) {
            return false;
        }

        // 记录日志
        $fromUser = 'SYSTEM';
        $log = "[WebSocket](private)The #{$fromUser} send message to the user #{$receiver}. Opcode: $opcode Data: {$data}";
        Log::info(Color::green($log));

        return $swooleServer->push($receiver, $data, $opcode, $finish);
    }

    /**
     * 发送给指定的一些客户端
     *
     * @param Frame|string $data      要发送的消息数据
     * @param array        $receivers 指定的接收者 fd 列表
     * @param array        $excluded  排除的接收者 fd 列表
     * @param int          $pageSize
     * @param int          $opcode
     *
     *
     * 当 $receivers 有数据时，将会忽略 $excluded。 此时就是将消息指定的发给这些接收者
     * 当 $receivers 为空时
     *   若 $excluded 有值，将会给除了这些人之外的发送消息
     *   若 $excluded 为空，相当于给所有人发消息
     *
     * @return int
     */
    public static function sendToSome(
        $data,
        array $receivers = [],
        array $excluded = [],
        int $pageSize = 50,
        int $opcode = WEBSOCKET_OPCODE_TEXT
    ): int
    {
        $count = 0;
        $fromUser = 'SYSTEM';

        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        // To receivers
        if ($receivers) {
            // 记录日志
            $log = "[WebSocket](broadcast)The #{$fromUser} gave some specified user sending a message. Opcode: $opcode Data: {$data}";
            Log::info(Color::green($log));

            foreach ($receivers as $fd) {
                if ($fd && $swooleServer->isEstablished((int)$fd)) {
                    $count++;
                    $swooleServer->push($fd, $data, $opcode);
                }
            }

            return $count;
        }

        // To special users
        $excluded = $excluded ? (array)array_flip($excluded) : [];

        // 记录日志
        $log = "[WebSocket](broadcast)The #{$fromUser} send the message to everyone except some people. Opcode: $opcode Data: {$data}";
        Log::info(Color::green($log));

        return self::pageEach(function (int $fd) use ($excluded, $data, $opcode, $swooleServer) {
            if (isset($excluded[$fd])) {
                return;
            }

            $swooleServer->push($fd, $data, $opcode);
        }, $pageSize);
    }

    /**
     * @param Frame|string $data
     * @param array        $receivers
     * @param array        $excluded
     * @param int          $sender
     * @param int          $opcode
     *
     * @return int
     */
    public static function broadcast(
        $data,
        array $receivers = [],
        array $excluded = [],
        int $sender = 0,
        int $opcode = WEBSOCKET_OPCODE_TEXT
    ): int
    {
        // Only one receiver
        if (1 === count($receivers)) {
            $ok = self::sendTo((int)array_shift($receivers), $data, $opcode);
            return $ok ? 1 : 0;
        }

        // Excepted itself
        if ($sender) {
            $excluded[] = $sender;
        }

        // To all
        if (!$excluded && !$receivers) {
            return self::sendToAll($data, 50, $opcode);
        }

        // To some
        return self::sendToSome($data, $receivers, $excluded, 50, $opcode);
    }

    /**
     * Send message to all connections
     *
     * @param string|Frame $data
     * @param int          $sender
     * @param int          $pageSize
     * @param int          $opcode see WEBSOCKET_OPCODE_*
     *
     * @return int
     */
    public static function sendToAll($data, int $pageSize = 50, int $opcode = WEBSOCKET_OPCODE_TEXT)
    {
        // record log
        $fromUser = 'SYSTEM';
        $log = "[WebSocket](broadcast)The #{$fromUser} send a message to all users. Opcode: $opcode Data: {$data}";
        Log::info(Color::green($log));

        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        return self::pageEach(function (int $fd) use ($data, $opcode, $swooleServer) {
            $swooleServer->push($fd, $data, $opcode);
        }, $pageSize);
    }

    /**
     * Pagination traverse all valid WS connection
     *
     * @param callable $handler
     * @param int      $pageSize
     *
     * @return int
     */
    public static function pageEach(callable $handler, int $pageSize = 50): int
    {
        $count = $startFd = 0;

        $swooleServer = ServerManager::getInstance()->getSwooleServer();

        while (true) {
            $fdList = (array)$swooleServer->getClientList($startFd, $pageSize);



            if (($num = count($fdList)) === 0) {
                break;
            }

            $count += $num;

            /** @var $fdList array */
            foreach ($fdList as $fd) {
                if ($fd > 0 && $swooleServer->isEstablished($fd)) {
                    $handler($fd);
                }
            }

            // It's last page.
            if ($num < $pageSize) {
                break;
            }

            // Get start fd for next page.
            $startFd = end($fdList);
        }

        return $count;
    }
}
