<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/6/13
 * Time: 下午7:43
 */

namespace slimExt\middlewares;

use slimExt\base\Request;
use slimExt\base\Response;

/**
 * Class ModuleMiddleware
 * @package slimExt\middlewares
 */
class ModuleMiddleware
{
    /**
     * module name
     * @var string
     */
    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        \Slim::$app->currentModule = $this->name;

        return $next($request, $response);
    }
}