<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/31
 * Time: 下午4:08
 */

namespace slimExt\web;

use inhere\exceptions\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use slimExt\filters\BaseFilter;

/**
 * Class AbstractController
 * @package slimExt
 */
abstract class AbstractController
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
     * module class
     * @var string
     */
    protected $moduleId = '';

    /**
     * __construct
     */
    public function __construct()
    {
        // save to container
        \Slim::set('controller', $this);

        $this->init();
    }

    protected function init()
    {
        // Some init logic
    }

    /**
     * {@inheritdoc}
     * @see Response::withJson()
     */
    protected function renderJson($data, $code = null, $msg = '', $status = 200)
    {
        return $this->response->withJson($data, $code, $msg, $status);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // setting...
        $this->request = $request;
        $this->response = $response;

        // active module
        if ($this->moduleId) {
            \Slim::$app->activeModule($this->moduleId);
        }

        // Maybe want to do something
        $this->beforeInvoke($args);

        $resp = $this->processInvoke($args);

        // Might want to customize to perform the action name
        $this->afterInvoke($args, $resp);

        return $resp;
    }

    /**
     * @param array $args
     * @return void
     */
    protected function beforeInvoke(array $args)
    {
    }

    /**
     * @param array $args
     * @return ResponseInterface
     */
    abstract protected function processInvoke(array $args);

    /**
     * @param mixed $result
     * @return ResponseInterface
     */
    protected function onSecurityFilterFail($result)
    {
        if ($resp instanceof ResponseInterface) {
            return $resp;
        }

        $msg = $resp && is_string($resp) ? $resp : 'Access is not allowed';

        // when is xhr
        if ($this->request->isXhr()) {
            return $this->response->withJson(-403, $msg , 403);
        }

        return $this->response->withGoBack('/')->withMessage($msg);
    }

    /**
     * @param array $args
     * @param ResponseInterface $response
     * @return void
     */
    protected function afterInvoke(array $args, $response)
    {
    }

    /**********************************************************
     * request method security check @todo ...
     **********************************************************/

    /**
     * @return array
     */
    public function filters()
    {
        return [
            'access' => [
                // 'filter' => AccessFilter::class, // 过滤器类
                'rules' => [
//                    [
//                        'actions' => ['login', 'error'],
//                        'allow' => false,
//                        'roles' => ['?'],
//                    ],
                    [
                        'actions' => [], // ['logout', 'index'],
                        'allow' => true,
                        // '?' not login '@' logged '*' all user. you can add custom role. like 'user','admin'
                        'roles' => ['*'],
                    ],
                ],
            ],
//            'verbs' => [
//                'filter' => VerbsFilter::class,
//                'actions' => [
//                    //'logout' => ['post'],
//                ],
//            ],
        ];
    }

    /**
     * do Security Filter
     * @param $action
     * @return mixed
     *
     * Return:
     *     bool     True is allow access, False is Deny
     *     string   Deny, is the error message
     *     Response Deny, A Response instance
     *
     * @throws NotFoundException
     */
    protected function doSecurityFilter($action)
    {
        $defaultFilter = '\slimExt\filters\\%sFilter';

        foreach ($this->filters() as $name => $settings) {
            $filter = !empty($settings['filter']) ?
                $settings['filter'] :
                sprintf($defaultFilter, ucfirst($name));

            unset($settings['filter']);

            // filter is a Closure. call it.
            if ($filter instanceof \Closure) {
                return $filter($action, $this);
            }

            if (!class_exists($filter)) {
                throw new NotFoundException("The filter class [$filter] not found.");
            }

            $filter = new $filter($settings);

            if (!$filter instanceof BaseFilter) {
                throw new NotFoundException('The filter class must be instanceof ' . BaseFilter::class);
            }

            $resp = $filter($this->request, $this->response, $action);

            if (true !== $resp) {
                return $resp;
            }
        }

        return true;
    }
}
