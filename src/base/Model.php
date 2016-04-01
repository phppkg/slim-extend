<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExtend\base;

use Slim;
use slimExtend\database\AbstractDriver;
use inhere\validate\ValidatorTrait;
use slimExtend\DataConst;
use Windwalker\Query\Query;

/**
 * Class BaseModel
 * @package slimExtend
 *
 */
abstract class Model extends Collection
{
    use ValidatorTrait;

    protected $enableValidate = true;

    /**
     * the table primary key name
     * @var string
     */
    protected static $priKey = 'id';

    /**
     * callable method List at Sqlite
     * @var array
     */
    protected static $callableList = [
        'insert', 'update', 'save', 'updateBatch'
    ];

    const SCENE_CREATE = 'create';
    const SCENE_UPDATE = 'update';

    /**
     * define model field list
     * e.g:
     * in sub class
     * ```
     * public function columns()
     * {
     *    return [
     *          // column => type
     *          'id'          => 'int',
     *          'title'       => 'string',
     *          'createTime'  => 'int',
     *          'updateTime'  => 'int',
     *    ];
     * }
     * ```
     *
     * @return array
     */
    abstract public function columns();


    /**
     * @return string
     */
    public static function tableName()
    {
        $className = lcfirst( basename( str_replace('\\', '/', get_called_class()) ) );

        // '@@' -- is table prefix placeholder
        // return '@@articles';
        // if no table prefix
        // return 'articles';

        return '@@' . $className;
    }

    /**
     * @return AbstractDriver
     */
    public static function getDb()
    {
        return Slim::get('db');
    }

    /**
     * @param $data
     * @return static
     */
    public static function load($data)
    {
        return new static($data);
    }

    /**
     * init query
     * @return Query
     */
    public static function query()
    {
        return static::getQuery(true)->from(static::tableName());
    }

    /**
     * find record by primary key
     * @param mixed $priValue
     * @param string $select
     * @param string $class
     * @return static
     */
    public static function findByPk($priValue, $select='*', $class= 'model')
    {
        if ( is_array($priValue) ) {
            $condition = static::$priKey . ' in (' . implode(',', $priValue) . ')';
        } else {
            $condition = static::$priKey . '=' . $priValue;
        }

        return static::findOne($condition, $select, $class);
    }

    /**
     * find a record by where condition
     * @param $where
     * @param string $select
     * @param string $class
     * @return static|\stdClass|array
     */
    public static function findOne($where, $select='*', $class= 'model')
    {
        $query = static::handleWhere($where, static::getQuery(true) )
                ->select($select)
                ->from(static::tableName());

        return static::getDb()->setQuery($query)->loadOne($class === 'model' ? static::class : $class);
    }

    /**
     * @param mixed $where {@see self::handleWhere() }
     * @param string|array $select
     * @param null|string $indexKey
     * @param string $class data type, in :
     *  'model'      -- return object, instanceof `self`
     *  '\\stdClass' -- return object, instanceof `stdClass`
     *  'array'      -- return array, only  [ column's value ]
     *  'assoc'      -- return array, Contain  [ column's name => column's value]
     * @return array
     */
    public static function findAll($where, $select='*', $indexKey=null, $class= 'model')
    {
        $query = static::handleWhere( $where, static::getQuery(true) )
                ->select($select)
                ->from(static::tableName());

        return static::getDb()->setQuery($query)->loadAll($indexKey, $class === 'model' ? static::class : $class);
    }


    /**
     * @param bool|false $updateNulls
     * @return bool|static
     */
    public function save($updateNulls = false)
    {
        $this->beforeSave();

        $result = static::getDb()->save( static::tableName(), $this->data, static::$priKey, $updateNulls);

        $this->afterSave($result);

        return $result;
    }

    /**
     * @return array|bool|int
     */
    public function insert()
    {
        $this->beforeInsert();

        if ( $this->enableValidate && $this->validate()->fail() ) {
            return false;
        }

        $priValue = static::getDb()->insert( static::tableName(), $this->all());

        $this->set(static::$priKey, $priValue);

        $this->afterInsert($priValue);

        return $priValue;
    }

    /**
     * insert multiple
     * @param array $dataSet
     * @return array
     */
    public static function insertMulti(array $dataSet)
    {
        // return static::getDb()->insertMulti($table, $dataSet, $priKey);
        foreach ($dataSet as $k => $data) {
            $dataSet[$k] = static::load($data)->insert();
        }

        return $dataSet;
    }

    /**
     * update by primary key
     * @param array $updateColumns only update some columns
     * @param bool|false $updateNulls
     * @return bool
     */
    public function update($updateColumns = [], $updateNulls = false)
    {
        $data = $this->all();
        $priKey = static::$priKey;

        // only update some columns
        if ( $updateColumns ) {
            foreach ($data as $column => $value) {
                if ( !in_array($column,$updateColumns)  ) {
                    unset($data[$column]);
                }
            }

            if ( !isset($data[$priKey]) ) {
                $data[$priKey] = $this->get($priKey);
            }
        }

        $this->beforeUpdate();

        // validate data
        if ($this->enableValidate && $this->validate(array_keys($data))->fail() ) {
            return false;
        }

        $result = static::getDb()->update( static::tableName(), $data, $priKey, $updateNulls);

        $this->afterUpdate($result);

        return $result;
    }

    /**
     * @param $dataSet
     * @param array $updateColumns
     * @param bool|false $updateNulls
     * @return mixed
     */
    public static function updateMulti($dataSet, $updateColumns = [], $updateNulls = false)
    {
        foreach ($dataSet as $k => $data) {
            $dataSet[$k] = static::load($data)->update($updateColumns, $updateNulls);
        }

        return $dataSet;
    }

