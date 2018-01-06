<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:13
 */

namespace slimExt\base;

use inhere\exceptions\InvalidArgumentException;
use inhere\exceptions\InvalidConfigException;
use inhere\exceptions\UnknownMethodException;
use inhere\library\helpers\Arr;
use slimExt\database\AbstractDriver;
use slimExt\helpers\ModelHelper;
use Windwalker\Query\Query;

/**
 * Class RecordModel
 * @package slimExt\base
 */
abstract class RecordModel extends Model
{
    use RecordModelExtraTrait;

    /**
     * @var array
     */
    private $_backup = [];

    const SCENE_DEFAULT = 'default';
    const SCENE_CREATE = 'create';
    const SCENE_UPDATE = 'update';
    const SCENE_DELETE = 'delete';
    const SCENE_SEARCH = 'search';

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
     * the table name
     * @var string
     */
    private static $tableName;

    /**
     * @var array
     */
    protected static $defaultOptions = [
        /* data index column. */
        'indexKey' => null,
        /*
        data type, in :
            'model'      -- return object, instanceof `self`
            '\\stdClass' -- return object, instanceof `stdClass`
            'array'      -- return array, only  [ column's value ]
            'assoc'      -- return array, Contain  [ column's name => column's value]
        */
        'class' => 'model',

        // 追加限制
        // 可用方法: limit($limit, $offset), group($columns), having($conditions, $glue = 'AND')
        // innerJoin($table, $condition = []), leftJoin($table, $condition = []), order($columns),
        // outerJoin($table, $condition = []), rightJoin($table, $condition = []), bind()
        // ... more {@see Query}
        //
        // e.g:
        //  'limit' => [10, 120],
        //  'order' => 'createTime ASC',
        //  'group' => 'id, type',
        'select' => '*',

        // can be a closure
        // function ($query) { ... }
    ];

    /**
     * default only update the have been changed column.
     * @var bool
     */
    protected $onlyUpdateChanged = true;

    /**
     * @param $data
     * @param string $scene
     * @return static
     */
    public static function load($data, $scene = '')
    {
        return new static($data, $scene);
    }

    /**
     * RecordModel constructor.
     * @param array $items
     * @param string $scene
     * @throws InvalidConfigException
     */
    public function __construct(array $items = [], $scene = '')
    {
        parent::__construct($items);

        $this->scene = trim($scene);

        if (!$this->columns()) {
            throw new InvalidConfigException('Must define method columns() and cannot be empty.');
        }

        self::$tableName = static::tableName();
    }

    /***********************************************************************************
     * some prepare work
     ***********************************************************************************/

