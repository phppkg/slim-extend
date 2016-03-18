<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/3/18
 * Time: 11:05
 */

namespace slimExtend\exceptions;

/**
 * Class ParseDataException
 * @package slimExtend\exceptions
 */
class ParseDataException extends \RuntimeException
{
    public function __construct($msg = '', $code = 14, \Exception $previous = null)
    {
        parent::__construct($msg ? : 'data parse failure!!!', $code, $previous);
    }
}