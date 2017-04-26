<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;

/**
 * Class VerbFilter
 *
 * filter the request method
 * @package slimExt\filters
 */
class VerbFilter extends BaseFilter
{
    /**
     * in Controller:
     *
     * public function filters()
     * {
     *     return [
     *       'verbs' => [
     *           'handler' => VerbFilter::class,
     *           'actions' => [
     *               'index' => ['get'],
     *               'add'   => ['post', 'put'],
     *               'logout' => ['post'],
     *           ],
     *       ],
     *   ];
     * }
     * @var array
     */
    public $actions = [];

    /**
     * {@inheritDoc}
     */
    protected function doFilter($action)
    {
        if (!isset($this->actions[$action])) {
            return true;
        }

        $method = strtolower($this->request->getMethod());

        return in_array($method, (array)$this->actions[$action]);
    }
}