    /**
     * TODO 定义保存数据时,当前场景允许写入的属性字段
     * @return array
     */
    public function scenarios()
    {
        return [
            // 'create' => ['username', 'email', 'password','createTime'],
            // 'update' => ['username', 'email','createTime'],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        // default is current class name
        $className = lcfirst(basename(str_replace('\\', '/', static::class)));

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
    final public static function queryName()
    {
        return static::$aliasName ? static::tableName() . ' AS ' . static::$aliasName : static::tableName();
    }

    /**
     * the database driver instance
     * @return AbstractDriver
     */
    public static function getDb()
    {
        return \Slim::get('db');
    }

    /**
     * init query
     * @param mixed $where
     * @return Query
     */
    public static function query($where = null)
    {
        return ModelHelper::handleConditions($where, static::class)->from(static::queryName());
    }

    /**
     * getTableName
     * @return string
     */
    public static function getTableName()
    {
        return self::$tableName ?: self::tableName();
    }

    /***********************************************************************************
     * find operation
     ***********************************************************************************/

    /**
     * find record by primary key
     * @param mixed $priValue
     * @param string|array $options
     * @return static
     */
    public static function findByPk($priValue, $options = null)
    {
        // only one
        $where = [static::$priKey => $priValue];

        if (is_array($priValue)) {// many
            $where = static::$priKey . ' IN (' . implode(',', $priValue) . ')';
        }

        return static::findOne($where, $options);
    }

    /**
     * find a record by where condition
     * @param mixed $where
     * @param string|array $options
     * @return static|array
     */
    public static function findOne($where, $options = null)
    {
        // as select
        if (is_string($options)) {
            $options = [
                'select' => $options
            ];
        }

        $options = array_merge(static::$defaultOptions, (array)$options);
        $class = $options['class'] === 'model' ? static::class : $options['class'];

        unset($options['indexKey'], $options['class']);
        $query = self::applyAppendOptions($options, static::query($where));

        $model = static::setQuery($query)->loadOne($class);

        // use data model
        if ($model && $class === static::class) {
            /** @var static $model */
            $model->setOldData($model->all());
        }

        return $model;
    }

    /**
     * @param mixed $where {@see self::handleConditions() }
     * @param string|array $options
     * @return array
     */
    public static function findAll($where, $options = null)
    {
        // as select
        if (is_string($options)) {
            $options = [
                'select' => $options
            ];
        }

        $options = array_merge(static::$defaultOptions, ['class' => 'assoc'], (array)$options);
        $indexKey = Arr::remove($options, 'indexKey');
        $class = $options['class'] === 'model' ? static::class : $options['class'];

        unset($options['indexKey'], $options['class']);

        $query = self::applyAppendOptions($options, static::query($where));

        return static::setQuery($query)->loadAll($indexKey, $class);
    }

    /**
     * @param array $updateColumns
     * @param bool|false $updateNulls
     * @return bool
     */
    public function save(array $updateColumns = [], $updateNulls = false)
    {
        $this->isNew() ? $this->insert() : $this->update($updateColumns, $updateNulls);

        return !$this->hasError();
    }

    /***********************************************************************************
     * create operation
     ***********************************************************************************/

    /**
     * @return static
     */
    public function insert()
    {
        $this->beforeInsert();
        $this->beforeSave();

        if ($this->enableValidate && $this->validate()->fail()) {
            return $this;
        }

        $data = $this->getColumnsData();
        $priValue = static::getDb()->insert(static::tableName(), $data);

        // when insert successful.
        if ($priValue) {
            $this->set(static::$priKey, $priValue);

            $this->afterInsert();
            $this->afterSave();
        }

        return $this;
    }

    /***********************************************************************************
     * update operation
     ***********************************************************************************/

    /**
     * update by primary key
     * @param array $updateColumns only update some columns
     * @param bool|false $updateNulls
     * @return static
     * @throws InvalidArgumentException
     */
    public function update(array $updateColumns = [], $updateNulls = false)
    {
        $priKey = static::$priKey;
        $this->beforeUpdate();
        $this->beforeSave();

        // the primary column is must be exists.
        if ($updateColumns && !in_array($priKey, $updateColumns, true)) {
            $updateColumns[] = $priKey;
        }

        // validate data
        if ($this->enableValidate && $this->validate($updateColumns)->fail()) {
            return $this;
        }

        // collect there are data will update.
        $data = $this->getColumnsData();

        if ($this->onlyUpdateChanged) {
            // Exclude the column if it value not change
            foreach ($data as $column => $value) {
                if ($column !== $priKey && !$this->valueIsChanged($column)) {
                    unset($data[$column]);
                }
            }
        }

        // check primary key
        if (!isset($data[$priKey])) {
            throw new InvalidArgumentException('Must be require primary column of the method update()');
        }

        $result = static::getDb()->update(static::tableName(), $data, $priKey, $updateNulls);

        if ($result) {
            $this->afterUpdate();
            $this->afterSave();
        }

        return $this;
    }

    /***********************************************************************************
     * delete operation
     ***********************************************************************************/

    /**
     * delete by model
     * @return int
     */
    public function delete()
    {
        if (!($priValue = $this->priValue())) {
            return 0;
        }

        $this->beforeDelete();

        if ($affected = self::deleteByPk($priValue)) {
            $this->afterDelete();
        }

        return $affected;
    }

    /***********************************************************************************
     * transaction operation
     ***********************************************************************************/

    /**
     * @param bool $throwException throw a exception on failure.
     * @return bool
     */
    public static function beginTrans($throwException = true)
    {
        return static::getDb()->beginTrans($throwException);
    }

    /**
     * @param bool $throwException throw a exception on failure.
     * @return bool
     */
    public static function commit($throwException = true)
    {
        return static::getDb()->commit($throwException);
    }

    /**
     * @param bool $throwException throw a exception on failure.
     * @return bool
     */
    public static function rollBack($throwException = true)
    {
        return static::getDb()->rollBack($throwException);
    }

    /**
     * @return bool
     */
    public static function inTrans()
    {
        return static::getDb()->inTrans();
    }

    /***********************************************************************************
     * extra operation
     ***********************************************************************************/

    protected function beforeInsert()
    {
        return true;
    }

    protected function afterInsert()
    {
    }

    protected function beforeUpdate()
    {
        return true;
    }

    protected function afterUpdate()
    {
    }

    protected function beforeSave()
    {
        return true;
    }

    protected function afterSave()
    {
    }

    protected function beforeDelete()
    {
        return true;
    }

    protected function afterDelete()
    {
    }

    /***********************************************************************************
     * helper method
     ***********************************************************************************/

    /**
     * @return bool
     */
    public function isNew()
    {
        return !($this->has(static::$priKey) && $this->get(static::$priKey, false));
    }

    /**
     * @param null|bool $value
     * @return bool
     */
    public function enableValidate($value = null)
    {
        if (is_bool($value)) {
            $this->enableValidate = $value;
        }

        return $this->enableValidate;
    }

    /**
     * @param bool $forceNew
     * @return Query
     */
    final public static function getQuery($forceNew = false)
    {
        return static::getDb()->newQuery($forceNew);
    }

    /**
     * findXxx 无法满足需求时，自定义 $query
     *
     * ```php
     * $query = XModel::getQuery();
     * ...
     * XModel::setQuery($query)->loadAll(null, XModel::class);
     * ```
     *
     * @param string|Query $query
     * @return AbstractDriver
     */
    final public static function setQuery($query)
    {
        return static::getDb()->setQuery($query);
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
     * @param string $column
     * @return bool
     */
    protected function valueIsChanged($column)
    {
        return $this->isNew() || $this->get($column) !== $this->getOld($column);
    }

    /**
     * @return array
     */
    public function getOldData()
    {
        return $this->_backup;
    }

    /**
     * @param $data
     */
    public function setOldData($data)
    {
        $this->_backup = $data;
    }

    /**
     * @param $column
     * @return mixed
     */
    public function getOld($column)
    {
        return $this->_backup[$column] ?? null;
    }

    /***********************************************************************************
     * helper method
     ***********************************************************************************/

    /**
     * apply Append Options
     * @see self::$defaultOptions
     * @param  array $options
     * @param  Query $query
     * @return Query
     * @throws UnknownMethodException
     */
    public static function applyAppendOptions(array $options = [], Query $query)
    {
        return ModelHelper::applyQueryOptions($options, $query);
    }
}
