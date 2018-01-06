<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/24
 * Time: 下午11:32
 */

namespace slimExt\database;

/**
 * Class LoadResultSetTrait
 * @package slimExt\database
 *
 * @property \PDOStatement $cursor
 */
trait LoadResultSetTrait
{
////////////////////////////////////// Read record //////////////////////////////////////

    /**
     * @param null|string $key
     * @param string $class
     * @return array
     */
    public function loadAll($key = null, $class = \stdClass::class)
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
    public function loadOne($class = \stdClass::class)
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
        if (!$this->cursor) {
            $this->execute();
        }

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
        if (!$this->cursor) {
            $this->execute();
        }

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

    /**
     * @return array|bool
     */
    public function loadArray()
    {
        if (!$this->cursor) {
            $this->execute();
        }

        // Get the first row from the result set as an array.
        $array = $this->fetchArray();

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    /**
     * @param null $key
     * @return array
     */
    public function loadArrayList($key = null)
    {
        if (!$this->cursor) {
            $this->execute();
        }

        $array = [];

        // Get all of the rows from the result set as arrays.
        while ($row = $this->fetchArray()) {
            if ($key !== null && is_array($row)) {
                $array[$row[$key]] = $row;
            } else {
                $array[] = $row;
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    /**
     * @return array
     */
    public function loadAssoc()
    {
        if (!$this->cursor) {
            $this->execute();
        }

        // Get the first row from the result set as an associative array.
        $array = $this->fetchAssoc();

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    /**
     * @param null $key
     * @return array
     */
    public function loadAssocList($key = null)
    {
        if (!$this->cursor) {
            $this->execute();
        }

        $array = [];

        // Get all of the rows from the result set.
        while ($row = $this->fetchAssoc()) {
            if ($key) {
                $array[$row[$key]] = $row;
            } else {
                $array[] = $row;
            }
        }

        // Free up system resources and return.
        $this->freeResult();

        return $array;
    }

    /**
     * @param string $class
     * @return mixed
     */
    public function loadObject($class = 'stdClass')
    {
        if (!$this->cursor) {
            $this->execute();
        }

        // Get the first row from the result set as an object of type $class.
        $object = $this->fetchObject($class);

        // Free up system resources and return.
        $this->freeResult();

        return $object;
    }

    /**
     * @param null $key
     * @param string $class
     * @return array
     */
    public function loadObjectList($key = null, $class = 'stdClass')
    {
        if (!$this->cursor) {
            $this->execute();
        }

        $array = [];

        // Get all of the rows from the result set as objects of type $class.
        while ($row = $this->fetchObject($class)) {
            if ($key) {
                $array[$row->$key] = $row;
            } else {
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
        return $this->fetch();// \PDO::FETCH_ASSOC
    }

    /**
     * Method to fetch a row from the result set cursor as an object.
     * @param   string $class Unused, only necessary so method signature will be the same as parent.
     * @return  mixed   Either the next row from the result set or false if there are no more rows.
     */
    public function fetchObject($class = \stdClass::class)
    {
        return $this->cursor->fetchObject($class);
    }

    /**
     * fetch
     * @param int $type
     * @param int $ori
     * @param int $offset
     * @see http://php.net/manual/en/pdostatement.fetch.php
     * @return  bool|mixed
     */
    public function fetch($type = \PDO::FETCH_ASSOC, $ori = null, $offset = 0)
    {
        return $this->cursor->fetch($type, $ori, $offset);
    }

    /**
     * fetchAll
     * @param int $type
     * @param array $args
     * @param array $ctorArgs
     * @see http://php.net/manual/en/pdostatement.fetchall.php
     * @return  array|bool
     */
    public function fetchAll($type = \PDO::FETCH_ASSOC, $args = null, $ctorArgs = null)
    {
        return $this->cursor->fetchAll($type, $args, $ctorArgs);
    }
}
