<?php

namespace slimExt\console;

use Psr\Container\ContainerInterface;

use slimExt\base\Container;
use slimExt\components\QuicklyGetServiceTrait;
use slimExt\console\commands\AssetPublishCommand;
use slimExt\console\commands\CommandUpdateCommand;
use slimExt\console\controllers\AppController;
use slimExt\console\controllers\GeneratorController;

/**
 * Class ConsoleApp
 * @package slimExt\base
 */
class App extends \inhere\console\App
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
     * @param \slimExt\Collection $config
     * @internal string $name The name of the application
     * @internal string $version The version of the application
     */
    public function __construct(array $settings = [], array $services = [], $config)
    {
        \Slim::$app = $this;
        $this->container = new Container($settings, $services);

        parent::__construct([
            'name' => $config->get('name', 'Inhere Console'),
            'version' => $config->get('version', '1.0.1')
        ]);

        $config->loadArray($this->config);
        $this->container['config'] = $config;
        $this->loadBootstrapCommands();
    }

    /**
     * loadBuiltInCommands
     */
    public function loadBootstrapCommands()
    {
        /** @var \inhere\console\Command $command */
        foreach ((array)static::$bootstraps['commands'] as $command) {
            $this->command($command::getName(), $command);
        }

        /** @var \inhere\console\Controller $controller */
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
        /** @var \slimExt\Collection $config */
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
     * @return \slimExt\Collection
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
    public function getContainer()
    {
        return $this->container;
    }
}
