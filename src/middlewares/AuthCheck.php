<?php

namespace slimExt\middlewares;

use slimExt\exceptions\InvalidConfigException;
use Slim;
use slimExt\base\Request;
use slimExt\base\Response;

/**
 * Class AuthCheck  - 是否登录检查
 * @package slimExt\middlewares
 */
class AuthCheck
{
    /**
     * Auth middleware invokable class
     *
     * @param  Request   $request  PSR7 request
     * @param  Response  $response PSR7 response
     * @param  callable  $next     Next middleware
     *
     * @return Response
     * @throws InvalidConfigException
     * @throws \InvalidArgumentException
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        // if have been login
        if( Slim::$app->user->isLogin() ) {
            return $next($request, $response);
        }

        $authUrl = Slim::get('config')->get('urls.login');

        if (!$authUrl) {
            throw new InvalidConfigException("require config 'urls.login' !");
        }

        $msg = Slim::$app->language->tran('needLogin');

        // when is xhr
        if ( $request->isXhr() ) {
            $data = ['redirect' => $authUrl];

            return $response->withJson($data, __LINE__, $msg);
        }

         return $response->withRedirect($authUrl)->withMessage($msg);
    }
}