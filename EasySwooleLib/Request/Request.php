<?php

namespace EasySwooleLib\Request;

use EasySwoole\EasySwoole\ServerManager;

class Request
{
    /**
     * EasySwoole Http Request对象
     *
     * @var \EasySwoole\Http\Request
     */
    protected $esRequest;

    /**
     * 请求类型
     *
     * @var string
     */
    protected $method;

    /**
     * 当前请求参数
     *
     * @var array
     */
    protected $param = [];

    /**
     * 当前GET参数
     *
     * @var array
     */
    protected $get = [];

    /**
     * 当前POST参数
     *
     * @var array
     */
    protected $post = [];

    /**
     * 当前SERVER参数
     *
     * @var array
     */
    protected $server = [];

    /**
     * 当前HEADER参数
     *
     * @var array
     */
    protected $header = [];

    /**
     * 全局过滤规则
     *
     * @var array
     */
    protected $filter;

    /**
     * 当前请求内容
     *
     * @var string
     */
    protected $content;

    /**
     * php://input内容
     *
     * @var string
     */
    // php://input
    protected $input;

    /**
     * 构造函数
     *
     * @access public
     *
     * @param \EasySwoole\Http\Request $request 参数
     */
    public function __construct(\EasySwoole\Http\Request $request)
    {
        $this->esRequest = $request;

        if (is_null($this->filter)) {
            $this->filter = \config('app.default_filter');
        }

        // 保存 php://input
        $this->input = $this->esRequest->getBody()->__toString();
    }

    /**
     * 获取server参数
     *
     * @access public
     *
     * @param mixed        $name    数据名称
     * @param string       $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function server($name = '', $default = null, $filter = '')
    {
        if (empty($this->server)) {
            $this->server = $this->esRequest->getServerParams();
        }

        if (is_array($name)) {
            return $this->server = array_merge($this->server, $name);
        }

        return $this->input($this->server, false === $name ? false : $name, $default, $filter);
    }

    /**
     * 获取当前请求的时间
     *
     * @param bool $float 是否使用浮点类型
     *
     * @return integer|float
     */
    public function time(bool $float = false)
    {
        return $float ? $this->server('request_time_float') : $this->server('request_time');
    }

    /**
     * 当前的请求类型
     *
     * @param bool $method true 获取原始请求类型
     *
     * @return string
     */
    public function method()
    {
        if (!$this->method) {
            $this->method = isset($this->server['request_method']) ? $this->server['request_method'] : $this->server('request_method');
        }

        return $this->method;
    }

    /**
     * 是否为GET请求
     *
     * @access public
     * @return bool
     */
    public function isGet()
    {
        return $this->method() === 'GET';
    }

    /**
     * 是否为POST请求
     *
     * @access public
     * @return bool
     */
    public function isPost()
    {
        return $this->method() === 'POST';
    }

    /**
     * 是否为PUT请求
     *
     * @access public
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->method() == 'PUT';
    }

    /**
     * 是否为DELTE请求
     *
     * @access public
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->method() == 'DELETE';
    }

    /**
     * 是否为HEAD请求
     *
     * @access public
     * @return bool
     */
    public function isHead(): bool
    {
        return $this->method() == 'HEAD';
    }

    /**
     * 是否为PATCH请求
     *
     * @access public
     * @return bool
     */
    public function isPatch(): bool
    {
        return $this->method() == 'PATCH';
    }

    /**
     * 是否为OPTIONS请求
     *
     * @access public
     * @return bool
     */
    public function isOptions()
    {
        return $this->method() === 'OPTIONS';
    }

    /**
     * 获取当前请求的参数
     *
     * @access public
     *
     * @param mixed        $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function param($name = '', $default = null, $filter = '')
    {
        if (empty($this->param)) {
            $method = $this->method();

            // 自动获取请求变量
            switch ($method) {
                case 'POST':
                    $vars = $this->post(false);
                    break;
                case 'PUT':
                case 'DELETE':
                case 'PATCH':
                    $vars = $this->put(false);
                    break;
                default:
                    $vars = [];
            }

            // 当前请求参数和URL地址中的参数合并
            $this->param = array_merge($this->get(false), $vars);
        }

        if (true === $name) {
            $data = $this->param;
            return $this->input($data, '', $default, $filter);
        }

        return $this->input($this->param, $name, $default, $filter);
    }

    /**
     * 设置或者获取当前的Header
     *
     * @access public
     *
     * @param string|array $name    header名称
     * @param string       $default 默认值
     *
     * @return mixed
     */
    public function header($name = '', $default = null)
    {
        $swooleHeaders = $this->esRequest->getSwooleRequest()->header;
        $this->header  = array_merge($this->header, $swooleHeaders);

        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }

