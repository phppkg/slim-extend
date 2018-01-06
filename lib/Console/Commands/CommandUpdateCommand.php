<?php

namespace SlimExt\Console\Commands;

use Inhere\Console\Command;
use inhere\library\files\Directory;

/**
 * jump to the project root directory. run:
 * `./console demo:greet {name}`
 * see help: `./console command:update --help`
 * 命令行的参数是按位置赋予的
 */
class CommandUpdateCommand extends Command
{
    protected static $name = 'command:update';

    protected static $description = 'Will scan <info>@src/commands</info> directory for update application command list.';

    protected function configure()
    {
        /*$this
            ->setName('command:update')
            // 命令描述
            ->setDescription('Will scan <info>@src/commands</info> directory for update application command list.')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'If set, will force update <info>@project/bootstrap/console/commands.php</info>',
                false
            );*/
    }

    protected $targetFile = '@project/boot/console/commands.php';
    protected $tplFile = '@project/resources/templates/commands.tpl';

    /**
     * Generator a logic class of the project
     * @arguments
     *  path        the commands class file path dir(<cyan>@src/console/commands</cyan>)
     *  namespace   the commands class namespace(<cyan>app\console\commands</cyan>)
     *
     * @options
     *  -o,--override    whether override exists's file. (<info>false</info>)
     *
     * @return int
     */
    protected function execute($input, $output)
    {
        $namespace = $input->get('namespace', 'app\console\commands');
        $path = $input->get('path', '@src/console/commands');
        $dir = \Slim::alias($path);

        $output->writeln("Begin scan application command:(in <info>{$dir}</info>)");

        $ret = Directory::findFiles($dir, [
            'include' => [
                'ext' => ['php']
            ]
        ], true);

        if (!$ret) {
            $output->liteWarning('  Not found any command class. Bye!');

            return 0;
        }

        $output->writeln('Collection command:');
        $tplCmd = '$app->add(new %s);';
        $cmdList = [];

        foreach ($ret as $value) {
            $class = $namespace . substr(str_replace('/', '\\', $value), 0, -4);
            $cmdList[] = sprintf($tplCmd, $class);
            $output->writeln('  ' . $class);
        }

        $targetFile = \Slim::alias($this->targetFile);
        $override = $input->sameOpt(['o', 'override']);

        if (
            !$override &&
            file_exists($targetFile) &&
            false === $this->confirm('Found commands.php, are you want to override it!', false)
        ) {
            $output->writeln('  Exists update. Bye!');

            return 0;
        }

        $content = <<<EOF
<?php
/**
 * entry file is `{project}/console`
 * register console command
 * @var \$app \SlimExt\\console\App
 */

// \$app->add(new \app\commands\GreetCommand);
EOF;

        $content .= "\n" . implode("\n", $cmdList);

        if (file_put_contents($targetFile, $content)) {
            $output->writeln("\n" . '<info>Update commands class successful!</info>');

            return 0;
        }

        $output->writeln("\n" . '<error>Update commands class failure!</error>');

        return 1;
    }
}
