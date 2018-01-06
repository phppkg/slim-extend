<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace SlimExt;

/**
 * Class DataConst
 *
 * @deprecated The class will be deprecated, please use `SlimExt\DataType` instead of it
 * @package SlimExt
 */
abstract class DataConst
{
    const FLASH_MSG_KEY = 'alert_messages';
    const FLASH_OLD_INPUT_KEY = 'old_inputs';

    // php data type
    const TYPE_INT = 'int';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_DOUBLE = 'double';
    const TYPE_BOOL = 'bool';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_STRING = 'string';

    const TYPE_ARRAY = 'array';
    const TYPE_OBJECT = 'object';
    const TYPE_RESOURCE = 'resource';
}
