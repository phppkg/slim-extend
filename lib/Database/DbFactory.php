<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\database;

/**
 * Class DbFactory
 * @package slimExt\database
 *
 * @referrer Windwalker\Database\DatabaseFactory
 */
class DbFactory
{
    /**
     * The default DB object.
     * @var AbstractDriver
     */
    protected static $db;

    /**
     * Property instances.
     * [
     *     'name1' => \PDO,
     *     'name2' => \PDO,
     * ]
     * @var  array
     */
    protected static $instances = [];

    /**
     * getPdo
     * @param string $name
     * @param array $dbArgs
     * @param bool $forceNew
     * @throws \InvalidArgumentException
     * @return  AbstractDriver
     */
    public static function getDbo($name = null, array $dbArgs = [], $forceNew = false)
    {
        // No name name given, we return default DB object.
        if (!$name) {
            return self::$db;
        }

        // Create new instance if this name not exists.
        if ($forceNew || empty(self::$instances[$name])) {
            self::$instances[$name] = static::createDbo($dbArgs);

            // Set default DB object.
            if (!self::$db) {
                self::$db = self::$instances[$name];
            }
        }

        return self::$instances[$name];
    }

    /**
     * setPdo
     * @param string $name
     * @param AbstractDriver $db
     * @return  void
     */
    public static function setDbo($name, AbstractDriver $db = null)
    {
        self::$instances[$name] = $db;
    }

    /**
     * setDb
     * @param  string $name
     * @param   AbstractDriver $db
     */
    public static function setDefaultDbo($name, AbstractDriver $db)
    {
        if ($db) {
            self::$instances[$name] = self::$db = $db;
        }
    }

    /**
     * createPdo
     * @param array $options
     * e.g.
     *
     * [
     *     'dsn' => 'sqlite:/var/www/xx.db'
     * ]
     *
     * [
     *     'dsn' => 'mysql:dbname=testDb;host=127.0.0.1',
     *     'username' => 'demo',
     *     'password' => 'demo'
     * ]
     * @throws \RuntimeException
     * @return  AbstractDriver
     */
    public static function createDbo(array $options)
    {
        // Sanitize the database connector options.
        $options['driver'] = preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']);
        $options['database'] = $options['database'] ?? null;
        $options['select'] = $options['select'] ?? true;

        // Use custom Resource
//        $resource = isset($options['resource']) ? $options['resource'] : null;

        // Derive the class name from the driver.
        $class = __NAMESPACE__ . '\\' . ucfirst(strtolower($options['driver'])) . 'Driver';

        // If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Unable to load Database Driver: %s', $options['driver']));
        }

        /** @var AbstractDriver $class */
        if (!$class::isSupported()) {
            throw new \RangeException('Database driver ' . $options['driver'] . ' not supported.');
        }

        // Create our new Driver connector based on the options given.
        try {
            $instance = new $class($options);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
        }

        return $instance;
    }
}
