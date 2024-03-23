<?php
return [
    [
        'enable'                => false,
        'process_name'          => 'php_easyswoole_foo',
        'process_group'         => 'php_easyswoole_foo',
        'arg'                   => [],
        'redirect_stdin_stdout' => false,
        'pipe_type'             => \EasySwoole\Component\Process\Config::PIPE_TYPE_SOCK_DGRAM,
        'enable_coroutine'      => false,
        'max_exit_wait_time'    => 3,
        'class'                 => \App\Process\FooProcess::class,
        'process_num'           => 1,
    ],
    [
        'enable'       => false,
        'process_name' => 'php_easyswoole_fooQueueConsume',
        'class'        => \App\Process\Queue\Consumer\FooQueueConsumerProcess::class,
        'process_num'  => 1,
    ]
];
