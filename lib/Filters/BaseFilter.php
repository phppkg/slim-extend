<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 下午8:04
 */

namespace SlimExt\Filters;

use inhere\library\StdObject;
use SlimExt\Web\Request;
use SlimExt\Web\Response;

/**
 * Class BaseFilter
 * @package SlimExt\Filters
 */
abstract class BaseFilter extends StdObject
{
    // all user
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
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $action)
    {
        // setting...
        $this->request = $request;
        $this->response = $response;

        return $this->doFilter($action);
    }

    /**
     * how to get controller instance? use `\Slim::get('controller')`
     * @param string $action
     * @return mixed
     *
     * Return:
     *     bool     True is allow access, False is Deny
     *     string   Deny, is the error message
     *     Response Deny, A Response instance
     */
    abstract protected function doFilter($action);
}