    /**
     * @param $data
     * @param array $conditions
     * @return bool
     */
    public static function updateBatch($data, $conditions = [])
    {
        return static::getDb()->updateBatch( static::tableName(), $data, $conditions);
    }

    /**
     * @param $column
     * @param int $step
     * @return bool
     */
    public function increment($column, $step=1)
    {
        $priKey = static::$priKey;

        if ( !is_integer($this->$column) ) {
            throw new \InvalidArgumentException('The method only can be used in the column of type integer');
        }

        $this->$column += (int)$step;

        $data = [
            $priKey => $this->get($priKey),
            $column => $this->$column,
        ];

        $result = static::getDb()->update(static::tableName(), $data, $priKey);

        return $result;
    }
    public function incre($column, $step=1)
    {
        return $this->increment($column, $step);
    }

    /**
     * @param $column
     * @param int $step
     * @return bool
     */
    public function decrement($column, $step=-1)
    {
        $priKey = static::$priKey;

        if ( !is_integer($this->$column) ) {
            throw new \InvalidArgumentException('The method only can be used in the column of type integer');
        }

        $this->$column += (int)$step;

        $data = [
            $priKey => $this->get($priKey),
            $column => $this->$column,
        ];

        $result = static::getDb()->update(static::tableName(), $data, $priKey);

        return $result;
    }
    public function decre($column, $step=-1)
    {
        return $this->decrement($column, $step);
    }

    protected function beforeInsert()
    {
        $this->beforeSave();
    }

    protected function beforeUpdate()
    {
        $this->beforeSave();
    }

    protected function afterInsert($result)
    {
        $this->afterSave($result);
    }

    protected function afterUpdate($result)
    {
        $this->afterSave($result);
    }

    protected function beforeSave()
    {}

    protected function afterSave($result)
    {}

    public function enableValidate($value=null)
    {
        if ( is_bool($value) ) {
            $this->enableValidate = $value;
        }

        return $this->enableValidate;
    }

    /**
     * @param bool $forceNew
     * @return Query
     */
    final public static function getQuery($forceNew=false)
    {
        return static::getDb()->newQuery($forceNew);
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return !($this->has(static::$priKey) && $this->get(static::$priKey, false));
    }

    /**
     * `self::setQuery($query)->loadAll(null, self::class);`
     * @param string|Query $query
     * @return AbstractDriver
     */
    final public static function setQuery($query)
    {
        return static::getDb()->setQuery($query);
    }

    /**
     * @param string|array|\Closure $where
     * @param Query $query
     * @example
     * ```
     * ...
     * $result = XxModel::findAll([
     *      'userId = 23',
     *      'publishTime > 0',
     *      'title' => 'test', // equal to "title = 'test'"
     *      'status' => [3, 'or'], // equal to "OR status = 3"
     *      ['categoryId > 23', 'or'], // equal to "OR categoryId > 23"
     * ]);
     *
     * ```
     * @return Query
     */
    protected static function handleWhere($where, Query $query)
    {
        /* e.g:
        Closure function(Query $q) use ($value) {
            $q->where( 'column = ' . $q->q($value) );
        }
        */
        if (is_object($where) and $where instanceof \Closure) {
            $where($query);

            return $query;
        }

        if ( is_array($where) ) {
            $glue = 'AND';

            foreach ($where as $key => $subWhere) {
                if (is_object($where) and $where instanceof \Closure) {
                    $where($query);
                    continue;
                }

                // natural int key
                if ( is_integer($key) ) {

                    // e.g: $subWhere = [ "column = 'value'", 'OR' ];
                    if ( is_array($subWhere) ) {
                        list($subWhere, $glue) = $subWhere;
                        $glue = in_array(strtoupper($glue), ['AND', 'OR']) ? strtoupper($glue) : 'AND';
                    }

                    // $subWhere is string, e.g: $subWhere = "column = 'value'"; go on ...

                // string key, $key is a column name
                } elseif ( is_string($key) ) {

                    // e.g: $subWhere = [ 'value', 'OR' ];
                    if ( is_array($subWhere) ) {
                        list($subWhere, $glue) = $subWhere;

                        $glue = in_array(strtoupper($glue), ['AND', 'OR']) ? strtoupper($glue) : 'AND';
                    }

                    // $subWhere is a column value. e.g: $subWhere = 'value'; go on ...

                    $subWhere = $key . ' = ' . $query->q($subWhere);
                }

                $query->where($subWhere, $glue);
            }// end foreach
        } else {
            $query->where($where);
        }

        return $query;
    }


    /**
     * format column's data type
     * @param string $key
     * @param mixed $value
     * @return $this|void
     */
    public function set($key, $value)
    {
        if ( isset($this->columns()[$key]) ) {
            $type = $this->columns()[$key];

            if ($type === DataConst::TYPE_INT ) {
                $value = (int)$value;
            }

            return parent::set($key, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public static function callableList()
    {
        return static::$callableList;
    }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    // public function __call($method, array $args)
    // {
    //     $db = static::getDb();
    //     array_unshift($args, static::tableName());

    //     if ( in_array($method, static::$callableList) AND method_exists($db, $method) ) {
    //         return call_user_func_array( [$db, $method], $args);
    //     }

    //     throw new \RuntimeException("Called method [$method] don't exists!");
    // }

    /**
     * @param $method
     * @param array $args
     * @return mixed
     */
    // public static function __callStatic($method, array $args)
    // {
    //     $db = static::getDb();
    //     array_unshift($args, static::tableName());

    //     if ( in_array($method, static::$callableList) AND method_exists($db, $method) ) {
    //         return call_user_func_array( [$db, $method], $args);
    //     }

    //     throw new \RuntimeException("Called static method [$method] don't exists!");
    // }
}