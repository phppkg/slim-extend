    /**
     * Description of the group command
     * @usage {command} [arg0 name=value name1=value1] [--opt]
     * @arguments
     *  name<red>*</red>    the required arg
     *  name1        the optional arg. default: <cyan>default_value</cyan>
     * @options
     *  --long-opt this is a long option
     *  -s         this is a short option
     *
     * @param \inhere\console\io\Input $input
     * @param \inhere\console\io\Output $output
     * @return int
     */
    public function {@name}Command($input, $output)
    {
        //
        $this->write('hello, this is: ' . __METHOD__);

        return 0;
    }
