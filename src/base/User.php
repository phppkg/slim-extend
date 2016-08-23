<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/6 0006
 * Time: 21:57
 */

namespace slimExt\base;

/**
 * Class User
 * @package slimExt\base
 *
 * @property int id
 */
class User extends Collection
{
    /**
     * @var string
     */
    protected static $saveKey = '_slime_auth';

    /**
     * Exclude fields that don't need to be saved.
     * @var array
     */
    protected $excepted = ['password'];

    /**
     * don't allow set attribute
     * @param array $items
     */
    public function __construct($items=[])
    {
        parent::__construct();

        // if have already login
        if ( $user = session(self::$saveKey) ){
            $this->clear();
            $this->sets($user);
        }
    }

    /**
     * @param array $user
     * @return static
     */
    public function login($user)
    {
        // except column at set.
        foreach ($this->excepted as $column) {
            if ( isset($user[$column])) {
                unset($user[$column]);
            }
        }

        if ( $user instanceof Collection) {
            $user = $user->all();
        }

        $this->clear();
        $this->sets($user);

        session([static::$saveKey => $user]);

        return $this;
    }

    public function logout()
    {
        $this->clear();

        unset($_SESSION[static::$saveKey]);
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return count($this->data) !== 0;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return !$this->isLogin();
    }
}