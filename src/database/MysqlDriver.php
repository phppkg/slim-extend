<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 */

namespace slimExt\database;

use PDO;
use Windwalker\Database\Query\QueryHelper;
use Windwalker\Query\Query;
use Windwalker\Query\Mysql\MysqlQuery;

/**
 * Class MysqlDriver
 * @package slimExt\database
 */
class MysqlDriver extends AbstractDriver
{
    protected $name = 'mysql';
    public $supportInsertMulti = true;

    public function newQuery($forceNew=false)
    {
        if ( $forceNew || self::$newQueryCache === null ) {
            self::$newQueryCache = new MysqlQuery($this->pdo);
        }
        return self::$newQueryCache;
    }

    /**
     * Is this driver supported.
     *
     * @return  boolean
     */
    public static function isSupported()
    {
        return in_array('mysql', \PDO::getAvailableDrivers());
    }
}
