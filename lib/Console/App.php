<?php

namespace SlimExt\Console;

use Inhere\Console\Application;
use Psr\Container\ContainerInterface;

use SlimExt\Base\Container;
use SlimExt\Components\QuicklyGetServiceTrait;
use SlimExt\Console\Commands\AssetPublishCommand;
use SlimExt\Console\Commands\CommandUpdateCommand;
use SlimExt\Console\Controllers\AppController;
use SlimExt\Console\Controllers\GeneratorController;

/**
 * Class ConsoleApp
 * @package SlimExt\Console
 */
class App extends Application
{
    use QuicklyGetServiceTrait;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    protected static $bootstraps = [
        'commands' => [
            AssetPublishCommand::class,
            CommandUpdateCommand::class,
        ],
        'controllers' => [
            AppController::class,
            GeneratorController::class,
        ],
    ];

    /**
     * Constructor.
     *
     * @param array $settings
     * @param array $services
     * @param \SlimExt\Collection $config
     * @internal string $name The name of the application
     * @internal string $version The version of the application
     */
    public function __construct(array $settings = [], array $services = [], $config)
    {
        \Slim::$app = $this;
        $this->container = new Container($settings, $services);
        $this->container['config'] = $config;

        parent::__construct([
            'name' => $config->get('name', 'Inhere Console'),
            'version' => $config->get('version', '1.0.1')
        ]);

        // $config->loadArray($this->config);
        $this->loadBootstrapCommands();
    }

    /**
     * loadBuiltInCommands
     */
    public function loadBootstrapCommands()
    {
        /** @var \Inhere\Console\Command $command */
        foreach ((array)static::$bootstraps['commands'] as $command) {
            $this->command($command::getName(), $command);
        }

        /** @var \Inhere\Console\Controller $controller */
        foreach ((array)static::$bootstraps['controllers'] as $controller) {
            $this->controller($controller::getName(), $controller);
        }
    }

    /**
     * get/set config
     * @param  array|string $name
     * @param  mixed $default
     * @return mixed
     */
    public function config($name, $default = null)
    {
        /** @var \SlimExt\Collection $config */
        $config = $this->getContainer()['config'];

        // `$name` is array, set config.
        if (is_array($name)) {
            foreach ((array) $name as $key => $value) {
                $config[$key] = $value;
            }

            return true;
        }

        return $config->get($name, $default);
    }

    /**
     * @return \SlimExt\Collection
     */
    public function getConfig()
    {
        return $this->getContainer()['config'];
    }

    /**
     * @param string $name
     * @param mixed $handler
     * @return $this
     */
    public function add($name, $handler = null)
    {
        return $this->command($name, $handler);
    }

    /**
     * Enable access to the DI container by consumers of $app
     *
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
