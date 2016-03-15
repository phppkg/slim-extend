<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 */

namespace slimExtend\database;

use Slim;
use PDO;
use PDOStatement;
use Windwalker\Database\Query\QueryHelper;
use Windwalker\Query\Query;

/**
 * Class AbstractDriver
 * @package slimExtend\database
 */
abstract class AbstractDriver
{
    protected $name = '';

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @var PDOStatement
     */
    private $cursor;

    /**
     * @var
     */
    private $options = [];

    /**
     * @var string|Query
     */
    private $query;

    /**
     * @var string|Query
     */
    protected static $newQueryCache;

    /**
     * @var string
     */
    private $lastQuery = '';

    protected $prefixChar = '@@';

    protected $debug = false;

    /**
     * @param array $options
     * @param PDO|null $pdo
     */
    public function __construct(array $options = [], PDO $pdo = null)
    {
        $defaultOptions = array(
            'driver'   => 'odbc',
            'dsn'      => '',
            'host'     => 'localhost',
            'database' => '',
            'user'     => '',
            'password' => '',
            'driverOptions' => array()
        );

        $options = array_merge($defaultOptions, $options);

        $this->options = $options;
        $this->pdo     = $pdo;
        $this->driverOptions = $this->getOption('driverOptions');

        $this->debug = $this->getOption('debug', false);

        if ( !$pdo && $this->getOption('autoConnect', false) ) {
            $this->connect();
        }
    }

    /**
     * [connect description]
     * @return $this
     */
    public function connect()
    {
        if ( $this->pdo ) {
            return $this;
        }

        $dsn = DsnHelper::getDsn($this->options['driver'], $this->options);

        try {
            $this->pdo = new \PDO(
                $dsn,
                $this->options['user'],
                $this->options['password'],
                $this->options['driverOptions']
            );
        } catch (\PDOException $e) {
            throw new \RuntimeException('Could not connect to PDO: ' . $e->getMessage() . '. DSN: ' . $dsn, $e->getCode(), $e);
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);

        return $this;
    }

////////////////////////////////////// Read record //////////////////////////////////////

    public function loadAll($key = null, $class = '\\stdClass')
    {
        if (strtolower($class) === 'array') {
            return $this->loadArrayList($key);
        }

        if (strtolower($class) === 'assoc') {
            return $this->loadAssocList($key);
        }

        return $this->loadObjectList($key, $class);
    }

    /**
     * loadOne
     * @param string $class
     * @return  mixed
     */
    public function loadOne($class = '\\stdClass')
    {
        if (strtolower($class) === 'array') {
            return $this->loadArray();
        }

        if (strtolower($class) === 'assoc') {
            return $this->loadAssoc();
        }

        return $this->loadObject($class);
    }

    /**
     * @return array|bool
     */
    public function loadResult()
    {
        $this->execute();

        // Get the first row from the result set as an array.
        $row = $this->fetchArray();

        if ($row && is_array($row) && isset($row[0])) {
            $row = $row[0];
        }

        // Free up system resources and return.
        $this->freeResult();

        return $row;
    }

