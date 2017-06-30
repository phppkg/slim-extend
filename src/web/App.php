<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\web;

use slimExt\base\TraitUseModule;

/**
 * Class App
 * @package slimExt\base
 *
 * @property-read Request request
 * @property-read Response response
 *
 * @property \slimExt\base\Container container
 * @property \Monolog\Logger logger
 * @property \inhere\libraryPlus\auth\User user
 * @property \Slim\Flash\Messages flash
 * @property \slimExt\base\Language language
 *
 * @property \slimExt\database\AbstractDriver db
 * @property \slimExt\Collection config
 *
 */
class App extends \Slim\App
{
    use TraitUseModule;

    public function __construct($container = [])
    {
        \Slim::$app = $this;

        parent::__construct($container);
    }

    /**
     * Add route for RESTFul resource
     *
     * ```php
     *  $this->rest('/users', controllers\User::class);
     *  // Equals to:
     *  // $this->any('/users[/{resource}]', controllers\User::class);
     * ```
     *
     * @param  string $name  The resource name e.g '/users'
     * @param  string $class The resource controller class
     *
     * @return \Slim\Interfaces\RouteInterface
     */
    public function rest($name, $class)
    {
        return $this->any($name . '[/{resource}]', $class);
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

        if ($this->getContainer()->has($id)) {
            return $this->getContainer()->get($id);
        }

        throw new \InvalidArgumentException("Getting a unknown property [$id] in class.");
    }

    /**
     * @param string $id
     * @param mixed $value
     * @return mixed
     */
    public function __set($id, $value)
    {
        return $this->getContainer()[$id] = $value;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function __isset($id)
    {
        return $this->getContainer()->has($id);
    }
}
