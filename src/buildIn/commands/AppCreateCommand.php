<?php

namespace slimExt\buildIn\commands;

use inhere\console\Command;

/**
 * jump to the project root directory. run:
 * `./bin/console build:init {type}`
 * see help: `./bin/console build:init --help`
 */
class AppCreateCommand extends Command
{
    public static $description = 'build base structure of the project';

    /**
     * {@inheritDoc}
     */
    protected function execute($input, $output)
    {
        $output->write('hello');
    }
}