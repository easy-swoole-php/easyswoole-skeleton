<?php

namespace App\HttpController;

use App\Validate\TestValidate;
use EasySwooleLib\Controller\BaseController;

class Validate extends BaseController
{
    // eg: curl "http://localhost:9501/Validate/validate"
    // eg: curl "http://localhost:9501/Validate/validate?username=easyswoole&password=easyswoole"
    public function validate()
    {
        $get      = $this->_request->get();
        $validate = new \EasySwoole\Validate\Validate();
        $validate->addColumn('username')->required();
        $validate->addColumn('password')->required();

        if (!$validate->validate($get)) {
            return json(['error' => $validate->getError()->__toString()]);
        }

        return json($get);
    }

    // 下面是用 thinkphp-validate 组件实现的验证，你也可以使用 easyswoole 自带的验证器
    // eg: curl "http://localhost:9501/Validate/thinkphpValidate"
    // eg: curl "http://localhost:9501/Validate/thinkphpValidate?username=easyswoole&password=easyswoole"
    public function thinkphpValidate()
    {
        $get      = $this->_request->get();
        $validate = new TestValidate();

        if ($validate->check($get) === false) {
            return json(['error' => $validate->getError()]);
        }

        return json($get);
    }
}
