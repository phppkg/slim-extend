<?php

namespace slimExt\rest;

use Slim;
use inhere\librarys\exceptions\NotFoundException;
use inhere\librarys\exceptions\UnknownMethodException;
use slimExt\base\Request;
use slimExt\base\Response;

/**
 * Class RestFulController
 * @package slimExt\base
 *
 * how to use. e.g:
 * ```
 * class Book extends slimExt\base\RestFulController
 * {
 *     public function getsAction($args)
 *     {}
 *     public function getAction($args)
 *     {}
 *     public function postAction($args)
 *     {}
 *     public function putAction($args)
 *     {}
 *     public function deleteAction($args)
 *     {}
 *     ... ...
 * }
 * ```
 */
abstract class Controller
{
    // supported method
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_DELETE = 'DELETE';
    const METHOD_GET = 'GET';
    const METHOD_HEAD = 'HEAD';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_PATCH = 'PATCH';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_TRACE = 'TRACE';

    // special key
    const METHOD_GETS = 'GETS';

    const RESOURCE_KEY = 'resource';
    const MARK_MORE = '...';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * method name suffix.
     * so, the access's real controller method name is 'method name' + 'suffix'
     * @var string
     */
    public $actionSuffix = 'Action';

    protected $except = [];

    protected $extraMapping= [
        'GET,search' => 'search',
    ];

    /**
     * __construct
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        // Some init logic
    }

    /**
     * method mapping - the is default mapping.
     * supported method:
     *     CONNECT DELETE GET HEAD OPTIONS PATCH POST PUT TRACE
     *
     * you can change it. e.g:
     * protected function methodMapping()
     * {
     *     return [
     *         'gets'     => 'index',   # GET /users
     *         'get'      => 'view',    # GET /users/1
     *         'post'     => 'create',  # POST /users
     *         'put'      => 'update',  # PUT /users/1
     *         'delete'   => 'delete',  # DELETE /users/1
     *     ];
     *     // or
     *     // $mapping = parent::methodMapping();
     *     // $mapping['get'] = 'xxx';
     *     // return $mapping;
     * }
     * @var array
     */
    protected function methodMapping()
    {
        return [
             //REQUEST_METHOD => method name
             // 'gets' is special key.
             'get...'     => 'gets',   # GET /users
             'get'      => 'get',    # GET /users/1
             'post'     => 'post',   # POST /users
             'put'      => 'put',    # PUT /users/1
             # usually PUT == PATCH
             'patch'    => 'put',     # PATCH /users/1
             'delete'   => 'delete',  # DELETE /users/1
             'head'     => 'head',    # HEAD /users/1
             'head...'     => 'heads',   # HEAD /users
             'options'  => 'option', # OPTIONS /users/1
             'options...'  => 'options', # OPTIONS /users
             // extra method mapping
             // 'get,search' => search
         ];
    }

    /**
     * handleMethodMapping -- return real controller method name
     * @param  Request $request
     * @return string
     */
    protected function handleMethodMapping($request, array $args)
    {
        // default restFul action name, equals to REQUEST_METHOD
        $method = $request->getMethod();
        $mapping = $this->methodMapping();

        if (!$mapping || !is_array($mapping)) {
            throw new UnknownMethodException('No class method allow the called.');
        }

        $resource = !empty($args['resource']) ? $args['resource'] : '';

        foreach ($mapping as $key => $action) {
            # code...
        }

        return $method . $this->actionSuffix;
    }

    /**********************************************************
     * call the controller method
     **********************************************************/

    /**
     * @param array $args
     * @return void
     */
    protected function beforeInvoke(array $args)
    {}

    /**
     * e.g.
     * define route:
     * ```
     *   $app->any('/test[/{resource}]', controllers\api\Test::class);
     * ```
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
de($args);
        // default restFul action name
        $action = $this->handleMethodMapping($request);

        if ( method_exists($this, $action) ) {
            $response = $this->$action($args);

            // if the action return is array data
            if ( is_array($response) ) {
                $response = $this->response->withJson(['list' => $list]);
            }

            // Might want to customize to perform the action name
            $this->afterInvoke($args, $response);

            return $response;
        }

        throw new NotFoundException('Error Processing Request, Action [' . $action . '] don\'t exists!');
    }

    /**
     * @param array $args
     * @param Response $response
     * @return void
     */
    protected function afterInvoke(array $args, $response)
    {}

}
