<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace SlimExt\Base;

use Inhere\Library\Helpers\Php;
use LogicException;
use Inhere\Library\Collections\SimpleCollection;
use Slim\Container as SlimContainer;

/**
 * Class Container
 * @package SlimExt\Base
 *
 * @property-read SimpleCollection settings
 */
class Container extends SlimContainer
{
    /**
     * Container constructor.
     * @param array $settings
     * @param array $values
     */
    public function __construct(array $settings = [], array $values = [])
    {
        parent::__construct($values);

        if ($settings) {
            $this->settings->replace($settings);
        }
    }

    /**
     * @param $id
     * @param array $params
     * @return mixed
     * @throws LogicException
     */
    public function call($id, array $params = [])
    {
        $callable = $this->raw($id);

        if (!($callable instanceof \Closure)) {
            throw new LogicException('The service must is a Closure by the method(Container::call) call.');
        }

        return $params ? $callable($this) : Php::call($callable, $params);
    }
}
