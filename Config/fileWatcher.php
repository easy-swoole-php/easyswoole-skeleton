<?php
return [
    'enable'            => false,
    'allow_mode'        => [\EasySwooleLib\AppMode::MODE_DEV],
    'monitor_dir'       => EASYSWOOLE_ROOT . "/App",
    'on_change_handler' => [\App\HotReload\FileWatcher::class, 'onChange'],
];
