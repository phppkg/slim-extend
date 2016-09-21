<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\base;

use inhere\librarys\exceptions\LogicException;
use Slim\Container as SlimContainer;

/**
 * Class Container
 * @package slimExt\base
 */
class Container extends SlimContainer
{
    /**
     * @param $id
     * @param array $params
     * @return mixed
     * @throws LogicException
     */
    public function call($id, $params = [])
    {
        $callable = $this->raw($id);

        if ( !($callable instanceof \Closure) ) {
            throw new LogicException('The service must is a Closure by the method(Container::call) call.');
        }

        return $params ? $callable($this) : call_user_func_array($callable, $params);
    }

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