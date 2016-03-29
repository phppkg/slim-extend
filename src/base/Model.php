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
     * @var array
     */
    protected $columns = [
        // 'id'          => 'int',
        // 'title'       => 'string',
        // 'createTime'  => 'int',
        // 'updateTime'  => 'int',
    ];

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
     * format column's data type
     * @param string $key
     * @param mixed $value
     * @return $this|void
     */
    public function set($key, $value)
    {
        if ( isset($this->columns[$key]) ) {
            $type = $this->columns[$key];

            if ($type === 'int') {
                $value = (int)$value;
            }

            return parent::set($key, $value);
        }

        return false;
    }

    /**
     * @param $data
     * @return $this
     */
    public function load($data)
    {
        return $this->sets($data);
    }

      /**
     * find record
     * @return Query
     */
    public static function find()
    {
        return static::getQuery(true)->from(static::tableName());
    }

    /**
     * find record by primary key
     * @param mixed $priValue
     * @param string $select
     * @return static
     */
    public static function findByPk($priValue, $select='*')
    {
        if ( is_array($priValue) ) {
            $condition = static::$priKey . ' in (' . implode(',', $priValue) . ')';
        } else {
            $condition = static::$priKey . '=' . $priValue;
        }

        return static::findOne($condition, $select);
    }

    /**
     * find a record by where condition
     * @param $where
     * @param string $select
     * @return static
     */
    public static function findOne($where, $select='*')
    {
        $query = static::getQuery(true)
                ->select($select)
                ->from(static::tableName())
                ->where($where);

        return static::getDb()->setQuery($query)->loadOne(static::class);
    }

    /**
     * @param $where
     * @param string $select
     * @return array
     */
    public static function findAll($where, $select='*')
    {
        $query = static::getQuery(true)
                ->select($select)
                ->from(static::tableName())
                ->where($where);

        return static::getDb()->setQuery($query)->loadAll(null, static::class);
    }

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
     * @param bool $forceNew
     * @return Query
     */
    public static function getQuery($forceNew=false)
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

        if ( $this->validate && $this->validate()->fail() ) {
            return false;
        }

        $priValue = static::getDb()->insert( static::tableName(), $this->all());

        $this->set(static::$priKey, $priValue);

        $this->afterInsert($priValue);

        return $priValue;
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
        if ($this->validate && $this->validate(array_keys($data))->fail() ) {
            return false;
        }

        $result = static::getDb()->update( static::tableName(), $data, $priKey, $updateNulls);

        $this->afterUpdate($result);

        return $result;
    }

    /**
     * @param $data
     * @param array $conditions
     * @return bool
     */
    public function updateBatch($data, $conditions = [])
    {
        return static::getDb()->updateBatch( static::tableName(), $data, $conditions);
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