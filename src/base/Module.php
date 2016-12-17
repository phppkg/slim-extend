<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 下午9:48
 */

namespace slimExt\base;

use Slim;
use slimExt\DataCollector;

/**
 * Todo ...
 * Class Module
 * @package slimExt\base
 */
abstract class Module
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    protected $path = '';

    public $layout = 'default';

    /**
     * @var DataCollector
     */
    public $config;

    /**
     * __construct
     */
    public function __construct()
    {
        if ( !$this->name || !preg_match('/^[a-zA-Z][\w-]+$/i', $this->name)) {
            throw new \RuntimeException('required define module name (property $name)');
        }

        $this->prepare();

        $this->init();
    }

    public function prepare()
    {
        // get path
        $reflect = new \ReflectionClass($this);
        $this->path = dirname($reflect->getFileName());

        $globalFile = Slim::alias('@config') . '/module-' . $this->name . '.yml';
        $configFile = $this->path . '/config.yml';

        // runtime env config
        $this->config = DataCollector::make($configFile, DataCollector::FORMAT_YML)
                        ->loadYaml(is_file($globalFile) ? $globalFile : '');

        //add path alias
        // Slim::alias('@' . $this->name, $this->path);
        // add twig views path
        // Slim::get('twigRenderer')->getLoader()->addPath($this->path . '/resources/views');
        // or php views path
        // Slim::get('renderer')->setTemplatePath($this->path . '/resources/views');
    }

    protected function init()
    {
        /*
         * Some init logic
         */
    }

    /**
     * register module to application
     * @param App $app
     */
    public static function register($app)
    {
        $module = new static;

        $app->loadModule($module->name, $module)
            ->registerRoutes($app);
    }

    /**
     * @param App $app
     * @return mixed
     */
    abstract public function registerRoutes($app);
}