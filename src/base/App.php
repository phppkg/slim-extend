<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExtend\base;

use Slim\App as SlimApp;

/**
 * Class App
 * @package slimExtend\base
 *
 * @property-read Request                    request
 *
 * @property \Slim\Container                 container
 * @property \Monolog\Logger                 logger
 * @property \slimExtend\base\User       user
 * @property \Slim\Flash\Messages            flash
 * @property \slimExtend\base\Language   language
 *
 *
 * @property \slimExtend\DataCollector   config
 * @property \slimExtend\DataCollector   pageSet
 * @property \slimExtend\DataCollector   pageAttr
 *
 */
class App extends SlimApp
{
    /**
     * @param $id
     * @return \Interop\Container\ContainerInterface|mixed
     */
    public function __get($id)
    {
        if ($id === 'container') {
            return $this->getContainer();
        }

        if ( $this->getContainer()->has($id) ) {
            return $this->getContainer()->get($id);
        }

        throw new \InvalidArgumentException("Getting a unknown property [$id] in class.");
    }

    /**
     * @param $id
     * @param $value
     * @return mixed
     */
    public function __set($id, $value)
    {
        return $this->getContainer()->$id = $value;
    }
}