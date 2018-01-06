<?php

namespace SlimExt\Middleware;

use Psr\Http\Message\ResponseInterface;
use inhere\exceptions\InvalidConfigException;
use Slim;
use SlimExt\Web\Request;
use SlimExt\Web\Response;

/**
 * Class AuthCheck  - 是否登录检查
 * @package SlimExt\Middleware
 */
class AuthCheck
{
    /**
     * Auth middleware invokable class
     *
     * @param  Request $request PSR7 request
     * @param  Response $response PSR7 response
     * @param  callable $next Next middleware
     *
     * @return ResponseInterface
     * @throws InvalidConfigException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        // if have been login
        if (Slim::$app->user->isLogin()) {
            return $next($request, $response);
        }

        return Slim::$app->user->loginRequired($request, $response);
    }
}
