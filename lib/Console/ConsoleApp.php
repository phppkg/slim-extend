<?php

namespace SlimExt\Console;

use Inhere\Console\Application;
use Inhere\Console\IO\Input;
use Inhere\Console\IO\Output;
use Psr\Container\ContainerInterface;

use SlimExt\Components\QuicklyGetServiceTrait;
use SlimExt\Console\Commands\AssetPublishCommand;
use SlimExt\Console\Commands\CommandUpdateCommand;
use SlimExt\Console\Controllers\AppController;
use SlimExt\Console\Controllers\GeneratorController;

/**
 * Class ConsoleApp
 * @package SlimExt\Console
 */
class ConsoleApp extends Application
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
            // AppController::class,
            GeneratorController::class,
        ],
    ];

    /**
     * Constructor.
     *
     * @param Input|null $input
     * @param Output|null $output
     * @internal string $name The name of the application
     * @internal string $version The version of the application
     */
    public function __construct(Input $input = null, Output $output = null)
    {
        self::$internalOptions['--env'] = sprintf(
            'Manually specify the current environment name. allow: %s', implode(',', APP_ENV_LIST)
        );

        parent::__construct([
            'name' => config('name', 'Blog Console'),
            'version' => config('version', '1.0.1')
        ], $input, $output);

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
        /** @var \Inhere\Library\Collections\Configuration $config */
        $config = $this->getContainer()['config'];

        // `$name` is array, set config.
        if (\is_array($name)) {
            foreach ((array) $name as $key => $value) {
                $config[$key] = $value;
            }

            return true;
        }

        return $config->get($name, $default);
    }

    /**
     * @return \Inhere\Library\Collections\Configuration
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

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
