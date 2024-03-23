<?php

namespace App\HttpController;

use EasySwooleLib\Controller\BaseController;
use EasySwooleLib\Redis\Utility\RedisUtil;

class Redis extends BaseController
{
    // eg: curl "http://localhost:9501/Redis/set"
    public function set()
    {
        $ok  = RedisUtil::set('ckey', 'cache value');
        $ok1 = RedisUtil::set('ckey1', 'cache value2', ['EX' => 5]);

        return json(['ckey' => $ok, 'ckey1' => $ok1]);
    }

    // eg: curl "http://localhost:9501/Redis/get"
    public function get()
    {
        $val = RedisUtil::get('ckey');

        return json([
            'ckey'  => $val,
            'ckey1' => RedisUtil::get('ckey1')
        ]);
    }

    // eg: curl "http://localhost:9501/Redis/del"
    public function del()
    {
        return json(['del' => RedisUtil::del('ckey')]);
    }
}
