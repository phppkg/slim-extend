<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\base;

use Pimple\Container;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use slimExt\builder\commands;

/**
 * Class ConsoleApp
 * @package slimExt\base
 */
class ConsoleApp extends Application
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $bootstraps = [
        '\slimExt\builder\commands\AppCreateCommand',
        '\slimExt\builder\commands\AssetPublishCommand',
        '\slimExt\builder\commands\CommandUpdateCommand',
    ];

    /**
     * Constructor.
     *
     * @param array $settings
     * @param string $name The name of the application
     * @param string $version The version of the application
     */
    public function __construct( array $settings = [],$name = 'Inhere Console', $version = '1.0.1')
    {
        $this->container = new Container($settings);

        parent::__construct($name, $version);

        $this->loadBuiltInCommands();
    }

    public function loadBuiltInCommands()
    {
        foreach ($this->bootstraps as $command) {
            $this->add(new $command);
        }
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

    /**
     * add a command
     * @param Command|string $command A command object or a command class name
     * @return Command The registered command
     */
    public function create($command)
    {
        if ( is_string($command) ) {
            $command = new $command;
        }

        return $this->add($command);
    }

    /**
     * @param array $commands
     * @return $this
     */
    public function registerCommands(array $commands = [])
    {
        foreach ($commands as $command) {
            $this->create($command);
        }

        return $this;
    }
}
