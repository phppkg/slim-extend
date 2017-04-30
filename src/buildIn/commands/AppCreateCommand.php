<?php

namespace slimExt\buildIn\commands;

use Slim;
use slimExt\base\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * jump to the project root directory. run:
 * `./bin/console build:init {type}`
 * see help: `./bin/console build:init --help`
 */
class AppCreateCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:create')
            ->setDescription('build base structure of the project')
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'Who do you want to clear cache of type?'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            );
    }


    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('hello');
    }
}