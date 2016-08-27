<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午6:18
 */

namespace slimExt\builder\commands;

use inhere\librarys\asset\AssetPublisher;
use inhere\librarys\exceptions\InvalidArgumentException;
use inhere\librarys\files\FileFinder;
use inhere\librarys\traits\TraitUseOption;
use slimExt\base\Command;
use inhere\librarys\exceptions\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AssetPublishCommand
 * @package slimExt\builder\commands
 */
class AssetPublishCommand extends Command
{
    use TraitUseOption;

    protected function optionCheck()
    {
        if ( !$this->getOption('publishPath') ) {
            throw new InvalidOptionException('must be setting option [publishPath].');
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('asset:publish')
            // 命令描述
            ->setDescription('publish static asset to web access directory. [built in]
command example:
  <info> ./bin/console asset:publish -s @vendor/bower -p @public/assets/publish "bootstrap" "jquery" "yii2-pjax"</info>
            ')
            // 配置第一个参数位置，参数名 name e.g. $ console asset:publish [name]
            // 是可选的参数
            // 参数说明
            ->addArgument(
                'asset',
                InputArgument::IS_ARRAY, // OPTIONAL 可选的参数 REQUIRED 必须的
                'You want to publish\'s asset path. can use relative path. allow multi asset path.'
            )
            // 配置选项
            // e.g. $ console asset:publish [name] [--yell|-y value]
            ->addOption(
                'source-path',// 选项名 '--sourcePath value'
                's',          // 选项名缩写 '-s value'
                InputOption::VALUE_REQUIRED,
                'The asset source base path. is required.'
            )
            ->addOption(
                'publish-path',
                'p',
                InputOption::VALUE_REQUIRED,
                'The asset publish base path. is required.'
            )
            ->addOption(
                'override',
                'or',
                InputOption::VALUE_OPTIONAL,
                'If set, the publish will override existing asset',
                false
            )
            ->addOption(
                'show-published',
                'sp',
                InputOption::VALUE_OPTIONAL,
                'If set, will print published asset list',
                false
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publisher = new AssetPublisher([
            'sourcePath'  => \Slim::alias($input->getOption('source-path')),
            'publishPath' => \Slim::alias($input->getOption('publish-path')),
        ]);
//de($publisher, \Slim::config());
        // $input->isInteractive();

        /*
         e.g.
         $asset = [
            'bootstrap3-typeahead',
            'jquery',
            'yii2-pjax'
        ]
         */
        $asset = $input->getArgument('asset');

        if ( count($asset) === 0) {
            throw new InvalidArgumentException('Please provide asset dir to publish.');
        }

        $publisher->add($asset)->publish();
//de($input, $output);

        if ( $this->getOption('show-published') ) {
            $published = $publisher->getPublishedAssets();

            $output->writeln('<info>-- Created asset publish:</info>');
            $output->writeln($published['created'] ? : 'No file created.');

            $output->writeln('<info>-- Skipped asset publish:</info>');
            $output->writeln($published['skipped'] ? : 'No file skipped.');
        }


        $output->writeln('<info>Publish asset successful!</info>');
    }
}