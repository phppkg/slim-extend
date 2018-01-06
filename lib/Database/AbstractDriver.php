<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 */

namespace slimExt\database;

use inhere\exceptions\InvalidArgumentException;
use inhere\library\traits\LiteEventTrait;
use inhere\library\traits\LiteOptionsTrait;
use Slim;
use PDO;
use PDOStatement;
use slimExt\database\helpers\QueryHelper;
use slimExt\database\helpers\DsnHelper;
use Windwalker\Query\Query;

/**
 * Class AbstractDriver
 * @package slimExt\database
 * @link https://github.com/ventoviro/windwalker-database
 */
abstract class AbstractDriver implements InterfaceDriver
{
    use LiteEventTrait;
    use LiteOptionsTrait;
    use LoadResultSetTrait;

    const CONNECT = 'connect';
    const DISCONNECT = 'disconnect';
    const EXECUTE = 'execute';
    const BEFORE_EXECUTE = 'beforeExecute';
    const AFTER_EXECUTE  = 'afterExecute';

    protected $name = '';

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var PDOStatement
     */
    protected $cursor;

    /**
     * @var array
     */
    protected $options = [
        'driver' => 'odbc',
        'dsn' => '',
        'host' => 'localhost',
        'database' => '',
        'user' => '',
        'password' => '',
        'driverOptions' => [],
        'connecting' => false,
    ];

    /**
     * @var string|Query
     */
    protected $query;

    /**
     * @var string|Query
     */
    protected static $newQueryCache;

    /**
     * @var string
     */
    protected $lastQuery = '';

    /**
     * @var string
     */
    protected $prefixChar = '@@';

    /**
     * @var bool|mixed
     */
    protected $debug = false;

    /**
     * Whether to support a one-time insert or update multiple records.
     * @see insertBatch()
     * @see updateBatch()
     * @var bool
     */
    protected $supportBatchSave = false;

    /**
     * @var string
     */
    private $dsn;

    /**
     * @param array $options
     * @param PDO|null $pdo
     */
    public function __construct(array $options = [], PDO $pdo = null)
    {
        $this->pdo = $pdo;
        $this->setOptions($options);
        // $this->driverOptions = $this->getOption('driverOptions');
        $this->debug = $this->getOption('debug', false);

        $this->dsn = DsnHelper::getDsn($this->name, $this->options);

        if (!$pdo && $this->getOption('connecting', false)) {
            $this->connect();
        }
    }

    /**
     * connect description
     * @return $this
     */
    public function connect()
    {
        if ($this->pdo && $this->ping($this->pdo)) {
            return $this;
        }

        try {
            $this->pdo = new \PDO(
                $this->dsn,
                $this->options['user'],
                $this->options['password'],
                $this->options['driverOptions']
            );

            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        } catch (\PDOException $e) {
            throw new \RuntimeException('Could not connect to PDO: ' . $e->getMessage() . '. DSN: ' . $this->dsn, $e->getCode(), $e);
        }

        $this->fire(self::CONNECT, [$this]);

        return $this;
    }

