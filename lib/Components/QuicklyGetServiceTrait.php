<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/2
 * Time: 下午10:48
 */

namespace SlimExt\Components;

/**
 * Class QuicklyGetServiceTrait
 * @package SlimExt\Components
 *
 * @method \Psr\Container\ContainerInterface getContainer()
 */
trait QuicklyGetServiceTrait
{
    /**
     * @param $id
     * @return \psr\Container\ContainerInterface|mixed
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

    /**
     * @param $name
     * @return mixed
     */
    public function getService($name)
    {
        return $this->getContainer()[$name];
    }
}
