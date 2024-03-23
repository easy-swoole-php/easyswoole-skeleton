<?php

namespace EasySwooleLib\Process;

use EasySwoole\Component\Process\Config;
use EasySwoole\Component\Process\Manager;
use RuntimeException;
use function config;

class EasySwooleProcessManager
{
    public static function registerProcess()
    {
        $processes = config('processes');

        foreach ($processes as $index => $processConfig) {
            $index = $index + 1;
            $enable = $processConfig['enable'] ?? false;
            $processName = $processConfig['process_name'] ?? '';
            $class = $processConfig['class'] ?? null;
            $processNum = $processConfig['process_num'] ?? 1;

            if (!$enable) {
                continue;
            }

            if (!$processName) {
                throw new RuntimeException("The process[{$index}] configuration is missing the process_name configuration");
            }

            if (!$class) {
                throw new RuntimeException("The process[{$index}] configuration is missing the class configuration");
            }

            if (!class_exists($class)) {
                throw new RuntimeException("{$class} class not exists.");
            }

            $processConfigArr = [
                'processName' => $processName, // 设置 进程名称
            ];

            // 设置 进程组名称
            if (!empty($processConfig['process_group'])) {
                $processConfigArr['processGroup'] = $processConfig['process_group'];
            }

            // 设置 传递参数到自定义进程中
            if (isset($processConfig['arg'])) {
                $processConfigArr['arg'] = $processConfig['arg'];
            }

            if (isset($processConfig['redirect_stdin_stdout'])) {
                $processConfigArr['redirectStdinStdout'] = $processConfig['redirect_stdin_stdout'];
            }

            if (isset($processConfig['pipe_type']) && in_array($processConfig['pipe_type'], [
                    Config::PIPE_TYPE_NONE,
                    Config::PIPE_TYPE_SOCK_STREAM,
                    Config::PIPE_TYPE_SOCK_DGRAM,
                ])) {
                $processConfigArr['pipeType'] = $processConfig['pipe_type'];
            }

            // 设置 自定义进程自动开启协程环境
            if (isset($processConfig['enable_coroutine'])) {
                $processConfigArr['enableCoroutine'] = $processConfig['enable_coroutine'];
            }

            if (isset($processConfig['max_exit_wait_time'])) {
                $processConfigArr['maxExitWaitTime'] = $processConfig['max_exit_wait_time'];
            }

            $processConfigObject = new Config($processConfigArr);

            for ($i = 0; $i < $processNum; $i++) {
                $process = new $class($processConfigObject);
                Manager::getInstance()->addProcess($process);
            }
        }
    }
}
