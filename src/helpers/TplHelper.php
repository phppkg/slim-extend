<?php
/**
 *
 */
namespace slimExt\helpers;


class TplHelper
{
    public function __call($method, array $args=[])
    {
        if ( function_exists($method) ) {
            return call_user_func_array($method, $args);
        }
    }
}