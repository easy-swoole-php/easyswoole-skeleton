<?php
return [
    'enable'     => false,
    'worker_num' => 3,
    'crontab'    => [
        \App\Crontab\FooCrontab::class,
    ]
];
