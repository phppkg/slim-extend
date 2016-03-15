<?php

namespace slimExtend\middlewares;

use slimExtend\base\Request;
use slimExtend\base\Response;

/**
 * Class Permission
 * @package slimExtend\middlewares
 */
class Permission
{

    /**
     * Auth middleware invokable class
     *
     * @param  Request   $request  PSR7 request
     * @param  Response  $response PSR7 response
     * @param  callable  $next     Next middleware
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $passed = true;

        // some logic ... ...

        // if passed == true, go on ...
        if( $passed ) {
            return $next($request, $response);
        }

        $msg = \Slim::$app->language->tran('http403');

        // when is xhr
        if ( $request->isXhr() ) {
            return $response->withJson(403, $msg)->withStatus(403);
        }

        return $response->withStatus(403)->write($msg);
    }
}