        if ('' === $name) {
            return $this->header;
        }

        $name = str_replace('_', '-', strtolower($name));

        return $this->header[$name] ?? $default;
    }

    /**
     * 设置获取GET参数
     *
     * @access public
     *
     * @param mixed        $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function get($name = '', $default = null, $filter = '')
    {
        if (empty($this->get)) {
            $this->get = $this->esRequest->getQueryParams();
        }

        if (is_array($name)) {
            $this->param = [];
            return $this->get = array_merge($this->get, $name);
        }

        return $this->input($this->get, $name, $default, $filter);
    }

    /**
     * 设置获取POST参数
     *
     * @access public
     *
     * @param mixed        $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function post($name = '', $default = null, $filter = '')
    {
        if (empty($this->post)) {
            $content = $this->input;
            if (empty($this->esRequest->getParsedBody()) && false !== strpos($this->contentType(), 'application/json')) {
                $this->post = (array)json_decode($content, true);
            } else {
                $this->post = $this->esRequest->getParsedBody();
            }
        }

        if (is_array($name)) {
            $this->param = [];
            return $this->post = array_merge($this->post, $name);
        }

        return $this->input($this->post, $name, $default, $filter);
    }

    /**
     * 获取PUT参数
     *
     * @access public
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function put($name = '', $default = null, $filter = '')
    {
        if (is_null($this->put)) {
            $content = $this->input;
            if (false !== strpos($this->contentType(), 'application/json')) {
                $this->put = (array)json_decode($content, true);
            } else {
                parse_str($content, $this->put);
            }
        }

        if (is_array($name)) {
            $this->param = [];
            return $this->put = is_null($this->put) ? $name : array_merge($this->put, $name);
        }

        return $this->input($this->put, $name, $default, $filter);
    }

    /**
     * 设置获取DELETE参数
     *
     * @access public
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function delete($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 设置获取PATCH参数
     *
     * @access public
     *
     * @param string|array $name    变量名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤方法
     *
     * @return mixed
     */
    public function patch($name = '', $default = null, $filter = '')
    {
        return $this->put($name, $default, $filter);
    }

    /**
     * 获取变量 支持过滤和默认值
     *
     * @access public
     *
     * @param array        $data    数据源
     * @param string|false $name    字段名
     * @param mixed        $default 默认值
     * @param string|array $filter  过滤函数
     *
     * @return mixed
     */
    public function input($data = [], $name = '', $default = null, $filter = '')
    {
        if (false === $name) {
            // 获取原始数据
            return $data;
        }

        $name = (string)$name;
        if ('' != $name) {
            // 解析name
            if (strpos($name, '/')) {
                list($name, $type) = explode('/', $name);
            } else {
                $type = 's';
            }

            // 按.拆分成多维数组进行判断
            foreach (explode('.', $name) as $val) {
                if (isset($data[$val])) {
                    $data = $data[$val];
                } else {
                    // 无输入数据，返回默认值
                    return $default;
                }
            }

            if (is_object($data)) {
                return $data;
            }
        }

        // 解析过滤器
        $filter = $this->getFilter($filter, $default);

        if (is_array($data)) {
            array_walk_recursive($data, [$this, 'filterValue'], $filter);
            reset($data);
        } else {
            $this->filterValue($data, $name, $filter);
        }

        if (isset($type) && $data !== $default) {
            // 强制类型转换
            $this->typeCast($data, $type);
        }

        return $data;
    }

    /**
     * 设置或获取当前的过滤规则
     *
     * @access public
     *
     * @param mixed $filter 过滤规则
     *
     * @return mixed
     */
    public function filter($filter = null)
    {
        if (is_null($filter)) {
            return $this->filter;
        }

        $this->filter = $filter;
    }

    protected function getFilter($filter, $default)
    {
        if (is_null($filter)) {
            $filter = [];
        } else {
            $filter = $filter ?: $this->filter;
            if (is_string($filter) && false === strpos($filter, '/')) {
                $filter = explode(',', $filter);
            } else {
                $filter = (array)$filter;
            }
        }

        $filter[] = $default;

        return $filter;
    }

    /**
     * 递归过滤给定的值
     *
     * @access public
     *
     * @param mixed $value   键值
     * @param mixed $key     键名
     * @param array $filters 过滤方法+默认值
     *
     * @return mixed
     */
    private function filterValue(&$value, $key, $filters)
    {
        $default = array_pop($filters);

        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                // 调用函数或者方法过滤
                $value = call_user_func($filter, $value);
            } elseif (is_scalar($value)) {
                if (false !== strpos($filter, '/')) {
                    // 正则过滤
                    if (!preg_match($filter, $value)) {
                        // 匹配不成功返回默认值
                        $value = $default;
                        break;
                    }
                } elseif (!empty($filter)) {
                    // filter函数不存在时, 则使用filter_var进行过滤
                    // filter为非整形值时, 调用filter_id取得过滤id
                    $value = filter_var($value, is_int($filter) ? $filter : filter_id($filter));
                    if (false === $value) {
                        $value = $default;
                        break;
                    }
                }
            }
        }

        return $value;
    }


    /**
     * 强制类型转换
     *
     * @access public
     *
     * @param string $data
     * @param string $type
     *
     * @return mixed
     */
    private function typeCast(&$data, $type)
    {
        switch (strtolower($type)) {
            // 数组
            case 'a':
                $data = (array)$data;
                break;
            // 数字
            case 'd':
                $data = (int)$data;
                break;
            // 浮点
            case 'f':
                $data = (float)$data;
                break;
            // 布尔
            case 'b':
                $data = (boolean)$data;
                break;
            // 字符串
            case 's':
            default:
                if (is_scalar($data)) {
                    $data = (string)$data;
                } else {
                    throw new \InvalidArgumentException('variable type error：' . gettype($data));
                }
        }
    }

    /**
     * 当前请求URL地址中的query参数
     *
     * @access public
     * @return string
     */
    public function query()
    {
        return $this->server('query_string');
    }

    /**
     * 当前请求 HTTP_CONTENT_TYPE
     *
     * @access public
     * @return string
     */
    public function contentType()
    {
        $contentType = $this->header('content-type');

        if ($contentType) {
            if (strpos($contentType, ';')) {
                list($type) = explode(';', $contentType);
            } else {
                $type = $contentType;
            }
            return trim($type);
        }

        return '';
    }

    /**
     * 设置或者获取当前请求的content
     *
     * @access public
     * @return string
     */
    public function getContent()
    {
        if (is_null($this->content)) {
            $this->content = $this->input;
        }

        return $this->content;
    }

    /**
     * 获取当前请求的php://input
     *
     * @access public
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * 获取客户端IP地址
     *
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv  是否进行高级模式获取（有可能被伪装）
     *
     * @return mixed
     */
    public function ip(int $type = 0, bool $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        $httpAgentIp = config('app.http_agent_ip');

        if ($httpAgentIp && $this->header($httpAgentIp)) {
            $ip = $this->header($httpAgentIp);
        } elseif ($adv) {
            if ($this->header('x-forwarded-for')) {
                $arr = explode(',', $this->header('x-forwarded-for'));
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } else {
                $swooleServer = ServerManager::getInstance()->getSwooleServer();
                $client       = $swooleServer->getClientInfo($this->esRequest->getSwooleRequest()->fd);
                $ip           = $client['remote_ip'] ?? '';
            }
        } else {
            $swooleServer = ServerManager::getInstance()->getSwooleServer();
            $client       = $swooleServer->getClientInfo($this->esRequest->getSwooleRequest()->fd);
            $ip           = $client['remote_ip'] ?? '';
        }

        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip   = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }
}
