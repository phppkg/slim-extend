<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\base;

use Slim\Container as SlimContainer;

/**
 * Class Container
 * @package slimExt\base
 */
class Container extends SlimContainer
{
    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getSetting($key,$default = null)
    {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }
}