<?php

namespace slimExt\base;

use slimExt\exceptions\NotFoundException;
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
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

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
     * @param array $args
     * @return void
     */
    protected function beforeInvoke(array $args)
    {}

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     * @throws NotFoundException
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // setting...
        $this->request = $request;
        $this->response = $response;

        // Maybe want to do something
        $this->beforeInvoke($args);

        // default restFul action name
        $action = strtolower($request->getMethod());

        if ( method_exists($this, $action) ) {
            $response = $this->$action($args);

            // Might want to customize to perform the action name
            $this->afterInvoke( $args);

            return $response;
        }

        throw new NotFoundException('Error Processing Request, Action [' . $action . '] don\'t exists!');
    }

    /**
     * @param array $args
     * @return void
     */
    protected function afterInvoke(array $args)
    {}

}
