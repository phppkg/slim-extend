<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午6:18
 */

namespace slimExt\buildIn\commands;

use inhere\library\asset\AssetPublisher;
use slimExt\base\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AssetPublishCommand
 * @package slimExt\buildIn\commands
 */
class AssetPublishCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('asset:publish')
            // 命令描述
            ->setDescription('publish static asset to web access directory. [<info>built in</info>]
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
            // 选项名 '--sourcePath value'
                'source-path',
                // 选项名缩写 '-s value'
                // 注意 选项名缩写首字母不能出现相同的,不然会解析混乱. 这里使用了 s, 后面的选项缩写就不能再有以 s 开头的缩写名了
                's',
                InputOption::VALUE_REQUIRED, // InputOption::VALUE_REQUIRED,
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
                'o',
                InputOption::VALUE_OPTIONAL,
                'If set, the publish will override existing asset',
                false
            )
            ->addOption(
                'show-published',
                null,
                InputOption::VALUE_OPTIONAL,
                'If set, will print published asset list',
                true
            );
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourcePath = \Slim::alias($input->getOption('source-path'));
        $publishPath = \Slim::alias($input->getOption('publish-path'));

        $publisher = new AssetPublisher([
            'sourcePath' => $sourcePath,
            'publishPath' => $publishPath,
        ]);

        $io = $this->getIO();

        /*
         e.g.
         $asset = [
            'bootstrap3-typeahead',
            'jquery',
            'yii2-pjax'
        ]
         */
        $asset = $input->getArgument('asset');
        $override = $input->getOption('override');

        $io->title('    Asset Publish Information    ');
        $io->writeln([
            'Will publish: [<info>' . ($asset ? implode(',', $asset) : 'ALL FILES') . '</info>]',
            "source in path: <info>$sourcePath</info>",
            "publish to path: <info>$publishPath</info>",
            'override existing asset: <info>' . ($override ? 'Yes' : 'No') . '</info>',
        ]);

        $answer = $io->confirm('Are you sure publish?', true);

        if (!$answer) {
            $this->info($output, 'You want\'t to do publish, at now. GoodBye!!');

            return 1;
        }

        $publisher->add($asset)->publish();

        if ($input->getOption('show-published')) {
            $published = $publisher->getPublishedAssets();

            // $output->writeln('<info>-- Created asset publish:</info>');
            $io->section('-- Created asset publish:');
            $output->writeln($published['created'] ?: 'No file created.');
            $output->writeln('');

            $io->section('-- Skipped asset publish:');
            $output->writeln($published['skipped'] ?: 'No file skipped.');
            $output->writeln('');
        }

        $output->writeln('<info>Publish asset successful!</info>');

        return 0;
    }
}
