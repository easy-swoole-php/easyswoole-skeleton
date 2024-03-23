<?php

namespace EasySwooleLib\Controller;

use EasyApi\Validate\Validate;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwooleLib\Request\Request;

class BaseAnnotationController extends AnnotationController
{
    /** @var Request */
    protected $_request;

    /**
     * 是否批量验证
     *
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 验证失败是否抛出异常
     *
     * @var bool
     */
    protected $failException = false;

    protected function onRequest(?string $action): ?bool
    {
        $this->_request = new Request($this->request());
        if (!$this->initialize()) {
            return false;
        }

        return parent::onRequest($action);
    }

    protected function initialize(): bool
    {
        return true;
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->withStatus(404);
        $this->response()->withHeader('Content-Type', 'text/html;charset=utf-8');
        $this->response()->write('Not Found!');
    }

    public function onException(\Throwable $throwable): void
    {
        parent::onException($throwable);
    }

    /**
     * 验证数据
     *
     * @access protected
     *
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     *
     * @return array|string|true
     * @throws \Exception
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }

            $v = null;

            if (strpos($validate, "\\") !== false) {
                $v = new $validate();
            }

            if (is_null($v)) {
                throw new \Exception('validate class is not exist.');
            }

            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new \Exception($v->getError());
            }
            return $v->getError();
        }

        return true;
    }
}
