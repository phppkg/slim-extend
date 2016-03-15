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
 *     Slim::$app->request // by the magic method { @link \slimExtend\base\App::__get() }
 * equal
 *     Slim::$app->request() // by the magic method { @link \Slim\App::__call() }
 * ```
 */
abstract class Slim
{
    /**
     * @var $app \slimExtend\base\App
     */
    public static $app;

    /**
     * path alias
     * @var array
     */
    protected static $aliases = [
        '@project' => PROJECT_PATH,
        '@data'    => PROJECT_PATH . DIR_SEP . 'data',
        '@public'  => PROJECT_PATH . DIR_SEP . 'public',
        '@assets'  => PROJECT_PATH . DIR_SEP . 'public' . DIR_SEP . 'assets',
        '@src'     => PROJECT_PATH . DIR_SEP . 'src',
        '@sources' => PROJECT_PATH . DIR_SEP . 'sources',
        '@temp'    => PROJECT_PATH . DIR_SEP . 'temp',
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
                return isset(self::$aliases[$path]) ? self::$aliases[$path] : $path;
            }

            // have other partial. e.g: @project/temp/logs
            $realPath = $path;
            list($alias, $other) = explode(DIR_SEP, $path, 2);

            if ( isset(self::$aliases[$alias]) ) {
                $realPath = self::$aliases[$alias] . DIR_SEP . $other;
            }

            return $realPath;
        }

        if ( $path && $value && is_string($path) && is_string($value) ) {
            $path = [$path => $value];
        }

        // custom set path's alias. e.g: Slim::alias([ 'alias' => 'path' ]);
        if ( is_array($path) ) {
            foreach ($path as $alias => $realPath) {
                self::$aliases[$alias] = $realPath;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public static function getAliases()
    {
        return self::$aliases;
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function get($id)
    {
        if ( !self::$app ) {
            return null;
        }

        return self::$app->$id;
    }

    /**
     * @param $id
     * @param $value
     */
    public static function set($id, $value)
    {
        if ( self::$app ) {
            self::$app->$id = $value;
        }
    }

    /**
     * @return \slimExtend\DataCollector
     */
    public static function config()
    {
        return self::$app->getContainer()->get('config');
    }

    /**
     * @return \Monolog\Logger
     */
    public static function logger($name='logger')
    {
        return self::$app->getContainer()->get($name);
    }

    /**
     * Allows the use of a static method call registered in container service.
     * e.g:
     * ```
     * // get request service instance.
     *
     *     Slim::get('request')
     * equal
     *     Slim::request()
     * equal
     *     Slim::$di->request
     * equal
     *     Slim::$di->get('request')
     * ```
     * @param $method
     * @param array $args
     * @return mixed
     */
//    public static function __callStatic($method, array $args)
//    {
//         $prefix = substr($method, 0, 3);
//         $id = lcfirst(substr($method, 3));
//
//        $id = lcfirst($method);
//
//         if ( $prefix === 'get' AND self::$di->has($id) ) {
//        if ( self::$di->has($id) ) {
//            return self::$di->get($id);
//        }
//
//        throw new \RuntimeException("Called static method [$method] don't exists!");
//    }
}