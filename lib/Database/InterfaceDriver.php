<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/8/8
 * Time: 下午9:56
 */

namespace slimExt\database;

/**
 * Interface InterfaceDriver
 * @package slimExt\database
 */
interface InterfaceDriver
{
    public function connect();
    public function ping($pdo);
    public function disconnect();

    /**
     * @param string $table
     * @param array|\Iterator $data
     * @param string $priKey
     * @return array|bool|int
     */
    public function insert($table, $data, $priKey = '');
    public function update($table, $data, $key = 'id', $updateNulls = false);

    public function prepare($statement = null, array $driverOptions = []);
    public function execute(array $bindParams = []);
    public function freeResult($cursor = null);

    public function beginTrans($throwException = true);
    public function commit($throwException = true);
    public function rollBack($throwException = true);
    /**
     * @return bool
     */
    public function inTrans();

}
