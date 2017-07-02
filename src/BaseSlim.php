<?php

namespace slimExt;

use inhere\library\traits\PathAliasTrait;

/**
 * Class BaseSlim
 * @date 2016.2.17
 * @usage
 *
 * ```
 * // before, please extend it in your application.
 *
 * class Slim extend \slimExt\BaseSlim {
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
 * Slim::$app->request; // by the magic method { @see \slimExt\web\App::__get() }
 * // equals to:
 * Slim::$app->request(); // by the magic method { @see \Slim\App::__call() }
 * ```
 *
 * @method static \slimExt\Collection cache() Return a driver config instance
 */
abstract class BaseSlim
{
    use PathAliasTrait;

    /**
     * @var $app \slimExt\web\App
     */
    public static $app;

    /**
     * path alias
     * @var array
     */
    protected static $aliases = [
        '@project' => PROJECT_PATH,
        '@public' => PROJECT_PATH . DIR_SEP . 'public',
        '@config' => PROJECT_PATH . DIR_SEP . 'config',

        '@src' => PROJECT_PATH . DIR_SEP . 'src',
        '@res' => PROJECT_PATH . DIR_SEP . 'resources',
        '@resources' => PROJECT_PATH . DIR_SEP . 'resources',
        '@temp' => PROJECT_PATH . DIR_SEP . 'temp',

        // '@assets'  => PROJECT_PATH . DIR_SEP . 'public' . DIR_SEP . 'assets',
        '@modules' => PROJECT_PATH . DIR_SEP . 'src' . DIR_SEP . 'modules',

        '@vendor' => PROJECT_PATH . DIR_SEP . 'vendor',
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
     * @throws \inhere\exceptions\LogicException
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
     * @param mixed $key
     * @param mixed $default
     * @return \slimExt\Collection|mixed
     */
    public static function config($key = null, $default = null)
    {
        /** @var \slimExt\Collection $config */
        $config = static::$app->getContainer()['config'];

        if ($key && is_string($key)) {
            return $config->get($key, $default);
        }

        // set, when $key is array
        if ($key && is_array($key)) {
            return $config->loadArray($key);
        }

        return $config;
    }

    /**
     * @param string $name
     * @return \Monolog\Logger
     */
    public static function logger($name = 'logger')
    {
        return static::$app->getContainer()[$name];
    }

    /**
     * @param string $name
     * @param array $args
     */
    public static function __callStatic($name, $args)
    {
        return static::$app->getContainer()[$name];
    }
}
