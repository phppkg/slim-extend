<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/2 0002
 * Time: 22:33
 * @referrer https://github.com/auraphp/Aura.Sql
 */

namespace slimExt\database;

/**
 * $connections = new ConnectionLocator;
 * $connections->setDefault(function () {
 *     return DbFactory::getDbo([
 *         'dsn' => 'mysql:host=default.db.localhost;dbname=database',
 *         'user' => 'username',
 *         'pwd' => 'password'
 *     ]);
 * });
 */
class ConnectionLocator
{
    const DEFAULT_KEY = '__default';
    const TYPE_READER = 'reader';
    const TYPE_WRITER = 'writer';

    /**
     * connection callback list
     * @var array
     */
    private $values = [
        '__default' => null,
        // 'reader.slave1' => function(){},
        // 'writer.master' => function(){},
    ];

    /**
     * instanced connections
     * @var AbstractDriver[]
     */
    private $connections = [
        '__default' => null,
        // 'writer.master' => Object (slimExt\database\AbstractDriver),
    ];

    private $keys = [
        'readers' => [
            // 'slave1' => flase, // if it is in the $this->connections,  'slave1' => true
        ],
        'writers' => [],
    ];

    public function __construct(callable $default = null, $readers = [], $writers = [])
    {
        if ($default) {
            $this->setDefault($default);
        }

        foreach ($readers as $name => $reader) {
            $this->setReader($name, $reader);
        }

        foreach ($writers as $name => $writer) {
            $this->setWriter($name, $writer);
        }
    }

    /**
     * get default connection instance
     * @param callable $cb
     */
    public function setDefault(callable $cb)
    {
        $this->connections[self::DEFAULT_KEY] = $cb;
    }

    /**
     * get default connection instance
     * @return AbstractDriver
     */
    public function getDefault()
    {
        if (!$this->values[self::DEFAULT_KEY]) {
            throw new \InvalidArgumentException("The default connection don't setting!");
        }

        if (!isset($this->connections[self::DEFAULT_KEY])) {
            $this->connections[self::DEFAULT_KEY] = $this->values[self::DEFAULT_KEY]();
        }

        return $this->connections[self::DEFAULT_KEY];
    }

    /**
     * set Writer
     * @param string $name
     * @param callable $cb
     */
    public function setWriter($name, callable $cb)
    {
        $this->keys['writers'][$name] = false;
        $this->values['writer.' . $name] = $cb;
    }

    /**
     * get Writer
     * @param  string $name
     * @return AbstractDriver
     */
    public function getWriter($name = 'master')
    {
        return $this->getConnection(self::TYPE_WRITER, $name);
    }

    /**
     * [setReader
     * @param string $name
     * @param callable $cb
     */
    public function setReader($name, callable $cb)
    {
        $this->keys['readers'][$name] = false;
        $this->values['reader.' . $name] = $cb;
    }

    /**
     * get Reader
     * @param  string $name
     * @return AbstractDriver
     */
    public function getReader($name = null)
    {
        return $this->getConnection(self::TYPE_READER, $name);
    }

    /**
     * getConnection
     * @param  string $type
     * @param  string $name
     * @return AbstractDriver
     */
    protected function getConnection($type, $name)
    {
        // no reader/writer, return default
        if (!$this->keys[$type]) {
            return $this->getDefault();
        }

        if (!$name) {
            // return a random reader
            $name = array_rand($this->keys[$type]);
        }

        $key = $type . '.' . $name;

        if (!isset($this->keys[$type][$name])) {
            throw new \InvalidArgumentException("The connection [$type: $name] don't exists!");
        }

        // if not be instanced.
        if (!$this->keys[$type][$name]) {
            $this->connections[$key] = $instance = $this->values[$key]();
        }

        return $this->connections[$key];
    }

    public function getValue($name, $type = self::TYPE_READER)
    {
        if (!isset($this->keys[$type][$name])) {
            throw new \InvalidArgumentException("The connection [$type: $name] don't exists!");
        }

        return $this->values[$type][$name];
    }
}
