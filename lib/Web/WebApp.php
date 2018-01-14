<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace SlimExt\Web;

use Slim\App;
use Slim\Interfaces\RouteInterface;
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
 * @property \Inhere\Library\Components\Language language
 *
 * @property \SlimExt\Database\AbstractDriver db
 * @property \Inhere\Library\Collections\Configuration config
 *
 */
class WebApp extends App
{
    use TraitUseModule, QuicklyGetServiceTrait;

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
     * @return RouteInterface
     */
    public function rest($name, $class): RouteInterface
    {
        return $this->any($name . '[/{resource}]', $class);
    }
}
