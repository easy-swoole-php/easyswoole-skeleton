<?php

namespace EasySwooleLib\Event;

use EasySwoole\Command\CommandManager;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\SysConst;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\Queue\Driver\RedisQueue;
use EasySwoole\Redis\Config as RedisConfig;
use EasySwoole\Socket\Dispatcher;
use EasySwooleLib\AppMode;
use EasySwooleLib\Cli\ArgvArgcParser;
use EasySwooleLib\Config\ConfigManager;
use EasySwooleLib\Context\ContextUtil;
use EasySwooleLib\Crontab\EasySwooleCrontabManager;
use EasySwooleLib\Database\Mysql\EasySwooleMysqlPool;
use EasySwooleLib\Enums\AppEnum;
use EasySwooleLib\HotReload\FileWatcher;
use EasySwooleLib\Process\EasySwooleProcessManager;
use EasySwooleLib\Redis\EasySwooleRedisPool;
use EasySwooleLib\Server\SubServerConfig;
use EasySwooleLib\Server\SubServerEnum;
use EasySwoole\Socket\Config as SocketConfig;
use EasySwooleLib\Server\Tcp\TcpController\Protocol;
use EasySwooleLib\Server\Tcp\TcpController\TcpParser;
use EasySwooleLib\Server\Udp\UdpController\UdpParser;
use EasySwooleLib\Server\WebSocket\WebSocketController\WebSocketParser;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Server as SwooleServer;
use Swoole\Websocket\Server as SwooleWebSocketServer;
use Swoole\Websocket\Frame as SwooleWebSocketFrame;
use RuntimeException;
use function config;

class GlobalEvent
{
    public static function setTimezone()
    {
        $timezone = config('app.default_timezone') ?: date_default_timezone_get();
        date_default_timezone_set($timezone);
    }

    public static function bootstrap($argc, $argv)
    {
        defined('EASYSWOOLE_SERVER') or define('EASYSWOOLE_SERVER', 1);
        defined('EASYSWOOLE_WEB_SERVER') or define('EASYSWOOLE_WEB_SERVER', 2);
        defined('EASYSWOOLE_WEB_SOCKET_SERVER') or define('EASYSWOOLE_WEB_SOCKET_SERVER', 3);
        defined('EASYSWOOLE_TCP_SERVER') or define('EASYSWOOLE_TCP_SERVER', 4);
        defined('EASYSWOOLE_UDP_SERVER') or define('EASYSWOOLE_UDP_SERVER', 5);

        // init mode
        $parser = ArgvArgcParser::getInstance()->init($argc, $argv);
        $command = $parser->getCaller()->getCommand();
        $mode = $parser->getCommandManager()->getOpt('mode');
        $whiteCommands = [
            'server',
            'task',
            'crontab',
            'process',
        ];

        $isLoadConfig = false;

        if ($command && !in_array($command, $whiteCommands)) {
            if ($mode) {
                Core::getInstance()->runMode($mode);
            } else {
                $mode = AppMode::currentAppMode();
            }

            // load config
            ConfigManager::loadConfig($mode);
            $isLoadConfig = true;

            Core::getInstance()->initialize();
        }

        if (!$mode) {
            $mode = AppMode::currentAppMode();
        }

        // load config
        if (!$isLoadConfig) {
            ConfigManager::loadConfig($mode);
        }

        // set timezone
        self::setTimezone();

        if ($command === 'server') {
            !defined('IS_SERVER_COMMAND') && define('IS_SERVER_COMMAND', true);
        }

        // register command
        $commands = config('commands');

        if (empty($commands)) {
            $commands = [];
        }

        foreach ($commands as $command) {
            if (!class_exists($command)) {
                throw new RuntimeException("The class {$command} is not found.");
            }

            CommandManager::getInstance()->addCommand(new $command());
        }
    }

