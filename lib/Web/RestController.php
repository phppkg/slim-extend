<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace SlimExt\Web;

use Inhere\Exceptions\UnknownMethodException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class RestFulController
 * @package SlimExt\Base
 *
 * how to use. e.g:
 *
 * ```
 * class Book extends SlimExt\Web\RestController
 * {
 *     public function getsAction()
 *     {}
 *     public function getAction($id)
 *     {}
 *     public function postAction()
 *     {}
 *     public function putAction($id)
 *     {}
 *     public function deleteAction($id)
 *     {}
 *     ... ...
 * }
 * ```
 *
 * in routes
 *
 * ```
 * $app->rest('/api/books', Book::class);
 * OR
 * $app->any('/api/books[/{resource}]', Book::class);
 * ```
 */
abstract class RestController extends AbstractController
{
    const DEFAULT_ERR_CODE = 2;

    // $app->any('/api/test[/{resource}]', api\Book::class);
    const RESOURCE_KEY = 'resource';

    // match request like GET /users (get all resource)
    const MARK_MORE = '*';

    // allow multi request method reflect to one action.
    // e.g 'get|post' => 'index'
    const MULTI_SEP = '|';

    // method to action char
    // e.g 'get.search'
    const M2A_CHAR = '.';

    /**
     * method name suffix.
     * so, the access's real controller method name is 'method name' + 'suffix'
     * @var string
     */
    private $actionSuffix = 'Action';

    /**
     * @see self::parseRequestMethod()
     * @var string|int
     */
    private $resourceId;

    /**
     * @var array
     */
    protected $except = [];

    /**
     * allow more. like 'get*' 'head*'
     * @var array
     */
    private static $allowMore = [
        'get', 'head', 'options'
    ];

    /**
     * @return Response
     */
    public function headsAction()
    {
        return $this->response
            ->withHeader('X-Welcome', 'Hi, Welcome to the network world.')
            ->withHeader('X-Request-Method', 'method heads');
    }

    /**
     * @param $id
     * @return Response
     */
    public function headAction($id = 0)
    {
        return $this->response
            ->withHeader('X-Welcome', 'Hi, Welcome to the network world.')
            ->withHeader('X-Request-Method', 'method head')
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
     * more information
     * @see \SlimExt\Base\AbstractController::doSecurityFilter()
     * @return array
     */
    public function filters()
    {
        return [
//            'access' => [
//                'filter' => AccessFilter::class, // 过滤器类
//                'rules' => [ [
//                    'actions' => ['login', 'error'],
//                    'allow' => true,
//                 ], [
//                    'actions' => ['logout', 'index'],
//                    'allow' => true,
//                    'roles' => ['@'],
//                ]],
//            ]
        ];
    }

    /**********************************************************
     * controller method name handle
     **********************************************************/

    /**
     * method mapping - the is default mapping.
     * supported method: CONNECT DELETE GET HEAD OPTIONS PATCH POST PUT TRACE SEARCH
     * you can override it. e.g:
     *
     * ```php
     * protected function methodMapping()
     * {
     *     return [
     *         'get*'   => 'index',   // GET /users
     *         'get'    => 'view',    // GET /users/1
     *         'post'   => 'create',  // POST /users
     *         'put'    => 'update',  // PUT /users/1
     *         'delete' => 'delete',  // DELETE /users/1
     *         // ...
     *     ];
     *
     *     // or
     *     // $mapping = parent::methodMapping();
     *     // $mapping['get'] = 'xxx';
     *     // return $mapping;
     * }
     * ```
     *
     * @return array
     */
    protected function methodMapping()
    {
        return [
            //REQUEST_METHOD => method name(no suffix)
            // 'gets' is special key.
            'get*' => 'gets',      // GET /users
            'get' => 'get',        // GET /users/1
            'post' => 'post',      // POST /users
            'put' => 'put',        // PUT /users/1
            // usually PUT == PATCH
            'patch' => 'patch',      // PATCH /users/1
            'delete' => 'delete',    // DELETE /users/1
            'head' => 'head',        // HEAD /users/1
            'head*' => 'heads',      // HEAD /users
            'options' => 'option',    // OPTIONS /users/1
            'options*' => 'options',   // OPTIONS /users

            'connect' => 'connect',   // CONNECT /users
            'search' => 'search',     // SEARCH /users
            'trace' => 'trace',       // TRACE /users

            // multi REQUEST_METHOD match
            // 'get|post' => 'index'

            // extra method mapping
            // 'get.search' => search
            // 'post.create'  => create
            // 'post|put.save'  => save
        ];
    }

    /**********************************************************
     * call the controller method
     **********************************************************/

    /**
     * @param array $args
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    protected function processInvoke(array $args)
    {
        // default restFul action name
        list($action, $error) = $this->parseRequestMethod($this->request, $args);

        if ($error) {
            return $this->errorHandler($error);
        }

        if (!$action) {
            return $this->response->withJson([], __LINE__, 'No resource method are available!', 404);
        }

        $actionMethod = $action . ucfirst($this->actionSuffix);

        if (!method_exists($this, $actionMethod)) {
            // throw new NotFoundException('Error Processing Request, Action [' . $action . '] don\'t exists!');
            $msg = 'Error Processing Request, resource method [' . $action . '] don\'t exists!';

            return $this->response->withJson([], __LINE__, $msg, 404);
        }

        // if enable request action security filter
        if (true !== ($result = $this->doSecurityFilter($action))) {
            return $this->onSecurityFilterFail($result);
        }

        /** @var Response $response */
        $resp = $this->$actionMethod(array_shift($args));

        // if the action return is array data
        if (\is_array($resp)) {
            $resp = $this->response->withRawJson($resp);
        }

        return $resp;
    }

