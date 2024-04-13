<?php

namespace App\HttpController;

use App\Task\FooTask;
use EasySwooleLib\Controller\BaseController;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwooleLib\Logger\Log;

class Task extends BaseController
{
    // eg. curl "http://localhost:9501/Task/syncTaskWithTemplate"
    public function syncTaskWithTemplate()
    {
        // 投递同步任务
        $result = TaskManager::getInstance()->sync(new FooTask(['user' => 'custom']));
        return json(['result' => $result]);
    }

    // eg. curl "http://localhost:9501/Task/asyncTaskWithTemplate"
    public function asyncTaskWithTemplate()
    {
        // 投递异步任务
        $taskId = TaskManager::getInstance()->async(new FooTask(['user' => 'custom']));
        return json(['taskId' => $taskId]);
    }
}
