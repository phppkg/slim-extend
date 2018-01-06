
    /**
     * Description of the group command
     * @usage {command} [arg0 name=value name1=value1] [--opt]
     * @arguments
     *  name    the required arg<red>*</red>
     *  name1   the optional arg. (<cyan>default_value</cyan>)
     * @options
     *  --long-opt this is a long option(<info>default_value</info>)
     *  -s         this is a short option
     *
     * @param \Inhere\Console\IO\Input $input
     * @param \Inhere\Console\IO\Output $output
     * @return int
     */
    public function {@action}{@suffix}($input, $output)
    {
        // do something ...
        $this->write('hello, this is: ' . __METHOD__);

        return 0;
    }
