<?php

namespace SlimExt;

use Inhere\Library\Traits\PathAliasTrait;

/**
 * Class BaseSlim
 * @date 2016.2.17
 * @usage
 *
 * ```
 * // before, please extend it in your application.
 *
 * class Slim extends \SlimExt\BaseSlim {
 *      // ...
 * }
 *
 * // How to quickly get a service instance?
 * // e.g. get request service instance.
 *
 * Slim::get('request');
 * // equals to:
 * Slim::request();
 * // equals to:
 * Slim::$app->request; // by the magic method { @see \SlimExt\Web\App::__get() }
 * // equals to:
 * Slim::$app->request(); // by the magic method { @see \Slim\App::__call() }
 *
 */
abstract class BaseSlim
{
    use PathAliasTrait;

    /**
     * @var $app \SlimExt\Base\Container
     */
    public static $di;

    /**
     * @var $app \SlimExt\Web\WebApp
     */
    public static $app;

    /**
     * path alias
     * @var array
     */
    protected static $aliases = [
        // ...
    ];

    /**
     * @param string $id
     * @return mixed
     */
    public static function get($id)
    {
        return static::$app->getContainer()[$id];
    }

    /**
     * @param string $id
     * @param $value
     */
    public static function set($id, $value)
    {
        if (static::$app) {
            static::$app->getContainer()[$id] = $value;
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function has($id)
    {
        if (!static::$app) {
            return null;
        }

        return static::$app->getContainer()->has($id);
    }


    /**
     * @param $id
     * @param array $params
     * @return mixed
     */
    public static function call($id, array $params = [])
    {
        if (!static::$app) {
            return null;
        }

        return static::$app->container->call($id, $params);
    }

    /**
     * @param $id
     * @param string $class
     * @param array $params
     * @return mixed
     */
    public static function make($id, $class = null, $params = null)
    {
        if (!static::$app) {
            return null;
        }

        $callable = static::$app->container->factory(function () use ($class, $params) {
            return new $class($params);
        });

        static::$app->container[$id] = $callable;

        return true;
    }

    /**
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($name, $args)
    {
        return static::$app->getContainer()[$name];
    }
}
