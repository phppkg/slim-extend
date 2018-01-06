<?php

namespace SlimExt\Console\Controllers;

use Inhere\Console\Controller;

/**
 * jump to the project root directory. run:
 * `./bin/console build:init {type}`
 * see help: `./bin/console build:init --help`
 */
class AppController extends Controller
{
    protected static $name = 'app';

    protected static $description = 'build base structure of the project. [<info>built in</info>]';

    /**
     * init the application
     */
    public function initCommand()
    {
        $this->write('hello');
    }
}
