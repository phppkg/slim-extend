<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\base;

use Slim\App as SlimApp;

/**
 * Class App
 * @package slimExt\base
 *
 * @property-read Request                    request
 * @property-read Response                   response
 *
 * @property \slimExt\base\Container         container
 * @property \Monolog\Logger                 logger
 * @property \slimExt\base\User       user
 * @property \Slim\Flash\Messages            flash
 * @property \slimExt\base\Language   language
 *
 * @property \slimExt\database\AbstractDriver   db
 * @property \slimExt\DataCollector   config
 *
 */
class App extends SlimApp
{
    /**
     * @var array
     */
    public $loadedModules = [];

    /**
     * @param $name
     * @param Module $module
     * @return Module
     */
    public function loadModule($name, Module $module)
    {
        if ( $this->hasModule($name) ) {
            throw new \RuntimeException('Module ['.$name.'] have been loaded. don\'t allow override.');
        }

        $this->loadedModules[$name] = $this->loadedModules['__last'] = $module;

        return $module;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasModule($name)
    {
        return isset($this->loadedModules[$name]);
    }

    /**
     * @param string $name
     * @return Module
     */
    public function module($name='__last')
    {
        return isset($this->loadedModules[$name]) ? $this->loadedModules[$name] : null;
    }

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
        return $this->getContainer()[$id] = $value;
    }
}
