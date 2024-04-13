<?php

namespace App\HttpController;

use EasySwooleLib\Controller\BaseController;
use EasySwooleLib\Logger\Log as AppLog;

class Log extends BaseController
{
    // eg. curl "http://localhost:9501/Log/test"
    public function test()
    {
        AppLog::debug('this debug log', 'debug');
        AppLog::info('this info log', 'info');
        AppLog::notice('this notice log', 'notice');
        AppLog::waring('this waring log', 'waring');
        AppLog::error('this error log', 'error');

        return json(['log']);
    }
}
