<?php

namespace App\HttpController;

use EasySwoole\Component\Context\ContextManager;
use EasySwoole\Http\Message\Status;
use EasySwooleLib\Controller\BaseController;

class Index extends BaseController
{
    public function index()
    {
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/welcome.html';

        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/welcome.html';
        }

        return response(file_get_contents($file), Status::CODE_OK, ['Content-Type' => 'text/html;charset=utf-8']);
    }

    public function test()
    {
        return response('this is test');
    }

    public function hello()
    {
        $routerParams = ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY);
        $id = $routerParams['id'] ?? null;
        $name = $routerParams['name'] ?? null;

        if ($this->_request->isGet()) {
            return response("[Api][{$this->_request->method()}] The id is {$id}, Welcome to use EasySwoole Framework ^_^.");
        } else if ($this->_request->isPost()) {
            return response("[Api][{$this->_request->method()}] Hello {$name}, Welcome to use EasySwoole Framework ^_^.");
        }

        return false;
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $file = EASYSWOOLE_ROOT . '/vendor/easyswoole/easyswoole/src/Resource/Http/404.html';

        if (!is_file($file)) {
            $file = EASYSWOOLE_ROOT . '/src/Resource/Http/404.html';
        }

        return response(file_get_contents($file), Status::CODE_OK, ['Content-Type' => 'text/html;charset=utf-8']);
    }
}
