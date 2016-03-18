<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExtend;

/**
 * Class DataConst
 * @package slimExtend
 */
abstract class DataConst
{
    const FLASH_MSG_KEY         = 'alert_messages';
    const FLASH_OLD_INPUT_KEY   = 'old_inputs';

    // php data type
    const TYPE_INT              = 'int';
    const TYPE_INTEGER          = 'integer';
    const TYPE_FLOAT            = 'float';
    const TYPE_DOUBLE           = 'double';
    const TYPE_BOOL             = 'bool';
    const TYPE_BOOLEAN          = 'boolean';
    const TYPE_STRING           = 'string';

    const TYPE_ARRAY            = 'array';
    const TYPE_OBJECT           = 'object';
    const TYPE_RESOURCE         = 'resource';

    /**
     * @return array
     */
    public static function dataTypes()
    {
        return [
            static::TYPE_ARRAY, static::TYPE_BOOL, static::TYPE_BOOLEAN , static::TYPE_DOUBLE, static::TYPE_FLOAT,
            static::TYPE_INT,   static::TYPE_INTEGER, static::TYPE_OBJECT, static::TYPE_STRING, static::TYPE_RESOURCE
        ];
    }

    /**
     * @return array
     */
    public static function scalarTypes()
    {
        return [
            static::TYPE_BOOL, static::TYPE_BOOLEAN , static::TYPE_DOUBLE, static::TYPE_FLOAT,
            static::TYPE_INT, static::TYPE_INTEGER, static::TYPE_STRING
        ];
    }
}