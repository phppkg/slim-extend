<?php

namespace slimExtend\middlewares;

use slimExtend\exceptions\InvalidConfigException;
use Slim;
use slimExtend\base\Request;
use slimExtend\base\Response;

/**
 * Class AuthCheck  - 是否登录检查
 * @package slimExtend\middlewares
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

        $authUrl = Slim::get('config')->get('mder.loginUrl');

        if (!$authUrl) {
            throw new InvalidConfigException("require config 'mder.loginUrl' !");
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