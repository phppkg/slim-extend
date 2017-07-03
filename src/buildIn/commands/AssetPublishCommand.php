<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午6:18
 */

namespace slimExt\buildIn\commands;

use inhere\libraryPlus\asset\AssetPublisher;
use inhere\console\Command;

/**
 * Class AssetPublishCommand
 * @package slimExt\buildIn\commands
 */
class AssetPublishCommand extends Command
{
    protected static $name = 'asset:publish';

    protected static $description = 'publish static asset to web access directory. [<info>built in</info>]';

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        /*$this
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
            );*/
    }

    protected function execute($input, $output)
    {
        $sourcePath = \Slim::alias($input->getOpt('source-path'));
        $publishPath = \Slim::alias($input->getOpt('publish-path'));

        $publisher = new AssetPublisher([
            'sourcePath' => $sourcePath,
            'publishPath' => $publishPath,
        ]);

        /*
         e.g.
         $asset = [
            'bootstrap3-typeahead',
            'jquery',
            'yii2-pjax'
        ]
         */
        $asset = $input->getArgument('asset');
        $override = $input->boolOpt('override');

        $output->title('    Asset Publish Information    ');
        $output->write([
            'Will publish: [<info>' . ($asset ? implode(',', $asset) : 'ALL FILES') . '</info>]',
            "source in path: <info>$sourcePath</info>",
            "publish to path: <info>$publishPath</info>",
            'override existing asset: <info>' . ($override ? 'Yes' : 'No') . '</info>',
        ]);

        if (!$this->confirm('Are you sure publish?')) {
            $output->info('You want\'t to do publish, at now. GoodBye!!');

            return 1;
        }

        $publisher->add($asset)->publish();

        if ($input->boolOpt('show-published')) {
            $published = $publisher->getPublishedAssets();

            // $output->writeln('<info>-- Created asset publish:</info>');
            $output->title('-- Created asset publish:');
            $output->writeln($published['created'] ?: 'No file created.');
            $output->writeln('');

            $output->title('-- Skipped asset publish:');
            $output->writeln($published['skipped'] ?: 'No file skipped.');
            $output->writeln('');
        }

        $output->writeln('<info>Publish asset successful!</info>');

        return 0;
    }
}
