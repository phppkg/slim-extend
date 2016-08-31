<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/31
 * Time: 下午4:08
 */

namespace slimExt;

use inhere\librarys\exceptions\NotFoundException;
use Slim;
use slimExt\base\Request;
use slimExt\base\Response;
use slimExt\filters\AccessFilter;
use slimExt\filters\VerbFilter;

/**
 * Class AbstractController
 * @package slimExt
 */
class AbstractController
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
        // save to container
        Slim::set('controller', $this);

        $this->init();
    }

    protected function init()
    {
        // Some init logic
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
                'handler' => AccessFilter::class,
                'rules' => [
//                    [
//                        'actions' => ['login', 'error'],
//                        'allow' => true,
//                    ],
                    [
                        'actions' => [], // ['logout', 'index'],
                        'allow' => true,
                        // '@' logged '*' all user. you can add custom role. like 'user','admin'
                        'roles' => ['*'],
                    ],
                ],
            ],
//            'verbs' => [
//                'handler' => VerbFilter::class,
//                'actions' => [
//                    //'logout' => ['post'],
//                ],
//            ],
        ];
    }

    /**
     * @param $action
     * @return mixed
     */
    protected function doSecurityFilter($action)
    {
        if ( !$this->filters()) {
            return true;
        }

        $defaultFilter = '\slimExt\filters\\%sFilter';

        foreach ($this->filters() as $name => $settings) {
            $handler = !empty($settings['handler']) ?
                $settings['handler'] :
                sprintf( $defaultFilter, ucfirst($name));

            unset($settings['handler']);

            if ( !class_exists($handler) ) {
                throw new NotFoundException("Filter handler class [$handler] not found.");
            }

            $handler = new $handler($settings);
            $result = $handler($this->request, $this->response, $this, $action);

            if ( true !== $result ) {
                return $result;
            }
        }

        return true;
    }
}