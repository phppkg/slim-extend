<?php

namespace slimExt\base;

/**
 * Class TraitUseModule
 * @package slimExt\base
 */
trait TraitUseModule
{
    /**
     * @var array
     */
    public $loadedModules = [];

    /**
     * @param $name
     * @param Module $module
     * @return Module
     */
    public function loadModule($name, Module $module)
    {
        if ($this->hasModule($name)) {
            throw new \RuntimeException('Module [' . $name . '] have been loaded. don\'t allow override.');
        }

        $this->loadedModules[$name] = $this->loadedModules['__last'] = $module;

        return $module;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasModule($name)
    {
        return isset($this->loadedModules[$name]);
    }

    /**
     * @param string $name
     * @return Module
     */
    public function module($name = '__last')
    {
        return isset($this->loadedModules[$name]) ? $this->loadedModules[$name] : null;
    }

}