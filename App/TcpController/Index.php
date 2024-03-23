<?php

namespace App\TcpController;

class Index extends Base
{
    public function index()
    {
        $this->response()->setMessage('this is index');
    }
}
