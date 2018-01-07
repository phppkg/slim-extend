<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace SlimExt\Web;

use Slim\App;
use SlimExt\Base\TraitUseModule;
use SlimExt\Components\QuicklyGetServiceTrait;

/**
 * Class App
 * @package SlimExt\Base
 *
 * @property-read Request request
 * @property-read Response response
 *
 * @property \SlimExt\Base\Container container
 * @property \Monolog\Logger logger
 * @property \Inhere\LibraryPlus\Auth\User user
 * @property \Slim\Flash\Messages flash
 * @property \SlimExt\Base\Language language
 *
 * @property \SlimExt\Database\AbstractDriver db
 * @property \Inhere\Library\Collections\Configuration config
 *
 */
class WebApp extends App
{
    use TraitUseModule, QuicklyGetServiceTrait;

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
