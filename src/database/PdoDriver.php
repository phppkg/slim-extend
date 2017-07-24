<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2017/7/24 0002
 * Time: 22:33
 */

namespace slimExt\database;

/**
 * Class PdoDriver
 * @package slimExt\database
 */
class PdoDriver extends AbstractDriver
{
////////////////////////////////////// run method //////////////////////////////////////

    /**
     * exec a statement, returns the number of rows that were modified
     * can use to INSERT, UPDATE, DELETE
     * @param string $statement
     * @return int
     */
    public function exec($statement = '')
    {
        $this->connect();
        $sql = $statement ?: (string)$this->query;
        $sql = $this->replaceTablePrefix(trim($sql));

        // add sql log
        if ($this->debug) {
            $this->dbLogger()->debug($sql . ';');
        }

        return $this->pdo->exec($sql);
    }

    /**
     * @param array $bindParams
     * @return static
     */
    public function execute(array $bindParams = [])
    {
        $this->connect();
        $sql = $this->replaceTablePrefix((string)$this->query);

        // add sql log
        if ($this->debug) {
            $this->dbLogger()->debug($sql . '; ');
        }

        $this->cursor = $this->pdo->prepare($sql, $this->driverOptions);

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

        $this->lastQuery = $this->cursor->queryString;

        // add sql log
        // if ( $this->debug ) {
        //     $this->dbLogger()->debug('Successful Executed.');
        // }

        try {
            $this->cursor->execute($bindParams);
        } catch (\PDOException $e) {
            throw new \RuntimeException($e->getMessage() . "\nSQL: {$this->lastQuery}, $boundedStr", (int)$e->getCode(), $e);
        }

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

    public function inTransaction()
    {
        return $this->inTrans();
    }

}
