<?php
/**
 * Created by slimExt.
 * User: {@author}
 * Date: {@date}
 * Time: {@time}
 */

namespace {@namespace};

use {@parentClass};

/**
 * Class {@className}
 * @package {@namespace}
 */
class {@className} extends {@parentName}
{
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
    public function execute($input, $output)
    {
        // do something ...
        $this->write('hello, this is: ' . __METHOD__);

        return 0;
    }
}
