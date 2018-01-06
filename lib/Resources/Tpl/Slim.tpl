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
 *     Slim::$app->request // by the magic method { @see \slimExt\base\App::__get() }
 * equal
 *     Slim::$app->request() // by the magic method { @see \Slim\App::__call() }
 * ```
 * @method static \slimExt\Collection cache() Return a driver config instance
 * @method static \inhere\library\utils\LiteLogger logger() Return a driver config instance
 */
class Slim extends \slimExt\BaseSlim
{
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
}