    /**
     * Check whether the connection is available
     * @param  \PDO $pdo
     * @return Boolean
     */
    public function ping($pdo)
    {
        try {
            // mysql will return: "5.6.32-log"
            // $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $pdo->query('SELECT 1')->fetchColumn();// return: 1
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'server has gone away') !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * disconnect
     */
    public function disconnect()
    {
        $this->fire(self::DISCONNECT, [$this]);

        $this->freeResult();
        $this->pdo = null;

    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        $this->disconnect();
    }

////////////////////////////////////// insert record //////////////////////////////////////

    /**
     * SQL:
     * ```
     * INSERT INTO "user"
     * ("username", "password", "createTime", "role", "lastLogin", "avatar")
     * VALUES
     * (?, ?, ?, 1, 1, '')
     * ```
     * @param string $table
     * @param array|\Iterator $data
     * @param string $priKey
     * @return array|bool|int
     * @throws InvalidArgumentException
     */
    public function insert($table, $data, $priKey = '')
    {
        if (!$data) {
            throw new InvalidArgumentException('Insert data is empty. Please check it.');
        }

        $query = $this->newQuery(true);
        $fields = $values = [];

        // Iterate over the object variables to build the query fields and values.
        foreach ($data as $k => $v) {
            // Convert stringable object
            if (is_object($v) && is_callable(array($v, '__toString'))) {
                $v = (string)$v;
            }

            // Only process non-null scalars.
            if (is_array($v) || is_object($v) || $v === null) {
                continue;
            }

            // Ignore any internal fields.
            if ($k && is_string($k) && $k[0] === '_') {
                continue;
            }

            // Prepare and sanitize the fields and values for the database query.
            $fields[] = $query->quoteName($k);
            $values[] = $query->quote($v);
        }

        // Create the base insert statement.
        $query->insert($query->quoteName($table));
        $query->columns($fields);
        $query->values(array($values));

        // Set the query and execute the insert.
        $this->setQuery($query)->exec();

        // Update the primary key if it exists.
        $id = $this->pdo->lastInsertId();

        if ($priKey && $id && is_string($priKey)) {
            if (is_array($data)) {
                $data[$priKey] = $id;
            } else {
                $data->$priKey = $id;
            }
            return $data;
        }

        return $id;
    }

    /**
     * insertMultiple
     *
     * @param   string $table The name of the database table to update.
     * @param   array &$dataSet A reference to an object whose public properties match the table fields.
     * @param   array $key The name of the primary key.
     *
     * @throws \InvalidArgumentException
     * @return  mixed
     */
    public function insertMulti($table, &$dataSet, $key = null)
    {
        if (!is_array($dataSet) && !($dataSet instanceof \Traversable)) {
            throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
        }

        foreach ($dataSet as $k => $data) {
            $dataSet[$k] = $this->insert($table, $data, $key);
        }

        return $dataSet;
    }

    /**
     * one-time insert multiple records.
     * @param string $table
     * @param array $columns
     * @param array $values
     * e.g:
     * $columns = ['title', 'year'];
     * $values = [
     *   [ 'The Tragedy of Julius Caesar', 1599]
     *   [ 'Macbeth', 1606]
     * ];
     * @return bool|int
     */
    public function insertBatch($table, array $columns, array $values)
    {
        $query = $this->newQuery(true);
        $query->columns($columns);

        // Build conditions
        $hasField = false;
        $buildValues = [];

        foreach ($values as $value) {
            // to string. eg: "'Macbeth', 1606"
            $buildValues[] = implode(',', $query->q($value));
            $hasField = true;
        }

        if (!$hasField) {
            return false;
        }

        $query->values($buildValues);
        $query->insert($query->qn($table));

        return $this->setQuery($query)->exec();
    }

////////////////////////////////////// update record //////////////////////////////////////

    /**
     * Updates a row in a table based on an object's properties.
     * @param   string $table The name of the database table to update.
     * @param   array $data A reference to an object whose public properties match the table fields.
     * @param   array|string $key The name of the primary key.
     * @param   boolean $updateNulls True to update null fields or false to ignore them.
     * @return bool|int
     * @throws InvalidArgumentException
     */
    public function update($table, $data, $key = 'id', $updateNulls = false)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException('Please give me array or object to update.');
        }

        if (!$data) {
            throw new InvalidArgumentException('Update data is empty. Please check it.');
        }

        $query = $this->newQuery(true);
        $key = (array)$key;

        // Create the base update statement.
        $query->update($query->quoteName($table));

        // Iterate over the object variables to build the query fields/value pairs.
        foreach (get_object_vars((object)$data) as $k => $v) {
            // Convert stringable object
            if (is_object($v) && is_callable(array($v, '__toString'))) {
                $v = (string)$v;
            }

            // Only process scalars that are not internal fields.
            if (is_array($v) || is_object($v) || ($k && is_string($k) && $k[0] === '_')) {
                continue;
            }

            // Set the primary key to the WHERE clause instead of a field to update.
            if (in_array($k, $key, true)) {
                $query->where($query->quoteName($k) . '=' . $query->quote($v));

                continue;
            }

            // Prepare and sanitize the fields and values for the database query.
            if ($v === null || $v === '') {
                // If the value is null and we want to update nulls then set it.
                if ($updateNulls) {
                    $val = 'NULL';

                    // If the value is null and we do not want to update nulls then ignore this field.
                } else {
                    continue;
                }
            } else { // The field is not null so we prep it for update.
                $val = $query->quote($v);
            }

            // Add the field to be updated.
            $query->set($query->quoteName($k) . '=' . $val);
        }

