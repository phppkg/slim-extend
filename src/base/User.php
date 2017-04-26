<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/6 0006
 * Time: 21:57
 */

namespace slimExt\base;

use inhere\library\collections\SimpleCollection;
use inhere\library\helpers\ObjectHelper;
use Psr\Http\Message\ResponseInterface;
use Slim;
use inhere\library\exceptions\InvalidArgumentException;
use inhere\library\exceptions\InvalidConfigException;

/**
 * Class User
 * @package slimExt\base
 *
 * @property int id
 */
class User extends SimpleCollection
{
    /**
     * @var string
     */
    protected $saveKey = '_slim_auth';

    /**
     * data storage
     * @var null|array|\ArrayAccess
     */
    protected $storage;

    /**
     * Exclude fields that don't need to be saved.
     * @var array
     */
    protected $excepted = ['password'];

    /**
     * the identity [model] class name
     * @var string
     */
    public $identityClass;

    /**
     * @var string
     */
    public $loginUrl = '/login';

    /**
     * @var string
     */
    public $loggedTo = '/';

    /**
     * @var string
     */
    public $logoutUrl = '/logout';

    /**
     * @var string
     */
    public $logoutTo = '/';

    /**
     * @var CheckAccessInterface
     */
    public $accessChecker;

    /**
     * @var string
     */
    public $idColumn = 'id';

    /**
     * checked permission caching list
     * @var array
     * e.g.
     * [
     *  'createPost' => true,
     *  'deletePost' => false,
     * ]
     */
    private $_accesses = [];

    const AFTER_LOGGED_TO_KEY  = '_after_logged_to';
    const AFTER_LOGOUT_TO_KEY  = '_after_logout_to';

    /**
     * don't allow set attribute
     * @param array $options
     * @param null|array|\ArrayAccess $storage
     * @throws InvalidConfigException
     */
    public function __construct($options = [], &$storage = null)
    {
        ObjectHelper::loadAttrs($this, $options);

        // Set storage
        if (is_array($storage) || $storage instanceof \ArrayAccess) {
            $this->storage = &$storage;
        } elseif (is_null($storage)) {
            if (!isset($_SESSION)) {
                throw new \RuntimeException('Flash messages middleware failed. Session not found.');
            }
            $this->storage = &$_SESSION;
        } else {
            throw new \InvalidArgumentException('Flash messages storage must be an array or implement \ArrayAccess');
        }

        // if have already login
        parent::__construct($this->getStorageData());
    }

    /**
     * @param IdentityInterface $user
     * @return bool
     */
    public function login(IdentityInterface $user)
    {
        $this->clear();
        $this->setIdentity($user);

        return $this->isLogin();
    }

    /**
     * logout
     */
    public function logout()
    {
        $this->clear();
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     * @throws InvalidConfigException
     */
    public function loginRequired(Request $request, Response $response)
    {
        if (!$authUrl = $this->loginUrl) {
            throw new InvalidConfigException("require config property 'loginUrl'!");
        }

        $this->setLoggedTo($request->getRequestUri());
        $msg = Slim::$app->language->tran('needLogin');

        // when is xhr
        if ( $request->isXhr() ) {
            $data = ['redirect' => $authUrl];

            return $response->withJson($data, __LINE__, $msg);
        }

        return $response->withRedirect($authUrl)->withMessage($msg);
    }

    /**
     * check user permission
     * @param string $permission a permission name or a url
     * @param array $params
     * @param bool|true $caching
     * @return bool
     */
    public function can($permission, $params = [], $caching = true)
    {
        return $this->canAccess($permission, $params, $caching);
    }
    public function canAccess($permission, $params = [], $caching = true)
    {
        if (isset($this->_accesses[$permission])) {
            return $this->_accesses[$permission];
        }

        $access = false;

        if ($checker = $this->getAccessChecker()) {
            $access = $checker->checkAccess($this->getId(), $permission, $params);

            if ($caching) {
                $this->_accesses[$permission] = $access;
            }
        }

        return $access;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->get($this->idColumn) ?: 0;
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

    /**
     * {@inheritDoc}
     */
    public function clear()
    {
        $this->data = $this->_accesses = [];
        unset($this->storage[$this->saveKey]);
    }

    /**
     * @param bool|false $force
     * @throws InvalidConfigException
     */
    public function refreshIdentity($force=false)
    {
        $id = $this->getId();

        if ($this->identityClass === null) {
            throw new InvalidConfigException('The property [identityClass] must be set.');
        }

        /* @var $class IdentityInterface */
        $class = $this->identityClass;

        if (!$force && ($data = $this->getStorageData())) {
            $this->sets($data);
        } elseif ($user = $class::findIdentity($id)) {
            $this->setIdentity($user);
        } else {
            throw new \RuntimeException('The refresh auth data is failure!!');
        }
    }

    /**
     * @param IdentityInterface $identity
     * @throws InvalidArgumentException
     */
    public function setIdentity(IdentityInterface $identity)
    {
        if ($identity instanceof IdentityInterface) {
            $this->sets((array)$identity);

            $this->storage[$this->saveKey] = $this->all();

            $this->_accesses = [];
        } elseif ($identity === null) {
            $this->data = [];
        } else {
            throw new InvalidArgumentException('The identity object must implement the ' . IdentityInterface::class);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function sets(array $data)
    {
        // except column at set.
        foreach ($this->excepted as $column) {
            if (isset($data[$column])) {
                unset($data[$column]);
            }
        }

        return parent::sets($data);
    }

    /**
     * @return array
     */
    public function getStorageData()
    {
        return $this->storage[$this->saveKey] ? $this->storage[$this->saveKey] : [];
    }

    /**
     * @return mixed
     */
    public function getAccessChecker()
    {
        return $this->accessChecker ? : \Slim::get('accessChecker');
    }

    /**
     * @return string
     */
    public function getLogoutTo()
    {
        return $this->logoutTo;
    }

    /**
     * @param $url
     */
    public function setLogoutTo($url)
    {
        $this->logoutTo = trim($url);
    }

    /**
     * @return string
     */
    public function getLoggedTo()
    {
        return $this->loggedTo;
    }

    /**
     * @param $url
     */
    public function setLoggedTo($url)
    {
        $this->loggedTo = trim($url);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);

        if ( method_exists($this, $getter) ) {
            return $this->$getter();
        }

        return parent::__get($name);
    }
}
