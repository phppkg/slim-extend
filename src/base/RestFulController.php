<?php

namespace slimExt\base;

use Slim;

/**
 * Class RestFulController
 * @package slimExt\base
 *
 * how to use. e.g:
 * ```
 * class Book extends slimExt\base\RestFulController
 * {
 *     public function get($request, $response, $args)
 *     {}
 *     public function post($request, $response, $args)
 *     {}
 *     public function put($request, $response, $args)
 *     {}
 *     public function delete($request, $response, $args)
 *     {}
 *     ... ...
 * }
 * ```
 */
abstract class RestFulController
{
    /**
     * __construct
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        /*
        Some init logic
        */
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     */
    protected function beforeInvoke(Request $request, Response $response, array $args)
    {
        return false;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     * @throws \RuntimeException
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // Maybe want to do something
        if ( $result = $this->beforeInvoke($request, $response, $args)) {
            return $result;
        }

        // default restFul action name
        $action = strtolower($request->getMethod());

        if ( method_exists($this, $action) ) {
            return $this->$action($request, $response, $args);
        }

        // Might want to customize to perform the action name
        if ( $result = $this->afterInvoke($request, $response, $args)) {
            return $result;
        }

        throw new \RuntimeException('Error Processing Request');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     */
    protected function afterInvoke(Request $request, Response $response, array $args)
    {
        return false;
    }

}
