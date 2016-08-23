<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 下午9:48
 */

namespace slimExt\base;

/**
 * Todo ...
 * Class Module
 * @package slimExt\base
 */
class Module
{
    public $name = 'default';

    public $layout = 'default';

    // public $configDir = '@project/config/';

    /**
     * __construct
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        /*
        Some init logic
        */
    }
}