    /**
     * @param int $offset
     * @return array
     */
    public function loadColumn($offset = 0)
    {
        $this->execute();
        $array = [];

        // Get all of the rows from the result set as arrays.
        while ($row = $this->fetchArray()) {
            if ($row && is_array($row) && isset($row[$offset])) {
                $array[] = $row[$offset];
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    public function loadArray()
    {
        $this->execute();

        // Get the first row from the result set as an array.
        $array = $this->fetchArray();

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    public function loadArrayList($key = null)
    {
        $this->execute();
        $array = [];

        // Get all of the rows from the result set as arrays.
        while ($row = $this->fetchArray()) {
            if ($key !== null && is_array($row) ) {
                $array[$row[$key]] = $row;
            } else {
                $array[] = $row;
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    public function loadAssoc()
    {
        $this->execute();

        // Get the first row from the result set as an associative array.
        $array = $this->fetchAssoc();

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    public function loadAssocList($key = null)
    {
        $this->execute();
        $array = [];

        // Get all of the rows from the result set.
        while ($row = $this->fetchAssoc()) {
            if ($key) {
                $array[$row[$key]] = $row;
            }
            else {
                $array[] = $row;
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    public function loadObject($class = 'stdClass')
    {
        $this->execute();

        // Get the first row from the result set as an object of type $class.
        $object = $this->fetchObject($class);

        // Free up system resources and return.
        $this->freeResult();

        return $object;
    }

    public function loadObjectList($key = null, $class = 'stdClass')
    {
        $this->execute();
        $array = [];

        // Get all of the rows from the result set as objects of type $class.
        while ($row = $this->fetchObject($class)) {
            if ($key) {
                $array[$row->$key] = $row;
            }
            else {
                $array[] = $row;
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    /**
     * @return array|bool
     */
    public function fetchArray()
    {
        return $this->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Method to fetch a row from the result set cursor as an associative array.
     * @return  mixed  Either the next row from the result set or false if there are no more rows.
     */
    public function fetchAssoc()
    {
        return $this->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Method to fetch a row from the result set cursor as an object.
     * @param   string  $class  Unused, only necessary so method signature will be the same as parent.
     * @return  mixed   Either the next row from the result set or false if there are no more rows.
     */
    public function fetchObject($class = '\\stdClass')
    {
        return $this->getCursor()->fetchObject($class);
    }

    /**
     * fetch
     * @param int  $type
     * @param int  $ori
     * @param int  $offset
     * @see http://php.net/manual/en/pdostatement.fetch.php
     * @return  bool|mixed
     */
    public function fetch($type = \PDO::FETCH_ASSOC, $ori = null, $offset = 0)
    {
        return $this->getCursor()->fetch($type, $ori, $offset);
    }

    /**
     * fetchAll
     * @param int   $type
     * @param array $args
     * @param array $ctorArgs
     * @see http://php.net/manual/en/pdostatement.fetchall.php
     * @return  array|bool
     */
    public function fetchAll($type = \PDO::FETCH_ASSOC, $args = null, $ctorArgs = null)
    {
        return $this->getCursor()->fetchAll($type,$args , $ctorArgs);
    }


////////////////////////////////////// insert record //////////////////////////////////////

    /**
     * SQL:
     * ```
     * INSERT INTO "mder_users"
     * ("username", "password", "createTime", "role", "lastLogin", "avatar")
     * VALUES
     * (?, ?, ?, 1, 1, '')
     * ```
     * @param string $table
     * @param array $data
     * @param string $priKey
     * @return array|int|bool
     */
    public function insert($table, $data, $priKey='')
    {
        $query = $this->newQuery(true);
        $fields = $values = [];

        // Iterate over the object variables to build the query fields and values.
        foreach ($data as $k => $v) {
            // Convert stringable object
            if (is_object($v) && is_callable(array($v, '__toString'))) {
                $v = (string) $v;
            }

            // Only process non-null scalars.
            if (is_array($v) or is_object($v) or $v === null) {
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
        $query->insert($query->quoteName($table))
            ->columns($fields)
            ->values(array($values));

        // Set the query and execute the insert.
        if (!$this->setQuery($query)->execute()) {
            return false;
        }

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

////////////////////////////////////// update record //////////////////////////////////////

    /**
     * Updates a row in a table based on an object's properties.
     * @param   string  $table       The name of the database table to update.
     * @param   array   $data        A reference to an object whose public properties match the table fields.
     * @param   array|string  $key         The name of the primary key.
     * @param   boolean $updateNulls True to update null fields or false to ignore them.
     * @throws \InvalidArgumentException
     * @return  boolean  True on success.
     */
    public function update($table, $data, $key= 'id', $updateNulls = false)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new \InvalidArgumentException('Please give me array or object to update.');
        }

        $query = $this->newQuery(true);
        $key = (array) $key;

        // Create the base update statement.
        $query->update($query->quoteName($table));

        // Iterate over the object variables to build the query fields/value pairs.
        foreach (get_object_vars((object) $data) as $k => $v) {
            // Convert stringable object
            if (is_object($v) && is_callable(array($v, '__toString'))) {
                $v = (string) $v;
            }

            // Only process scalars that are not internal fields.
            if (is_array($v) || is_object($v) || ( $k && is_string($k) && $k[0] === '_') ) {
                continue;
            }

            // Set the primary key to the WHERE clause instead of a field to update.
            if (in_array($k, $key)) {
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
        return $this->setQuery($query)->execute();
    }

    /**
     * save
     * @param   string  $table        The name of the database table to update.
     * @param   array   &$data        A reference to an object whose public properties match the table fields.
     * @param   string  $key          The name of the primary key.
     * @param   boolean $updateNulls  True to update null fields or false to ignore them.
     * @return  bool|static
     * @throws \InvalidArgumentException
     */
    public function save($table, &$data, $key, $updateNulls = false)
    {
        if ( !is_scalar($key) ) {
            throw new \InvalidArgumentException(__NAMESPACE__ . '::save() dose not support multiple keys, please give me only one key.');
        }

        if (is_array($data)) {
            $id = isset($data[$key]) ? $data[$key] : null;
        } else {
            $id = isset($data->$key) ? $data->$key : null;
        }

        if ($id) {
            return $this->update($table, $data, $key, $updateNulls);
        }

        return $this->insert($table, $data, $key);
    }

    /**
     * Batch update some data.
     * @param string $table      Table name.
     * @param array  $data       Data you want to update.
     * @param mixed  $conditions Where conditions, you can use array or Compare object.
     *                           Example:
     *                           - `array('id' => 5)` => id = 5
     *                           - `new GteCompare('id', 20)` => 'id >= 20'
     *                           - `new Compare('id', '%Flower%', 'LIKE')` => 'id LIKE "%Flower%"'
     * @return  boolean True if update success.
     */
    public function updateBatch($table, $data, $conditions = [])
    {
        $query = $this->newQuery(true);

        // Build conditions
        $query = QueryHelper::buildWheres($query, $conditions);
        $hasField = false;

        foreach ((array) $data as $field => $value) {
            $query->set($query->format('%n = %q', $field, $value));

            $hasField = true;
        }

        if (!$hasField) {
            return false;
        }

        $query->update($table);

        return $this->setQuery($query)->execute();
    }

////////////////////////////////////// helper method //////////////////////////////////////

    /**
     * @param $query
     * @param array $driverOptions
     * @return $this
     */
    public function setQuery($query, $driverOptions = [])
    {
        $this->connect()->freeResult();
        $this->driverOptions = $driverOptions;
        $this->query = $query;

        return $this;
    }

    /**
     * @return static
     */
    public function execute()
    {
        $this->connect();

        $sql = $this->replaceTablePrefix((string)$this->query);

        // add sql log
        if ( $this->debug ) {
            $this->dbLogger()->debug('Prepared : ' . $sql.'; ');
        }

        $this->cursor = $this->pdo->prepare($sql, $this->driverOptions);

        if (!($this->cursor instanceof \PDOStatement)) {
            throw new \RuntimeException('PDOStatement not prepared. Maybe you haven\'t set any query');
        }

        // Bind the variables:
        if ($this->query instanceof Query\PreparableInterface) {
            $bounded =& $this->query->getBounded();

            foreach ($bounded as $key => $data) {
                $this->cursor->bindParam($key, $data->value, $data->dataType, $data->length, $data->driverOptions);
            }
        }

        $this->lastQuery = $this->cursor->queryString;

        // add sql log
        if ( $this->debug ) {
            $this->dbLogger()->debug('Executed : ' . $this->lastQuery.'; ');
        }

        try {
            $this->cursor->execute();
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage() . "\nSQL: " . $this->lastQuery, (int) $e->getCode(), $e);
        }

        return $this;
    }

    public function newQuery($forceNew=false)
    {
        if ( $forceNew || self::$newQueryCache === null ) {
            self::$newQueryCache = new Query($this->pdo);
        }
        return self::$newQueryCache;
    }

    public function dbLogger()
    {
        return Slim::get('dbLogger');
    }

    public function replaceTablePrefix($query)
    {
        return str_replace($this->prefixChar, $this->getOption('prefix'), (string)$query);
    }

    /**
     * @param null|PdoStatement $cursor
     * @return $this
     */
    public function freeResult($cursor = null)
    {
        $cursor = $cursor ? : $this->cursor;

        if ($cursor instanceof \PDOStatement) {
            $cursor->closeCursor();

            $cursor = null;
        }

        return $this;
    }

    /**
     * count
     * @return  integer
     */
    public function count()
    {
        return $this->getCursor()->rowCount();
    }

    /**
     * Get the number of affected rows for the previous executed SQL statement.
     * Only applicable for DELETE, INSERT, or UPDATE statements.
     * @return  integer  The number of affected rows.
     */
    public function countAffected()
    {
        return $this->getCursor()->rowCount();
    }

    /**
     * Method to get the auto-incremented value from the last INSERT statement.
     * @return  string  The value of the auto-increment field from the last inserted row.
     */
    public function insertId()
    {
        // Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
        return @$this->getPdo()->lastInsertId();
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
     * @return  resource|PDOStatement
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    public function getOption($name, $default = '')
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
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
     * Is this driver supported.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return class_exists('PDO');
    }
}