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
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class Command
 *
 * built in color tag:
 *  info comment question error
 * usage:
 *  `<info>Operation successful!</info>`
 *
 * if you want to use more style, please create style instance.
 *
 * ```
 * use Symfony\Component\Console\Style\SymfonyStyle
 *
 * $style = new SymfonyStyle($input, $output);
 *
 * // $style->title($message);
 * // $style->success($message);
 * // ...
 * ```
 *
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
     * @var SymfonyStyle
     */
    protected $styleIO;

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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return SymfonyStyle
     */
    public function getIO(InputInterface $input, OutputInterface $output)
    {
        if (!$this->styleIO) {
            $this->styleIO = new SymfonyStyle($input, $output);
        }

        return $this->styleIO;
    }

    /**
     * @return \Symfony\Component\Console\Helper\QuestionHelper
     */
    public function getQuestionHelper()
    {
        return $this->getHelper('question');
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @param int $options
     */
    public function info($output, $msg, $options = 0)
    {
        $output->writeln("<info>$msg</info>", $options);
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @param int $options
     */
    public function comment($output, $msg, $options = 0)
    {
        $output->writeln("<comment>$msg</comment>", $options);
    }

    /**
     * @param OutputInterface $output
     * @param $msg
     * @param int $options
     */
    public function error($output, $msg, $options = 0)
    {
        $output->writeln("<error>$msg</error>", $options);
    }
}