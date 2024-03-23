<?php
return [
    [
        'enable'            => false,
        'queue_name'        => 'foo1',
        'queue_class'       => \App\Queue\FooQueue::class,
        'queue_driver'      => 'redis',
        'queue_driver_name' => 'default',
    ]
];
