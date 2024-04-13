<?php

namespace App\HttpController;

use EasySwooleLib\Controller\BaseController;

class Cookie extends BaseController
{
    // eg: http://localhost:9501/Cookie/set
    public function set()
    {
        $this->response()->setCookie('c-name', 'c-value');
        return json(['hello']);
    }

    // eg: http://localhost:9501/Cookie/get
    public function get()
    {
        $data = $this->request()->getCookieParams();
        return json($data);
    }

    // eg: http://localhost:9501/Cookie/del
    public function del()
    {
        $this->response()->setCookie('c-name', '');
        return json(['ok']);
    }
}
