<?php

namespace app\models;

use Slim;
use slimExt\base\RecordModel;
use inhere\libraryPlus\auth\IdentityInterface;

/**
 * Class Users
 * @package app\models
 *
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $avatar
 * @property string $auth_key
 * @property int $id
 * @property int $role_id
 * @property int $updated
 * @property int $created
 */
class UserModel extends RecordModel implements IdentityInterface
{
    const DEFAULT_AVATAR = '/assets/images/avatars/avatar.png';

    public function columns()
    {
        return [
            'id'          => 'int',
            'username'    => 'string',
            'password'    => 'string',
            'avatar'      => 'string',
            'email'       => 'string',
            'role_id'        => 'int',
            'created'  => 'int',
            'updated'  => 'int',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return '@@user';
    }

    public function rules()
    {
        return [
            ['username,password', 'required', 'on' => 'create'],
            ['username,password,avatar,email', 'string'],
            ['id,created,updated,role_id', 'int'],
        ];
    }

    protected function beforeUpdate()
    {
        $this->updated = time();
    }

    /**
     * 定义保存数据时,当前场景允许写入的属性字段
     * @return array
     */
    public function sceneAttrs()
    {
        return [
            'create' => ['username', 'email', 'password','created'],
            'update' => ['username', 'email','created'],
        ];
    }

    /**
     * @param $name
     * @return mixed
     */
    public static function findByName($name)
    {
        return self::findOne(['username' => trim($name) ]);
    }

    public static function findIdentity($id)
    {
        return static::findByPk($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * @param $username
     * @param $password
     * @return bool|string
     */
    public static function login($username, $password)
    {
        $lang = Slim::$app->language;

        if ( !$username || !$password ) {
            return $lang->trans('missOrErrorParam');
        }

        if ( !$user= self::findByName($username) ) {
            return '用户不存在！';
        }

        if ( !password_verify($password, $user['password']) ) {
            return '密码错误！';
        }

        Slim::$app->user->login($user);

        return true;
    }

    /**
     * @return array|bool|int
     */
    public function register()
    {
        if ( !$this->isNew() ) {
            throw new \LogicException('current user have already registered.');
        }

        // check username
        if ( self::findOne(['username' => trim($this->username)], 'id') ) {
            return "username [{$this->username}] have already registered.";
        }

        // check email
        if ( self::findOne(['email' => trim($this->email)], 'id') ) {
            return "email [{$this->email}] have already used.";
        }

        $this->password   = password_hash($this->password, PASSWORD_BCRYPT);
        $this->avatar     = self::DEFAULT_AVATAR;
        $this->created = time();
        $this->insert();

        if ( $this->id ) {
            return true;
        }

        return 'register failure!!';
    }
}
