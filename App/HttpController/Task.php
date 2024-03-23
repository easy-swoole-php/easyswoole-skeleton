<?php

namespace App\HttpController;

use App\Task\FooTask;
use EasySwooleLib\Controller\BaseController;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwooleLib\Logger\Log;

class Task extends BaseController
{
    // eg. curl "http://localhost:9501/Task/syncTask"
    public function syncTask()
    {
        $result = TaskManager::getInstance()->sync(function () {
            Log::info("exec sync task");
            return 'exec success';
        });

        return json(['result' => $result]);
    }

    // eg. curl "http://localhost:9501/Task/asyncTask"
    public function asyncTask()
    {
        $taskId = TaskManager::getInstance()->async(function () {
            Log::info("exec async task");
        }, function ($reply, $taskId, $workerIndex) {
            // $reply 返回的执行结果
            // $taskId 任务id
            Log::info("async success");
        });

        return json(['taskId' => $taskId]);
    }

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
