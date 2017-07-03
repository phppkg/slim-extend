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
            $this->command($command::getName(), $command);
        }
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
