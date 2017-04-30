<?php

namespace slimExt\console;

use slimExt\base\Container;
use Interop\Container\ContainerInterface;

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
     * Constructor.
     *
     * @param array $settings
     * @param array $services
     * @param string $name The name of the application
     * @param string $version The version of the application
     */
    public function __construct(array $settings = [], array $services = [], $name = 'Inhere Console', $version = '1.0.1')
    {
        $this->container = new Container($settings, $services);

        parent::__construct([
            'name' => $name,
            'version' => $version
        ]);
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
