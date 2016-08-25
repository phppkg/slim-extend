<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午6:18
 */

namespace slimExt\builder\commands;

use inhere\librarys\asset\AssetPublisher;
use inhere\librarys\traits\TraitUseOption;
use slimExt\base\Command;
use inhere\librarys\exceptions\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AssetPublishCommand
 * @package slimExt\builder\commands
 */
class AssetPublishCommand extends Command
{
    use TraitUseOption;

    public $options = [
        'assetsPath'  => '',
        'publishPath' => '',

        'includeFile' => ['README.md'],
        'includeExt' => ['js','css'],
        'includeDir' => ['src'],

        'excludeFile' => '.gitignore',
        'excludeExt' => '.git, .gitignore',
        'excludeDir' => '.git, .gitignore',
    ];

    /**
     * will use it, when asset is relation path
     * @var string
     */
    public $basePath = '';

    /**
     * @var array
     */
    public $assets = [
        // 'path' allow dir path, file path
        // e.g.
        // '/zz/path',
        // '/xx/path/app.js',
        // '/xx/path/app.css',
    ];

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
            ->setDescription('publish static asset to directory.')
            // 配置第一个参数位置，参数名 name e.g. $ console asset:publish [name]
            // 是可选的参数
            // 参数说明
            ->addArgument(
                'name',
                InputArgument::OPTIONAL, // 可选的参数 InputArgument::REQUIRED 必须的
                'Who do you want to greet?'
            )
            // 配置选项 e.g. $ console asset:publish [name] [--yell|-y value]
            ->addOption(
                '--yell',
                '-y',// 选项缩写
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        ;
    }

    protected function execute($input, $output)
    {
        $publisher = new AssetPublisher();
        $publisher->publish();

    }
}