<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace SlimExt\Base;

use inhere\library\collections\SimpleCollection;
use inhere\validate\ValidationTrait;
use SlimExt\DataType;

/**
 * Class BaseModel
 * @package SlimExt
 *
 */
abstract class Model extends SimpleCollection
{
    use ValidationTrait;

    /**
     * @var bool
     */
    protected $enableValidate = true;

    /**
     * if true, will only save(insert/update) safe's data -- Through validation's data
     * @var bool
     */
    protected $onlySaveSafeData = true;

    /**
     * Validation class name
     */
    //protected $validateHandler = '\inhere\validate\Validation';

    /**
     * @param $data
     * @return static
     */
    public static function load($data)
    {
        return new static($data);
    }

    /**
     * define model field list
     * in sub class:
     * ```
     * public function columns()
     * {
     *    return [
     *          // column => type
     *          'id'          => 'int',
     *          'title'       => 'string',
     *          'createTime'  => 'int',
     *    ];
     * }
     * ```
     * @return array
     */
    abstract public function columns();

    public function translates()
    {
        return [
            // 'field' => 'translate',
            // e.g. 'name'=>'名称',
        ];
    }

    /**
     * format column's data type
     * @inheritdoc
     */
    public function set($column, $value)
    {
        // belong to the model.
        if (isset($this->columns()[$column])) {
            $type = $this->columns()[$column];

            if ($type === DataType::T_INT) {
                $value = (int)$value;
            }
        }

        return parent::set($column, $value);
    }

    /**
     * @return array
     */
    public function getColumnsData()
    {
        $source = $this->onlySaveSafeData ? $this->getSafeData() : $this;
        $data = [];

        foreach ($source as $col => $val) {
            if (isset($this->columns()[$col])) {
                $data[$col] = $val;
            }
        }

        return $data;
    }
}
