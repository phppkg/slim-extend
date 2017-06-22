<?php

namespace slimExt\console;

use slimExt\base\Container;
use Interop\Container\ContainerInterface;

use slimExt\buildIn\commands\AppCreateCommand;
use slimExt\buildIn\commands\AssetPublishCommand;
use slimExt\buildIn\commands\CommandUpdateCommand;

/**
 * Class ConsoleApp
 * @package slimExt\base
 */
class App extends \inhere\console\App
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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
     * @param string $name The name of the application
     * @param string $version The version of the application
     */
    public function __construct(array $settings = [], array $services = [], $name = 'Inhere Console', $version = '1.0.1')
    {
        \Slim::$app = $this;
        $this->container = new Container($settings, $services);

        parent::__construct([
            'name' => $name,
            'version' => $version
        ]);
        $this->loadBuiltInCommands();
    }

    /**
     * loadBuiltInCommands
     */
    public function loadBuiltInCommands()
    {
        foreach (static::$bootstraps as $command) {
            $this->command($command::$name, $command);
        }
    }

    /**
     * @param $name
     * @param $handler
     * @return $this
     */
    public function add($name, $handler)
    {
        return $this->command($name, $handler);
    }

    /**
     * Enable access to the DI container by consumers of $app
     *
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
