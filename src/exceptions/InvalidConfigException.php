<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 22:50
 */

namespace slimExtend\exceptions;

/**
 * Class InvalidConfigException
 * @package slimExtend\exceptions
 */
class InvalidConfigException extends \RuntimeException
{
    public function __construct($msg = '', $code = 17, \Exception $previous = null)
    {
        parent::__construct($msg ? : 'invalid configuration information!!', $code, $previous);
    }
}