<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 下午8:04
 */

namespace slimExt\filters;

use inhere\library\StdObject;
use slimExt\base\Request;
use slimExt\base\Response;

/**
 * Class BaseFilter
 * @package slimExt\filters
 */
abstract class ObjectFilter extends StdObject
{
    const MATCH_ALL = '*';

    // logged user
    const MATCH_LOGGED = '@';

    // guest user
    const MATCH_GUEST = '?';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @param Request $request
     * @param Response $response
     * @param $action
     * @return bool
     */
    public function __invoke(Request $request, Response $response, $action)
    {
        // setting...
        $this->request = $request;
        $this->response = $response;

        return $this->doFilter($action);
    }

    /**
     * how to get controller instance?
     *  `Slim::get('controller')`
     * @param string $action
     * @return bool
     */
    abstract protected function doFilter($action);
}
