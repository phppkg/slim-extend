<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午6:20
 */

namespace slimExt\base;

use Slim;
use Symfony\Component\Console\Command\Command as SfCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 * @package slimExt\base
 */
class Command extends SfCommand
{
    /**
     * some options of the class
     * @var array
     */
    protected $options = [];

    /**
     * @param null|string $name
     */
    public function __construct($name = null)
    {
        $this->beforeConstruct();

        parent::__construct($name);

        $this->afterConstruct();
    }

    protected function beforeConstruct(){}

    protected function afterConstruct(){}

    /**
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {}

    /**
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {}
}