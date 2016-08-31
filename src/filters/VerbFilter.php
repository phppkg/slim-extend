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
 * @package slimExt\filters
 */
class VerbFilter extends BaseFilter
{
    protected function doFilter($action)
    {
        $method = $this->request->getMethod();

        return true;
    }
}