    public static function init()
    {
        // init trigger handler
        $triggerClass = config('trigger.handler.class');
        Di::getInstance()->set(SysConst::TRIGGER_HANDLER, new $triggerClass());

        // init http exception handler
        $httpExceptionCallable = config('exceptions.handler_callable.http');

        if (!is_callable($httpExceptionCallable)) {
            throw new RuntimeException("The config 'exceptions.handler_callable.http' is not a callable.");
        }

        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, $httpExceptionCallable);
    }

    public static function initMysqlPool()
    {
        $databases = \config('databases');

        foreach ($databases as $name => $config) {
            $enable = $config['enable'] ?? false;

            if (!$enable) {
                continue;
            }

            $poolConfig = $config['pool'] ?? [];

            if ($poolConfig) {
                $config = array_merge($config, $poolConfig);
            }

            if (!empty($config['driver'])) {
                if ($config['driver'] === 'mysql') {
                    EasySwooleMysqlPool::getInstance()->initPool($name, $config);
                }
            }
        }
    }

    public static function initRedisPool()
    {
        $redis = \config('redis');

        foreach ($redis as $name => $config) {
            $enable = $config['enable'] ?? false;

            if (!$enable) {
                continue;
            }

            $poolConfig = $config['pool'] ?? [];

            if ($poolConfig) {
                $config = array_merge($config, $poolConfig);
            }

            EasySwooleRedisPool::getInstance()->initPool($name, $config);
        }
    }

    public static function registerCrontab()
    {
        EasySwooleCrontabManager::registerCrontab();
    }

    public static function registerProcess()
    {
        EasySwooleProcessManager::registerProcess();
    }

    public static function registerQueue()
    {
        $queues = config('queue');
        foreach ($queues as $index => $queue) {

            if (!$queue || !is_array($queue)) {
                continue;
            }

            $index = $index + 1;
            $enable = $queue['enable'] ?? false;

            if (!$enable) {
                continue;
            }

            $queueName = $queue['queue_name'] ?? '';
            $class = $queue['queue_class'] ?? null;
            $queueDriver = $queue['queue_driver'] ?? null;
            $queueDriverName = $queue['queue_driver_name'] ?? 'default';

            if (!$class) {
                throw new RuntimeException("The queue[{$index}] configuration is missing the queue_class configuration");
            }

            if (!class_exists($class)) {
                throw new RuntimeException("{$class} class not exists.");
            }

            if (!$queueName) {
                throw new RuntimeException("The queue[{$index}] configuration is missing the queue_name configuration");
            }

            if (!$queueDriver) {
                throw new RuntimeException("The queue[{$index}] configuration is missing the queue_driver configuration");
            }

            if ($queueDriver !== 'redis') {
                throw new RuntimeException("The queue[{$index}] configuration queue_driver configuration is invalid.");
            }

            /** @var array $driverConfig */
            $driverConfig = config("redis.{$queueDriverName}");

            if (!$driverConfig) {
                throw new RuntimeException("The redis.{$queueDriverName} configuration is missed.");
            }

            $redisConfig = new RedisConfig($driverConfig);
            $driver = new RedisQueue($redisConfig, $queueName);
            $class::getInstance($driver);
        }
    }

    public static function initFileWatcher()
    {
        FileWatcher::enable();
    }

    public static function initialize()
    {
        self::setTimezone();
        self::init();
        self::initMysqlPool();
        self::initRedisPool();
        self::registerCrontab();
        self::registerProcess();
        self::registerQueue();
    }

    public static function mainServerCreate(EventRegister $register)
    {
        self::initFileWatcher();
        self::registerMainServerEvent($register);
        self::registerTcpController($register);
        self::registerUdpController($register);
        self::registerWebSocketController($register);
        self::registerSubServer($register);
    }

    public static function initHttpGlobalOnRequest(Request $request, Response $response)
    {
        ContextUtil::set(AppEnum::EASYSWOOLE_HTTP_REQUEST, $request);
        ContextUtil::set(AppEnum::EASYSWOOLE_HTTP_RESPONSE, $response);
    }

    public static function registerMainServerEvent(EventRegister $register)
    {
        $mainServerCallbacks = config('MAIN_SERVER.CALLBACKS');

        foreach ($mainServerCallbacks as $event => $callback) {
            $register->set($event, $callback);
        }
    }

    public static function registerSubServer(EventRegister $register)
    {
        $mainServerType = config('MAIN_SERVER.SERVER_TYPE');

        if ($mainServerType === EASYSWOOLE_SERVER) {
            $mainServerSockType = config('MAIN_SERVER.SOCK_TYPE');

            if ($mainServerSockType === SWOOLE_TCP) {
                $mainServerType = EASYSWOOLE_TCP_SERVER;
            } else if ($mainServerSockType === SWOOLE_UDP) {
                $mainServerType = EASYSWOOLE_UDP_SERVER;
            } else {
                throw new RuntimeException("The configuration 'MAIN_SERVER.SOCK_TYPE' is invalid.");
            }
        }

        $mainServerTypeName = SubServerEnum::getServerName($mainServerType);
        $allowSubServerTypes = SubServerEnum::getSupportSubServerTypes($mainServerType);

        if (!$allowSubServerTypes) {
            throw new RuntimeException("The configuration 'MAIN_SERVER.SERVER_TYPE' is invalid.");
        }

        $subServers = config('subServer');
        foreach ($subServers as $index => $subServer) {

            if (empty($subServer['enable'])) {
                continue;
            }

            $index = $index + 1;
            $subServerType = $subServer['server_type'] ?? null;

            if (!$subServerType) {
                throw new RuntimeException("The subServer[{$index}] configuration is missing the server_type configuration");
            }

            $subServerTypeName = SubServerEnum::getServerName($subServerType);
            if (!in_array($subServerType, $allowSubServerTypes, true)) {
                throw new RuntimeException("The subServer[{$index}] configuration is invalid. When the main server type is '{$mainServerTypeName}', the sub server type does not support '{$subServerTypeName}'.");
            }

            $subServerName = $subServer['name'] ?? $subServerTypeName . '_' . $index;
            $listenAddress = $subServer['listen_address'] ?? null;
            $port = $subServer['port'] ?? null;
            $subSwooleType = SubServerEnum::getSwooleSockType($subServerType);
            $callbacks = $subServer['callbacks'] ?? [];
            $setting = $subServer['setting'] ?? [];

            if ($subServerType === EASYSWOOLE_WEB_SOCKET_SERVER) {
                $setting['open_websocket_protocol'] = true;
            } else if ($subServerType === EASYSWOOLE_WEB_SERVER) {
                $setting['open_http_protocol'] = true;
            }

            $serverEventRegister = ServerManager::getInstance()->addServer($subServerName, $port, $subSwooleType, $listenAddress, $setting);

            // after add sub server success, record config
            $subServerRegister = [
                'port'          => $port,
                'listenAddress' => $listenAddress,
                'type'          => $subSwooleType,
                'setting'       => $setting,
                'eventRegister' => $serverEventRegister,
            ];
            SubServerConfig::getInstance()->addSubServerRegister($subServerName, $subServerRegister);

            foreach ($callbacks as $event => $callback) {
                $serverEventRegister->set($event, $callback);
            }

            switch ($subServerType) {
                case EASYSWOOLE_TCP_SERVER:
                    self::registerTcpController($serverEventRegister, false, $subServerName);
                    break;
                case EASYSWOOLE_UDP_SERVER:
                    self::registerUdpController($serverEventRegister);
                    break;
                case EASYSWOOLE_WEB_SOCKET_SERVER:
                    self::registerWebSocketController($serverEventRegister);
                    break;
                case EASYSWOOLE_WEB_SERVER:
                    self::registerHttpController($serverEventRegister);
                    break;
            }
        }
    }

    public static function registerTcpController(EventRegister $register, bool $isMainServer = true, string $subServerName = '')
    {
        if ($register->get(EventRegister::onReceive)) {
            return;
        }

        if (!$isMainServer && $subServerName) {
            Protocol::getInstance($isMainServer, $subServerName);
        }

        $config = new SocketConfig();
        $config->setType(SocketConfig::TCP);
        $config->setParser(TcpParser::class);
        $exceptionHandler = config('exceptions.handler_callable.tcp_controller_dispatcher');

        if ($exceptionHandler && is_callable($exceptionHandler)) {
            $config->setOnExceptionHandler($exceptionHandler);
        }

        $tcpDispatcher = new Dispatcher($config);

        $register->set(EventRegister::onReceive, function (SwooleServer $server, int $fd, int $reactorId, string $data) use ($tcpDispatcher) {
            $tcpDispatcher->dispatch($server, $data, $fd, $reactorId);
        });
    }

    public static function registerWebSocketController(EventRegister $register)
    {
        if ($register->get(EventRegister::onMessage)) {
            return;
        }

        $config = new SocketConfig();
        $config->setType(SocketConfig::WEB_SOCKET);
        $config->setParser(WebSocketParser::class);
        $exceptionHandler = config('exceptions.handler_callable.websocket_controller_dispatcher');

        if ($exceptionHandler && is_callable($exceptionHandler)) {
            $config->setOnExceptionHandler($exceptionHandler);
        }

        $webSocketDispatcher = new Dispatcher($config);

        $register->set(EventRegister::onMessage, function (SwooleWebSocketServer $server, SwooleWebSocketFrame $frame) use ($webSocketDispatcher) {
            $webSocketDispatcher->dispatch($server, $frame->data, $frame);
        });
    }

    public static function registerUdpController(EventRegister $register)
    {
        if ($register->get(EventRegister::onPacket)) {
            return;
        }

        $config = new SocketConfig();
        $config->setType(SocketConfig::UDP);
        $config->setParser(UdpParser::class);
        $exceptionHandler = config('exceptions.handler_callable.udp_controller_dispatcher');

        if ($exceptionHandler && is_callable($exceptionHandler)) {
            $config->setOnExceptionHandler($exceptionHandler);
        }

        $udpDispatcher = new Dispatcher($config);

        $register->set(EventRegister::onPacket, function (SwooleServer $server, string $data, array $clientInfo) use ($udpDispatcher) {
            $udpDispatcher->dispatch($server, $data, $clientInfo['server_socket'], $clientInfo['address'], $clientInfo['port']);
        });
    }

    public static function registerHttpController(EventRegister $register)
    {
        if ($register->get(EventRegister::onRequest)) {
            return;
        }

        $namespace = Di::getInstance()->get(SysConst::HTTP_CONTROLLER_NAMESPACE);
        if (empty($namespace)) {
            $namespace = 'App\\HttpController\\';
        }

        $depth = intval(Di::getInstance()->get(SysConst::HTTP_CONTROLLER_MAX_DEPTH));
        $depth = $depth > 5 ? $depth : 5;

        $httpDispatcher = \EasySwoole\EasySwoole\Http\Dispatcher::getInstance()->setNamespacePrefix($namespace)->setMaxDepth($depth);;
        // 补充HTTP_EXCEPTION_HANDLER默认回调
        $httpExceptionHandler = Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER);

        if (!is_callable($httpExceptionHandler)) {
            $httpExceptionHandler = function ($throwable, $request, $response) {
                $response->withStatus(Status::CODE_INTERNAL_SERVER_ERROR);
                $response->write(nl2br($throwable->getMessage() . "\n" . $throwable->getTraceAsString()));
                Trigger::getInstance()->throwable($throwable);
            };
            Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, $httpExceptionHandler);
        }

        $httpDispatcher->setHttpExceptionHandler($httpExceptionHandler);
        $requestHook = Di::getInstance()->get(SysConst::HTTP_GLOBAL_ON_REQUEST);
        $afterRequestHook = Di::getInstance()->get(SysConst::HTTP_GLOBAL_AFTER_REQUEST);

        $register->set(EventRegister::onRequest, function (SwooleRequest $request, SwooleResponse $response) use ($httpDispatcher, $requestHook, $afterRequestHook) {
            $request_psr = new Request($request);
            $response_psr = new Response($response);

            GlobalEvent::initHttpGlobalOnRequest($request_psr, $response_psr);

            try {
                $ret = null;

                if (is_callable($requestHook)) {
                    $ret = call_user_func($requestHook, $request_psr, $response_psr);
                }

                if ($ret !== false) {
                    $httpDispatcher->dispatch($request_psr, $response_psr);
                }

            } catch (\Throwable $throwable) {
                call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER), $throwable, $request_psr, $response_psr);
            } finally {
                try {
                    if (is_callable($afterRequestHook)) {
                        call_user_func($afterRequestHook, $request_psr, $response_psr);
                    }

                } catch (\Throwable $throwable) {
                    call_user_func(Di::getInstance()->get(SysConst::HTTP_EXCEPTION_HANDLER), $throwable, $request_psr, $response_psr);
                }
            }

            $response_psr->__response();
        });
    }
}
