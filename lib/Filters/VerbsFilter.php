<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;

/**
 * Class VerbsFilter
 *
 * filter the request method
 * @package slimExt\filters
 */
class VerbsFilter extends BaseFilter
{
    /**
     * in Controller:
     *
     * public function filters()
     * {
     *     return [
     *       'verbs' => [
     *           'filter' => VerbsFilter::class,
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
