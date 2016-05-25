<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\base;

use Slim;
use slimExt\database\AbstractDriver;
use inhere\validate\ValidationTrait;
use slimExt\DataConst;
use Windwalker\Query\Query;

/**
 * Class BaseModel
 * @package slimExt
 *
 */
abstract class Model extends Collection
{
    use ValidationTrait;

    protected $enableValidate = true;

    /**
     * @var array
     */
    private $_oldData = [];

    /**
     * the table primary key name
     * @var string
     */
    protected static $priKey = 'id';

    /**
     * current table name alias
     * 'mt' -- main table
     * @var string
     */
    protected static $aliasName = 'mt';

    /**
     * callable method List at Sqlite
     * @var array
     */
    protected static $callableList = [
        'insert', 'update', 'save', 'updateBatch'
    ];

    const SCENE_CREATE = 'create';
    const SCENE_UPDATE = 'update';

    protected static $baseOptions = [
        'indexKey' => null,
        /*
        data type, in :
            'model'      -- return object, instanceof `self`
            '\\stdClass' -- return object, instanceof `stdClass`
            'array'      -- return array, only  [ column's value ]
            'assoc'      -- return array, Contain  [ column's name => column's value]
        */
        'class'    => 'model',

        // 追加限制
        // 可用方法: limit($limit, $offset), group($columns), having($conditions, $glue = 'AND')
        // innerJoin($table, $condition = []), leftJoin($table, $condition = []), order($columns),
        // outerJoin($table, $condition = []), rightJoin($table, $condition = [])
        // e.g:
        //  'limit' => [10, 120],
        //  'order' => 'createTime ASC',
        //  'group' => 'id, type',
        //
    ];

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
     * if {@see static::$aliasName} not empty, return `tableName AS aliasName`
     * @return string
     */
    public static function queryName()
    {
        return static::$aliasName ? static::tableName() . ' AS ' . static::$aliasName : static::tableName();
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
     * @param mixed $where
     * @return Query
     */
    public static function query($where=null)
    {
        return static::handleWhere($where, static::getQuery(true))->from(static::queryName());
    }

    /**
     * find record by primary key
     * @param mixed $priValue
     * @param string $select
     * @param array $options
     * @return static|array
     */
    public static function findByPk($priValue, $select='*', array $options= [])
    {
        // only one
        $where = [static::$priKey => $priValue];

        // many
        if ( is_array($priValue) ) {
            $where = static::$priKey . ' in (' . implode(',', $priValue) . ')';
        }

        return static::findOne($where, $select, $options);
    }

    /**
     * find a record by where condition
     * @param mixed $where
     * @param string $select
     * @param array $options
     * @return static|array
     */
    public static function findOne($where, $select='*', array $options= [])
    {
        $query = static::query($where)->select($select);

        $options = array_merge(static::$baseOptions, $options);
        $class = $options['class'] === 'model' ? static::class : $options['class'];

        unset($options['indexKey'], $options['class']);
        static::applyAppendOptions($options, $query);

        return static::setQuery($query)->loadOne($class);
    }

    /**
     * @param mixed $where {@see self::handleWhere() }
     * @param string|array $select
     * @param array $options
     * @return array
     */
    public static function findAll($where, $select='*', array $options = [])
    {
        $query = static::query($where)->select($select);

        $options = array_merge(static::$baseOptions, $options);
        $indexKey = $options['indexKey'];
        $class = $options['class'] === 'model' ? static::class : $options['class'];

        unset($options['indexKey'], $options['class']);

        static::applyAppendOptions($options, $query);

        return static::setQuery($query)->loadAll($indexKey, $class);
    }

    /**
     * simple count
     * @param $where
     * @return int
     */
    public static function counts($where)
    {
        $query = static::query($where);

        return static::setQuery($query)->count();
    }

    /**
     * @param $where
     * @return int
     */
    public static function exists($where)
    {
        $query = static::query($where);

        return static::setQuery($query)->exists();
    }

    /**
     * @param array $updateColumns
     * @param bool|false $updateNulls
     * @return bool
     */
    public function save($updateColumns = [], $updateNulls = false)
    {
        $this->beforeSave();

        $result = $this->isNew() ? $this->insert() : $this->update($updateColumns, $updateNulls);

        if ($result) {
            $this->afterSave();
        }

        return $result ? true : false;
    }

    /**
     * @return static
     */
    public function insert()
    {
        $this->beforeInsert();
        $this->beforeSave();

        if ( $this->enableValidate && $this->validate()->fail() ) {
            return false;
        }

        $priValue = static::getDb()->insert( static::tableName(), $this->all());

        // when insert successful.
        if ($priValue) {
            $this->set(static::$priKey, $priValue);

            $this->afterInsert();
            $this->afterSave();
        }

        return $this;
    }

    /**
     * insert multiple
     * @param array $dataSet
     * @return array
     */
    public static function insertMulti(array $dataSet)
    {
        $pris = [];

        // return static::getDb()->insertMulti($table, $dataSet, $priKey);
        foreach ($dataSet as $k => $data) {
            $pris[$k] = static::load($data)->insert()->priValue();
        }

        return $pris;
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
        $this->beforeSave();

        // validate data
        if ($this->enableValidate && $this->validate(array_keys($data))->fail() ) {
            return false;
        }

        $result = static::getDb()->update( static::tableName(), $data, $priKey, $updateNulls);

        if ($result) {
            $this->afterUpdate();
            $this->afterSave();
        }

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
        $res = [];

        foreach ($dataSet as $k => $data) {
            $res[$k] = static::load($data)->update($updateColumns, $updateNulls);
        }

        return $res;
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
     * delete by model
     * @return int
     */
    public function delete()
    {
        $this->beforeDelete();

        if ( !$priValue = $this->priValue() ) {
            return 0;
        }

        $query = static::handleWhere([ static::$priKey => $priValue ])->delete(static::tableName());

        if ($affected = static::setQuery($query)->execute()->countAffected() ) {
            $this->afterDelete();
        }

        return $affected;
    }

    /**
     * @param int|array $priValue
     * @return int
     */
    public static function deleteByPk($priValue)
    {
        // only one
        $where = [static::$priKey => $priValue];

        // many
        if ( is_array($priValue) ) {
            $where = static::$priKey . ' in (' . implode(',', $priValue) . ')';
        }

        $query = static::handleWhere($where)->delete(static::tableName());

        return static::setQuery($query)->execute()->countAffected();
    }

    /**
     * @param $where
     * @return int
     */
    public static function deleteBy($where)
    {
        $query = static::handleWhere($where, static::getQuery(true))->delete(static::tableName());

        return static::setQuery($query)->execute()->countAffected();
    }

    protected function beforeInsert(){}
    protected function afterInsert(){}
    protected function beforeUpdate(){}
    protected function afterUpdate(){}
    protected function beforeSave(){}
    protected function afterSave(){}
    protected function beforeDelete(){}
    protected function afterDelete(){}

    /**
     * @param $column
     * @param int $step
     * @return bool
     */
    public function increment($column, $step=1)
    {
        $priKey = static::$priKey;

        if ( !is_int($this->$column) ) {
            throw new \InvalidArgumentException('The method only can be used in the column of type integer');
        }

        $this->$column += (int)$step;

        $data = [
            $priKey => $this->get($priKey),
            $column => $this->$column,
        ];

        return static::getDb()->update(static::tableName(), $data, $priKey);
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

        if ( !is_int($this->$column) ) {
            throw new \InvalidArgumentException('The method only can be used in the column of type integer');
        }

        $this->$column += (int)$step;

        $data = [
            $priKey => $this->get($priKey),
            $column => $this->$column,
        ];

        return static::getDb()->update(static::tableName(), $data, $priKey);
    }
    public function decre($column, $step=-1)
    {
        return $this->decrement($column, $step);
    }

    /**
     * @param null|bool $value
     * @return bool
     */
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
     * findXxx 无法满足需求时，自定义 $query
     * `
     * $query = XModel::getQuery();
     * ...
     * self::setQuery($query)->loadAll(null, self::class);
     * `
     * @param string|Query $query
     * @return AbstractDriver
     */
    final public static function setQuery($query)
    {
        return static::getDb()->setQuery($query);
    }

    /**
     * apply Append Options
     * @param  array  $appends
     * @param  Query  $query
     */
    protected static function applyAppendOptions($appends=[], Query $query)
    {
        foreach ($appends as $method => $val) {
            is_array($val) ? $query->$method($val[0],$val[1]) : $query->$method($val);
        }
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
    protected static function handleWhere($where, Query $query = null)
    {
        $query = $query ?: static::getQuery(true);

        /* e.g:
        a Closure
        function(Query $q) use ($value) {
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

                // natural int key
                } else if ( is_int($key) ) {

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
                    $subWhere = $key . ' = ' . (is_numeric($subWhere) ? (int)$subWhere : $query->q($subWhere));
                }

                $query->where($subWhere, $glue);
            }// end foreach

        } elseif ( $where && is_string($where) ) {
            $query->where($where);
        }

        return $query;
    }

    /**
     * format column's data type
     * @param string $column
     * @param mixed $value
     * @return $this|void
     */
    public function set($column, $value)
    {
        if ( isset($this->columns()[$column]) ) {
            $type = $this->columns()[$column];

            if ($type === DataConst::TYPE_INT ) {
                $value = (int)$value;
            }

            // backup old value.
            if ( !$this->isNew() ) {
                $this->_oldData[$column] = $this->get($column);
            }

            return parent::set($column, $value);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function priValue()
    {
        return $this->get(static::$priKey);
    }

    /**
     * Check whether the column's value is changed, the update.
     * @param $column
     * @return bool
     */
    protected function valueIsChanged($column)
    {
        if ( $this->isNew() ) {
            return true;
        }

        return $this->get($column) !== $this->getOld($column);
    }

    /**
     * @return array
     */
    public function getOldData()
    {
        return $this->_oldData;
    }

    /**
     * @param $column
     * @return mixed
     */
    public function getOld($column)
    {
        return isset($this->_oldData[$column]) ? $this->_oldData[$column] : null;
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