    /**
     * {@inheritdoc}
     */
    protected function onSecurityFilterFail($result)
    {
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        $msg = $result && \is_string($result) ? $result : 'Resources not allowed for access';

        return $this->response->withJson([], -403, $msg, 403);
    }

    /**
     * parseRequestMethod -- return real controller method name
     * @param  Request $request
     * @param array $args
     * @return string
     * @throws UnknownMethodException
     */
    private function parseRequestMethod($request, array $args)
    {
        // default restFul action name, equals to REQUEST_METHOD
        $mapping = $this->methodMapping();

        if (!$mapping || !\is_array($mapping)) {
            return [null, 'No any accessible resource method!'];
        }

        // 值可能是:
        // 1. 是资源名/资源ID
        //  e.g GET `/users/12`
        //  - $resourceId = 12
        //  e.g GET `/blog/a-post-name`
        //  - $resourceId = 'a-post-name'
        // 2. 是扩展资源方法
        //  e.g GET `/users/search` 同时 `methodMapping()` 配置了 `'get.search' => search`
        //  - $resourceId = 'search'
        $resourceId = !empty($args[self::RESOURCE_KEY]) ? trim($args[self::RESOURCE_KEY]) : '';

        // convert 'first-second' to 'firstSecond'
        if ($resourceId && strpos($resourceId, '-')) {
            $resourceId = ucwords(str_replace('-', ' ', $resourceId));
            $resourceId = str_replace(' ', '', lcfirst($resourceId));
        }

        $map = $this->parseMethodMapping($mapping);
        $method = strtolower($request->getMethod());

        // 是扩展资源方法 e.g 'get.search'
        if ($resourceId && !is_numeric($resourceId)) {
            $extraKey = $method . self::M2A_CHAR . $resourceId;

            if (isset($map[$extraKey])) {
                return [$map[$extraKey], null];
            }
        }

        $action = $error = '';
        $moreKey = $method . self::MARK_MORE;
        $this->resourceId = $resourceId;

        // 根据请求方法 匹配 资源方法
        foreach ($map as $key => $value) {
            // want get resources. like 'get*' 'head*'
            if (!$resourceId && $key === $moreKey && \in_array($method, self::$allowMore, true)) {
                $action = $value;

                // is a resource. like '/users/1' '/users/username'
            } elseif ($key === $method) {
                $action = $method === 'options' ? 'option' : $value;
            }

            // match successful.
            if ($action) {
                break;
            }
        }

        return [$action, $error];
    }

    /**
     * when route have been setting action name:
     * ```
     * $app->get('/users/{id}', controllers\User::class . ':view');
     * ```
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $args[2][self::RESOURCE_KEY] = $method;

        return $this($args[0], $args[1], $args[2]);
    }

//    private function getParsedMapping()
//    {
//    }

    /**
     * @param array $mapping
     * @return array
     */
    private function parseMethodMapping(array $mapping)
    {
        $map = [];

        foreach ($mapping as $key => $item) {
            $key = str_replace(' ', '', $key);
            $item = trim($item);

            // `get.search = search` `get|post.search = search`
            if (strpos($key, '.')) {
                list($m, $a) = explode('.', $key);

                $m = strtolower($m);

                if (strpos($m, '|')) {
                    foreach (explode('|', $m) as $k) {
                        $map[$k . self::M2A_CHAR . $a] = $item;
                    }
                } else {
                    $map[$m . self::M2A_CHAR . $a] = $item;
                }

                // get|post => index
            } elseif (strpos($key, '|')) {
                $key = strtolower($key);

                foreach (explode('|', $key) as $k) {
                    $map[$k] = $item;
                }
            } else {
                $key = strtolower($key);

                $map[$key] = $item;
            }
        }

        return $map;
    }

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
