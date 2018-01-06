<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/7/24 0002
 * Time: 22:33
 */

namespace slimExt\database;

use Windwalker\Query\Query;

/**
 * Class PdoDriver
 * @package slimExt\database
 */
class PdoDriver extends AbstractDriver
{
    /**
     * @var bool
     */
    private $prepared = false;

////////////////////////////////////// run method //////////////////////////////////////

    /**
     * exec a statement, returns the number of rows that were modified
     * can use to INSERT, UPDATE, DELETE
     * @param string $statement
     * @return int
     */
    public function exec($statement = null)
    {
        $this->connect();
        $statement = $this->replaceTablePrefix(trim($statement ?: (string)$this->query));

        // add sql log
        if ($this->debug) {
            $this->dbLogger()->debug($statement . ';');
        }

        $this->lastQuery = $statement;

        $this->fire(self::EXECUTE, [$this, 'exec']);

        return $this->pdo->exec($statement);
    }

    /**
     * @param null $statement
     * @param array $driverOptions
     * @return $this
     */
    public function prepare($statement = null, array $driverOptions = [])
    {
        if ($this->prepared) {
            return $this;
        }

        $this->connect();

        $statement = $this->replaceTablePrefix($statement ?: (string)$this->query);

        // add sql log
        if ($this->debug) {
            $this->dbLogger()->debug($statement . '; ');
        }

        $this->prepared = true;
        $this->lastQuery = $statement;
        $this->cursor = $this->pdo->prepare($statement, $driverOptions);

        return $this;
    }

    /**
     * @param array $bindParams
     * @return static
     */
    public function execute(array $bindParams = [])
    {
        $this->prepare();

        if (!($this->cursor instanceof \PDOStatement)) {
            throw new \RuntimeException('PDOStatement not prepared. Maybe you haven\'t set any query');
        }

        $boundedStr = 'Bounded ';

        // Bind the variables:
        if ($this->query instanceof Query\PreparableInterface) {
            $bounded = &$this->query->getBounded();

            foreach ((array)$bounded as $key => $data) {
                $this->cursor->bindParam($key, $data->value, $data->dataType, $data->length, $data->driverOptions);
                $boundedStr .= "$key->{$data->value},";
            }
        }

        // add sql log
        // if ( $this->debug ) {
        //     $this->dbLogger()->debug('Successful Executed.');
        // }

        $this->fire(self::EXECUTE, [$this, 'execute']);

        try {
            $this->cursor->execute($bindParams);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage() . "\nSQL: {$this->lastQuery}, $boundedStr", (int)$e->getCode(), $e);
        }

        $this->prepared = false;

        return $this;
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function query($statement = null)
    {
        $this->connect();

        $statement = $this->replaceTablePrefix($statement ?: (string)$this->query);

        // add sql log
        if ($this->debug) {
            $this->dbLogger()->debug($statement);
        }

        $this->lastQuery = $statement;

        $this->fire(self::EXECUTE, [$this, 'query']);

        $this->cursor = $this->pdo->query($statement);

        return $this;
    }

    /**
     * @param null|\PDOStatement $cursor
     * @return $this
     */
    public function freeResult($cursor = null)
    {
        $cursor = $cursor ?: $this->cursor;

        if ($cursor instanceof \PDOStatement) {
            $cursor->closeCursor();

            $cursor = null;
        }

        $this->cursor = null;

        return $this;
    }

////////////////////////////////////// extra method //////////////////////////////////////

    /**
     * count
     *
     * ```
     * $db->setQuery($query)->count();
     * ```
     * @return int
     */
    public function count()
    {
        $this->query->select('COUNT(*) AS total');

        $result = $this->loadOne();

        return $result ? (int)$result->total : 0;
    }

    /**
     * exists
     *
     * ```
     * $db->setQuery($query)->exists();
     * // SQL: select exists(select * from `table` where (`phone` = 152xxx)) as `exists`;
     * ```
     * @return int
     */
    public function exists()
    {
        $this->query = sprintf('SELECT EXISTS(%s) AS `exists`', $this->query->select('*'));

        $result = $this->loadOne();

        return $result ? $result->exists : 0;
    }

    /**
     * Get the number of affected rows for the previous executed SQL statement.
     * Only applicable for DELETE, INSERT, or UPDATE statements.
     * @return  integer  The number of affected rows.
     */
    public function affectedNum()
    {
        return $this->cursor->rowCount();
    }

    /**
     * Method to get the auto-incremented value from the last INSERT statement.
     * @return  string  The value of the auto-increment field from the last inserted row.
     */
    public function insertId()
    {
        // Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
        return @$this->pdo->lastInsertId();
    }
////////////////////////////////////// transaction method //////////////////////////////////////

    /**
     * Initiates a transaction
     * @link http://php.net/manual/en/pdo.begintransaction.php
     * @param bool $throwException throw a exception on failure.
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function beginTrans($throwException = true)
    {
        $this->connect();
        $result = $this->pdo->beginTransaction();

        if ($throwException && false === $result) {
            throw new \RuntimeException('Begin a transaction is failure!!');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->beginTrans();
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
     * Commits a transaction
     * @link http://php.net/manual/en/pdo.commit.php
     * @param bool $throwException throw a exception on failure.
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function commit($throwException = true)
    {
        if (!$this->inTransaction()) {
            throw new \LogicException('Transaction must be turned on before committing a transaction!!');
        }

        $result = $this->pdo->commit();

        if ($throwException && false === $result) {
            throw new \RuntimeException('Committing a transaction is failure!!');
        }

        return $result;
    }

    /**
     * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
     * Rolls back a transaction
     * @link http://php.net/manual/en/pdo.rollback.php
     * @param bool $throwException throw a exception on failure.
     * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
     */
    public function rollBack($throwException = true)
    {
        if (!$this->inTransaction()) {
            throw new \LogicException('Transaction must be turned on before rolls back a transaction!!');
        }

        $result = $this->pdo->rollBack();

        if ($throwException && false === $result) {
            throw new \RuntimeException('Committing a transaction is failure!!');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function inTrans()
    {
        $this->connect();

        return $this->pdo->inTransaction();
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTrans();
    }

}
