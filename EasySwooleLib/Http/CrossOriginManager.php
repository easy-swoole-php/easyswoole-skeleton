<?php

namespace EasySwooleLib\Http;

use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use function config;

class CrossOriginManager
{
    public static function handle(Request $request, Response $response)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $crossConfig = config('crossOrigin');
            $origin      = $request->getHeaderLine('origin') ?: $crossConfig['Access-Control-Allow-Origin'];
            $response->withHeader('Access-Control-Allow-Origin', $origin);

            foreach ($crossConfig as $name => $value) {
                $response->withHeader($name, $value);
            }

            $response->withStatus(Status::CODE_OK);
            return false;
        }

        return true;
    }
}
