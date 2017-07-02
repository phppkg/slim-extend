<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\web;

use slimExt\base\TraitUseModule;
use slimExt\components\QuicklyGetServiceTrait;

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
    use QuicklyGetServiceTrait;

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

}
