<?php

namespace slimExt\rest;

use inhere\librarys\exceptions\HttpRequestException;
use Slim;
use inhere\librarys\exceptions\NotFoundException;
use inhere\librarys\exceptions\UnknownMethodException;
use slimExt\AbstractController;
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
abstract class Controller extends AbstractController
{
    const DEFAULT_ERR_CODE = 2;

    const RESOURCE_ARG_KEY = 'argument';
    const MARK_MORE = '...';

    /**
     * method name suffix.
     * so, the access's real controller method name is 'method name' + 'suffix'
     * @var string
     */
    private $actionSuffix = 'Action';

    protected $except = [];

    protected $extraMapping= [
        'GET,search' => 'search',
    ];

    /**
     * @return array
     * @return Response
     */
    public function headsAction()
    {
        return $this->response
            ->withHeader('X-Welcome','Hi, Welcome to the network.')
            ->withHeader('X-Request-Method','method heads');
    }

    /**
     * @param $id
     * @return Response
     */
    public function headAction($id)
    {
        return $this->response
            ->withHeader('X-Welcome','Hi, Welcome to the network.')
            ->withHeader('X-Request-Method','method head')
            ->withHeader('X-Request-Param', $id);
    }

    /**
     * @return array
     */
    public function optionsAction()
    {
        return array_values($this->methodMapping());
    }

    /**
     * @return array
     */
    public function filters()
    {
        return [
//            'access' => [
//                'handler' => AccessFilter::class,
//                'rules' => [
//                    [
//                        'actions' => ['login', 'error'],
//                        'allow' => true,
//                    ],
//                    [
//                        'actions' => ['logout', 'index'],
//                        'allow' => true,
//                        '@' logged '*' all user. you can add custom role. like 'user','admin'
//                        'roles' => ['@'],
//                    ],
//                ],
//            ]
        ];
    }

    /**********************************************************
     * controller method name handle
     **********************************************************/

    /**
     * method mapping - the is default mapping.
     * supported method:
     *     CONNECT DELETE GET HEAD OPTIONS PATCH POST PUT TRACE
     *
     * you can change it. e.g:
     * protected function methodMapping()
     * {
     *     return [
     *         'get...'   => 'index',   # GET /users
     *         'get'      => 'view',    # GET /users/1
     *         'post'     => 'create',  # POST /users
     *         'put'      => 'update',  # PUT /users/1
     *         'delete'   => 'delete',  # DELETE /users/1
     *         // ...
     *     ];
     *     // or
     *     // $mapping = parent::methodMapping();
     *     // $mapping['get'] = 'xxx';
     *     // return $mapping;
     * }
     * @return array
     */
    protected function methodMapping()
    {
        return [
             //REQUEST_METHOD => method name
             // 'gets' is special key.
             'get...'     => 'gets',    # GET /users
             'get'      => 'get',       # GET /users/1
             'post'     => 'post',      # POST /users
             'put'      => 'put',       # PUT /users/1
             # usually PUT == PATCH
             'patch'    => 'patch',     # PATCH /users/1
             'delete'   => 'delete',    # DELETE /users/1
             'head'     => 'head',      # HEAD /users/1
             'head...'  => 'heads',     # HEAD /users
             'options'  => 'option',    # OPTIONS /users/1
             'options...' => 'options', # OPTIONS /users
             // extra method mapping
             // 'get.search' => search
         ];
    }

    /**
     * handleMethodMapping -- return real controller method name
     * @param  Request $request
     * @param array $args
     * @return string
     * @throws HttpRequestException
     */
    protected function handleMethodMapping($request, array $args)
    {
        // default restFul action name, equals to REQUEST_METHOD
        $method = strtolower($request->getMethod());
        $mapping = $this->methodMapping();

        if (!$mapping || !is_array($mapping)) {
            throw new UnknownMethodException('No class method allow the called.');
        }

        $action = $error = '';
        $allowMore = ['get','head','options'];
        $argument = !empty($args[self::RESOURCE_ARG_KEY]) ? trim($args[self::RESOURCE_ARG_KEY]) : '';
        $extraKey = $method . '.' . $argument;

        // find like 'get.search' ... extra method
        if ($argument && isset($mapping[$extraKey]) ) {
            $actionMethod = trim($mapping[$extraKey]) . $this->actionSuffix;

            return [$actionMethod, $error];
        }

        foreach ($mapping as $key => $value) {
            // full match REQUEST_METHOD. like 'get' 'post'
            if ($argument && $key === $method) {
                $action = $method === 'options' ? 'option' : $value;

            // like 'get' 'get...' 'get.search'
            } elseif (0 === strpos($key, $method)) {
                $ext = substr($key, strlen($method));

                // as 'get...'
                if ($ext === self::MARK_MORE && !$argument && in_array($method, $allowMore)) {
                    $action = $value;
                }
            }

            // match successful.
            if ($action) {
                break;
            }
        }

        $actionMethod = $action . $this->actionSuffix;

        return [$actionMethod, $error];
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

        // default restFul action name
        list($action,$error) = $this->handleMethodMapping($request,$args);

        if ($error) {
            return $this->errorHandler($error);
        }

        if ( method_exists($this, $action) ) {
            try {
                /** @var Response $response */
                $response = $this->$action(array_shift($args));

                // if the action return is array data
                if (is_array($response)) {
                    $response = $this->response->withJson($response);
                }
            } catch (\Exception $e) {
                $error = $e->getMessage();

                return $this->errorHandler($error, $e->getCode() ? : 2);
            }

            // Might want to customize to perform the action name
            $this->afterInvoke($args, $response);

            return $response;
        }

        // throw new NotFoundException('Error Processing Request, Action [' . $action . '] don\'t exists!');
        $error = 'Error Processing Request, Action [' . $action . '] don\'t exists!';

        return $this->errorHandler($error);
    }

    /**
     * @param array $args
     * @param Response $response
     * @return void
     */
    protected function afterInvoke(array $args, $response)
    {}

    /**
     * @param string $error
     * @param int $code
     * @param int $status
     * @return mixed
     * @internal param Response $response
     */
    protected function errorHandler($error, $code = 2, $status = 403)
    {
        //throw new HttpRequestException($error);
        return $this->response->withJson([], $code, $error, $status);
    }
}
