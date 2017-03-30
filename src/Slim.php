<?php

/**
 * Class Slim
 * @date  2016.2.17
 *
 * ---------------
 * How to quickly get a service instance?
 * e.g:
 * get request service instance.
 *
 * ```
 *     Slim::get('request')
 * equal
 *     Slim::$app->request // by the magic method { @link \slimExt\base\App::__get() }
 * equal
 *     Slim::$app->request() // by the magic method { @link \Slim\App::__call() }
 * ```
 */
abstract class Slim
{
    /**
     * @var $app \slimExt\base\App
     */
    public static $app;

    /**
     * path alias
     * @var array
     */
    protected static $aliases = [
        '@project' => PROJECT_PATH,
        '@public'  => PROJECT_PATH . DIR_SEP . 'public',
        '@config'  => PROJECT_PATH . DIR_SEP . 'config',

        '@src'     => PROJECT_PATH . DIR_SEP . 'src',
        '@res'      => PROJECT_PATH . DIR_SEP . 'res',
        '@resources' => PROJECT_PATH . DIR_SEP . 'res',
        '@temp'    => PROJECT_PATH . DIR_SEP . 'temp',

        // '@assets'  => PROJECT_PATH . DIR_SEP . 'public' . DIR_SEP . 'assets',
        '@modules'   => PROJECT_PATH . DIR_SEP . 'src' . DIR_SEP . 'modules',

        '@vendor'    => PROJECT_PATH . DIR_SEP . 'vendor',
    ];

    /**
     * set/get path alias
     * @param array|string $path
     * @param string|null $value
     * @return bool|string
     */
    public static function alias($path, $value=null)
    {
        // get path by alias
        if ( is_string($path) && !$value ) {
            // don't use alias
            if ( $path[0] !== '@' ) {
                return $path;
            }

            $path = str_replace(['/','\\'], DIR_SEP , $path);

            // only a alias. e.g. @project
            if ( !strpos($path, DIR_SEP) ) {
                return isset(static::$aliases[$path]) ? static::$aliases[$path] : $path;
            }

            // have other partial. e.g: @project/temp/logs
            $realPath = $path;
            list($alias, $other) = explode(DIR_SEP, $path, 2);

            if ( isset(static::$aliases[$alias]) ) {
                $realPath = static::$aliases[$alias] . DIR_SEP . $other;
            }

            return $realPath;
        }

        if ( $path && $value && is_string($path) && is_string($value) ) {
            $path = [$path => $value];
        }

        // custom set path's alias. e.g: Slim::alias([ 'alias' => 'path' ]);
        if ( is_array($path) ) {
            foreach ($path as $alias => $realPath) {
                // 1th char must is '@'
                if ( $alias[0] !== '@' ) {
                    continue;
                }

                static::$aliases[$alias] = $realPath;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public static function getAliases()
    {
        return static::$aliases;
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function has($id)
    {
        if ( !static::$app ) {
            return null;
        }

        return static::$app->getContainer()->has($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function get($id)
    {
        if ( !static::$app ) {
            return null;
        }

        return static::$app->getContainer()[$id];
    }

    /**
     * @param $id
     * @param array $params
     * @return mixed
     */
    public static function call($id,$params = [])
    {
        if ( !static::$app ) {
            return null;
        }

        return static::$app->container->call($id,$params);
    }

    /**
     * @param $id
     * @param string $class
     * @param array $params
     * @return mixed
     * @throws \inhere\library\exceptions\LogicException
     */
    public static function make($id, $class = null, $params = null)
    {
        if ( !static::$app ) {
            return null;
        }

        $callable = static::$app->container->factory(function () use ($class, $params) {
            return new $class($params);
        });

        static::$app->container[$id] = $callable;
    }

    /**
     * @param $id
     * @param $value
     */
    public static function set($id, $value)
    {
        if ( static::$app ) {
            static::$app->$id = $value;
        }
    }

    /**
     * @param mixed $key
     * @param mixed $default
     * @return \slimExt\DataCollector|mixed
     */
    public static function config($key=null, $default=null)
    {
        /** @var \slimExt\DataCollector $config */
        $config = static::$app->getContainer()['config'];

        if ($key &&  is_string($key) ) {
            return $config->get($key,$default);
        }

        // set, when $key is array
        if ($key && is_array($key) ) {
            return $config->loadArray($key);
        }

        return $config;
    }

    /**
     * @param string $name
     * @return \Monolog\Logger
     */
    public static function logger($name='logger')
    {
        return static::$app->getContainer()[$name];
    }
}
