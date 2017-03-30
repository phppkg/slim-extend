<?php

namespace slimExt\buildIn\commands;

use inhere\library\files\Directory;
use slimExt\base\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * jump to the project root directory. run:
 * `./console demo:greet {name}`
 * see help: `./console command:update --help`
 * 命令行的参数是按位置赋予的
 */
class CommandUpdateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('command:update')
            // 命令描述
            ->setDescription('Will scan <info>@src/commands</info> directory for update application command list.')
            ->addOption(
               'force',
               'f',
               InputOption::VALUE_OPTIONAL,
               'If set, will force update <info>@project/bootstrap/console/commands.php</info>',
                false
            )
        ;
    }

    protected $targetFile = '@project/bootstrap/console/commands.php';
    protected $tplFile = '@project/resources/templates/commands.tpl';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');
        $namespace = '\app\commands\\';
        $dir = \Slim::alias('@src/commands');

        $output->writeln('Begin scan application command: [in <info>@src/commands</info>]');

        $ret = Directory::findFiles($dir, [
            'include' => [
                'ext' => ['php']
            ]
        ], true);

        if (!$ret) {
            $output->writeln('  Not found any command class. Bye!');

            return 0;
        }

        $output->writeln('Collection command:');
        $tplCmd = '$app->add(new %s);';
        $cmdList = [];

        foreach ($ret as $value) {
            $class = $namespace . substr(str_replace('/', '\\',$value), 0, -4);
            $cmdList[] = sprintf($tplCmd, $class);
            $output->writeln('  ' . $class);
        }

        $targetFile = \Slim::alias($this->targetFile);

        if (
            !$force &&
            file_exists($targetFile) &&
            false === $this->getIO()->confirm('Found commands.php, are you want to override it!', false)
        ) {
            $output->writeln('  Exists update. Bye!');

            return 0;
        }

        $content = <<<EOF
<?php
/**
 * entry file is `{project}/console`
 * register console command
 * @var \$app \slimExt\\base\ConsoleApp
 */

// \$app->add(new \app\commands\GreetCommand);
EOF;

        $content .= "\n" . implode("\n", $cmdList);

        if ( file_put_contents($targetFile, $content) ) {

            // $this->getIO()->success('   Update commands class successful!');
            $output->writeln("\n" .'<info>Update commands class successful!</info>');

            return 0;
        }

        $output->writeln("\n" .'<error>Update commands class failure!</error>');

        return 1;
    }
}
