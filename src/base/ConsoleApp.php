<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExtend\base;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Class ConsoleApp
 * @package slimExtend\base
 */
class ConsoleApp extends Application
{
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