        // Set the query and execute the update.
        return $this->setQuery($query)->exec();
    }

    /**
     * updateMultiple
     *
     * @param   string $table The name of the database table to update.
     * @param   array $dataSet A reference to an object whose public properties match the table fields.
     * @param   array $key The name of the primary key.
     * @param   boolean $updateNulls True to update null fields or false to ignore them.
     *
     * @throws \InvalidArgumentException
     * @return  mixed
     */
    public function updateMulti($table, $dataSet, $key, $updateNulls = false)
    {
        if (!is_array($dataSet) && !($dataSet instanceof \Traversable)) {
            throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
        }

        foreach ($dataSet as $data) {
            $this->update($table, $data, $key, $updateNulls);
        }

        return $dataSet;
    }

    /**
     * Batch update some data.
     * @param string $table Table name.
     * @param array $data Data you want to update.
     * @param mixed $conditions Where conditions, you can use array or Compare object.
     *                           Example:
     *                           - `array('id' => 5)` => id = 5
     *                           - `new GteCompare('id', 20)` => 'id >= 20'
     *                           - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
     * @return  int
     */
    public function updateBatch($table, $data, $conditions = null)
    {
        $query = $this->newQuery(true);

        // Build conditions
        $query = QueryHelper::buildWheres($query, (array)$conditions);
        $hasField = false;

        foreach ((array)$data as $field => $value) {
            $query->set($query->format('%n = %q', $field, $value));

            $hasField = true;
        }

        if (!$hasField) {
            return false;
        }

        $query->update($table);

        return $this->setQuery($query)->exec();
    }

    /**
     * save
     * @param   string $table The name of the database table to update.
     * @param   array &$data A reference to an object whose public properties match the table fields.
     * @param   string $key The name of the primary key.
     * @param   boolean $updateNulls True to update null fields or false to ignore them.
     * @return  mixed
     * @throws \InvalidArgumentException
     */
    public function save($table, &$data, $key, $updateNulls = false)
    {
        if (!is_scalar($key)) {
            throw new \InvalidArgumentException(__NAMESPACE__ . '::save() dose not support multiple keys, please give me only one key.');
        }

        if (is_array($data)) {
            $id = $data[$key] ?? null;
        } else {
            $id = $data->$key ?? null;
        }

        if ($id) {
            return $this->update($table, $data, $key, $updateNulls);
        }

        return $this->insert($table, $data, $key);
    }

    /**
     * @param string $table
     * @param $dataSet
     * @param string $key
     * @param bool|false $updateNulls
     * @return mixed
     */
    public function saveMulti($table, $dataSet, $key, $updateNulls = false)
    {
        if (!is_array($dataSet) && !($dataSet instanceof \Traversable)) {
            throw new \InvalidArgumentException('The data set to store should be array or \Traversable');
        }

        foreach ($dataSet as $data) {
            $this->save($table, $data, $key, $updateNulls);
        }

        return $dataSet;
    }

////////////////////////////////////// run method //////////////////////////////////////

    /**
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->connect()->freeResult();
        $this->query = $query;

        return $this;
    }

    /**
     * exec a statement, returns the number of rows that were modified
     * can use to INSERT, UPDATE, DELETE
     * @param string $statement
     * @return int
     */
    abstract public function exec($statement = '');

    /**
     * @param string $statement
     * @return $this
     */
    abstract public function query($statement = null);

    /**
     * @param string $statement
     * @param array $driverOptions
     * @return $this
     */
    abstract public function prepare($statement = null, array $driverOptions = []);

    /**
     * @param array $bindParams
     * @return $this
     */
    abstract public function execute(array $bindParams = []);

    /**
     * @param null|PdoStatement $cursor
     * @return $this
     */
    abstract public function freeResult($cursor = null);

    /**
     * @param bool $forceNew
     * @return Query
     */
    public function newQuery($forceNew = false)
    {
        if ($forceNew || self::$newQueryCache === null) {
            self::$newQueryCache = new Query($this->pdo);
        }

        return self::$newQueryCache;
    }

    /**
     * @return mixed
     */
    public function dbLogger()
    {
        return Slim::get('dbLogger');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function replaceTablePrefix($query)
    {
        return str_replace($this->prefixChar, $this->getOption('prefix'), (string)$query);
    }

////////////////////////////////////// getter/setter method //////////////////////////////////////

    /**
     * @return PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param PDO $pdo
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * getCursor
     * @return PDOStatement
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    public function getDriver()
    {
        return $this->getOption('driver');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLastQuery()
    {
        return $this->lastQuery;
    }

    /**
     * @return bool|string
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @return bool
     */
    public function supportBatchSave()
    {
        return $this->supportBatchSave;
    }

    /**
     * Is this driver supported.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return class_exists('PDO');
    }
}
