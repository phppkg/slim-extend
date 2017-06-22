<?php

namespace slimExt\base;

use slimExt\web\App;
use slimExt\web\Module;

/**
 * Class TraitUseModule
 * @package slimExt\base
 */
trait TraitUseModule
{
    /**
     *  current module instance
     * @var Module
     */
    public $module;

    /**
     * @var array
     */
    public $loadedModules = [];

    /**
     * @param array $classes
     */
    public function registerModules($classes)
    {
        /** @var Module $class */
        foreach ($classes as $class) {
            /** @var App $this */
            $class::register($this);
        }
    }

    /**
     * @param string $class
     * @return Module
     */
    public function activeModule($class)
    {
        /** @var Module $module */
        $module = new $class;

        if ($this->hasModule($module->name)) {
            throw new \LogicException("Module [$class] has been activated!");
        }

        $this->module = $this->loadedModules[$module->name] = $module;

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
    public function module($name = '')
    {
        if (!$name) {
            return $this->module;
        }

        return $this->loadedModules[$name] ?? null;
    }

}