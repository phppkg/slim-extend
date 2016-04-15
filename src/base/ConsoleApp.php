<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExtend\base;

use Pimple\Container;
use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Class ConsoleApp
 * @package slimExtend\base
 */
class ConsoleApp extends Application
{
    protected $container;

    /**
     * Constructor.
     *
     * @param string $name    The name of the application
     * @param string $version The version of the application
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $this->container = new Container;

        parent::__construct($name, $version);
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
}