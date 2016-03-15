<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExtend\base;

use Slim\Collection as SlimCollection;

/**
 * Class Collection
 * @package slimExtend\base
 */
class Collection extends SlimCollection
{
    /**
     * @param array $data
     * @return $this
     */
    public function sets(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function toArray()
    {
        return $this->all();
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        parent::set($name, $value);

        return $this;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }
}