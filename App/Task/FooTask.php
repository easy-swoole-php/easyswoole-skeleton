<?php

namespace App\Task;

use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Task\AbstractInterface\TaskInterface;
use EasySwooleLib\Logger\Log;

class FooTask implements TaskInterface
{
    protected $data;

    public function __construct($data)
    {
        // 保存投递过来的数据
        $this->data = $data;
    }

    public function run(int $taskId, int $workerIndex)
    {
        // 执行逻辑
        Log::info("task id: {$taskId}");
        Log::info("task data: " . json_encode($this->data));
        Log::info("worker index: {$workerIndex}");

        return 'exec success';
    }

    public function onException(\Throwable $throwable, int $taskId, int $workerIndex)
    {
        // 异常处理
        Trigger::getInstance()->throwable($throwable);
    }
}
