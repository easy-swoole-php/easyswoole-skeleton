<?php

namespace App\Validate;

use EasyApi\Validate\Validate;

class TestValidate extends Validate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require|alphaDash',
    ];
}
