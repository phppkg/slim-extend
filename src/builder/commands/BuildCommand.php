<?php

namespace slimExt\builder\commands;

use Slim;
use app\enums\CacheType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * jump to the project root directory. run:
 * `./console cache:clear {type}`
 * see help: `./console cache:clear --help`
 */
class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build:init')
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
            )
        ;
    }

    /**
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    /**
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');

        if ( $type === CacheType::FILE_TWIG ) {
            $re = exec('rm -rf ' . $twigPath, $outputInfo);
        } elseif ($type === CacheType::FILE_OUTPUT ) {
            $re = exec('rm -rf ' . $outputsPath, $outputInfo);
        } else {
            $re = exec('rm -rf ' . $twigPath, $outputInfo);
            $re = exec('rm -rf ' . $outputsPath, $outputInfo);
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }

        $output->writeln($text);
    }
}