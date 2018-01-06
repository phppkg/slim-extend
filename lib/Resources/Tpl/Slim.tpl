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
 *     Slim::$app->request // by the magic method { @see \SlimExt\Base\App::__get() }
 * equal
 *     Slim::$app->request() // by the magic method { @see \Slim\App::__call() }
 * ```
 * @method static \SlimExt\Collection cache() Return a driver config instance
 * @method static \Inhere\Library\Utils\LiteLogger logger() Return a driver config instance
 */
class Slim extends \SlimExt\BaseSlim
{
    /**
     * @param mixed $key
     * @param mixed $default
     * @return \SlimExt\Collection|mixed
     */
    public static function config($key = null, $default = null)
    {
        /** @var \SlimExt\Collection $config */
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
