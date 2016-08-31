<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;


/**
 * Class AccessFilter
 * auth/permission check
 * @package slimExt\filters
 */
class AccessFilter extends BaseFilter
{
    public $rules = [];

    protected function doFilter( $action )
    {
        return true;
    }
}