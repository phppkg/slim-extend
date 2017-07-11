<?php

namespace slimExt\console;

use Psr\Container\ContainerInterface;

use slimExt\base\Container;
use slimExt\buildIn\commands\AppCreateCommand;
use slimExt\buildIn\commands\AssetPublishCommand;
use slimExt\buildIn\commands\CommandUpdateCommand;
use slimExt\components\QuicklyGetServiceTrait;

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
        AppCreateCommand::class,
        AssetPublishCommand::class,
        CommandUpdateCommand::class,
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
        $this->loadBuiltInCommands();
    }

    /**
     * loadBuiltInCommands
     */
    public function loadBuiltInCommands()
    {
        foreach (static::$bootstraps as $command) {
            $this->command($command::getName(), $command);
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
