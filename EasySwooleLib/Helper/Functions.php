<?php
if (!function_exists('config')) {
    function config($keyPath = '')
    {
        return \EasySwoole\EasySwoole\Config::getInstance()->getConf($keyPath);
    }
}


if (!function_exists('container')) {
    /**
     * @return Closure|\EasySwooleLib\Ioc\Container
     */
    function container()
    {
        return \EasySwooleLib\Ioc\Container::getInstance();
    }
}

if (!function_exists('bind')) {
    /**
     * 绑定一个类到容器
     *
     * @param string|array $abstract 类标识、接口（支持批量绑定）
     * @param mixed        $concrete 要绑定的类、闭包或者实例
     *
     * @return \EasySwooleLib\Ioc\Container
     */
    function bind($abstract, $concrete = null)
    {
        return \EasySwooleLib\Ioc\Container::getInstance()->bind($abstract, $concrete);
    }
}

if (!function_exists('invoke')) {
    /**
     * 调用反射实例化对象或者执行方法 支持依赖注入
     *
     * @param mixed $call 类名或者callable
     * @param array $args 参数
     *
     * @return mixed
     */
    function invoke($call, array $args = [])
    {
        if (is_callable($call)) {
            return \EasySwooleLib\Ioc\Container::getInstance()->invoke($call, $args);
        }

        return \EasySwooleLib\Ioc\Container::getInstance()->invokeClass($call, $args);
    }
}

if (!function_exists('easyswoole_request')) {
    /**
     * 获取当前 EasySwoole Request 对象实例
     *
     * @return \EasySwoole\Http\Request
     */
    function easyswoole_request()
    {
        return \EasySwooleLib\Context\ContextUtil::get(\EasySwooleLib\Enums\AppEnum::EASYSWOOLE_HTTP_REQUEST);
    }
}

if (!function_exists('request')) {
    /**
     * 获取当前 Request 对象实例
     *
     * @return \EasySwooleLib\Request\Request
     */
    function request()
    {
        return new \EasySwooleLib\Request\Request(easyswoole_request());
    }
}

if (!function_exists('easyswoole_response')) {
    /**
     * 获取当前 EasySwoole Response 对象实例
     *
     * @return \EasySwoole\Http\Response
     */
    function easyswoole_response()
    {
        return \EasySwooleLib\Context\ContextUtil::get(\EasySwooleLib\Enums\AppEnum::EASYSWOOLE_HTTP_RESPONSE);
    }
}

if (!function_exists('response')) {
    /**
     * 发送普通数据给客户端
     *
     * @param mixed      $data   输出数据
     * @param int|string $code   状态码
     * @param array      $header 头信息
     * @param string     $type
     *
     * @return false|mixed
     */
    function response($data = [], int $code = 200, array $header = [], string $type = 'html')
    {
        return \EasySwooleLib\Response\Response::create(easyswoole_response(), $data, $type, $code, $header)->send();
    }
}

if (!function_exists('json')) {
    /**
     * 发送json数据给客户端
     *
     * @param $data
     * @param $code
     * @param $header
     * @param $options
     *
     * @return false|mixed
     */
    function json($data = [], $code = 200, $header = [], $options = [])
    {
        return \EasySwooleLib\Response\Response::create(easyswoole_response(), $data, 'json', $code, $header, $options)->send();
    }
}

if (!function_exists('redirect')) {
    /**
     * 发送重定向请求给客户端
     *
     * @param $url
     * @param $params
     * @param $code
     * @param $with
     *
     * @return mixed
     */
    function redirect(string $url, array $params = [], int $code = \EasySwoole\Http\Message\Status::CODE_MOVED_TEMPORARILY)
    {
        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }

        if (!empty($params)) {
            $url = $url . '?' . http_build_query($params);
        }

        return easyswoole_response()->redirect($url, $code);
    }
}
