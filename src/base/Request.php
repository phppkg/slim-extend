<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\base;

use slimExt\DataConst;
use inhere\validate\StrainerList;
use Slim;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;

/**
 * extension Slim's Request class
 * Class Request
 * @package slimExt\base
 *
 * @method      string   getRaw($name, $default = null)      Get raw data
 * @method      integer  getInt($name, $default = null)      Get a signed integer.
 * @method      integer  getNumber($name, $default = null)   Get an unsigned integer.
 * @method      float    getFloat($name, $default = null)    Get a floating-point number.
 * @method      boolean  getBool($name, $default = null)     Get a boolean.
 * @method      boolean  getBoolean($name, $default = null)  Get a boolean.
 * @method      string   getString($name, $default = null)
 * @method      string   getTrimmed($name, $default = null)
 * @method      string   getSafe($name, $default = null)
 * @method      string   getEmail($name, $default = null)
 * @method      string   getUrl($name, $default = null)      Get URL
 *
 * @property  Uri $uri;
 */
class Request extends SlimRequest
{
    /**
     * return raw data
     */
    const FILTER_RAW = 'raw';

    /**
     * @var array
     */
    protected $filterList = [
        // return raw
        'raw'     => '',

        // (int)$var
        'int'     => 'int',
        // (float)$var or floatval($var)
        'float'   => 'float',
        // (bool)$var
        'bool'    => 'bool',
        // (bool)$var
        'boolean' => 'bool',
        // (string)$var
        'string'  => 'string',

        // trim($var)
        'trimmed'  => StrainerList::class . '::trim',

        // safe data
        'safe'  => 'htmlspecialchars',

        // abs((int)$var)
        'number'  => StrainerList::class . '::abs',
        // will use filter_var($var ,FILTER_SANITIZE_EMAIL)
        'email'   => StrainerList::class . '::email',
        // will use filter_var($var ,FILTER_SANITIZE_URL)
        'url'     => StrainerList::class . '::url',

        // will use filter_var($var ,FILTER_SANITIZE_ENCODED, $settings);
        'encoded'     => StrainerList::class . '::encoded',
    ];

    /**
     * getParams() alias method
     * @return array
     */
    public function all()
    {
        return $this->getParams();
    }

    /**
     * @return array|null|object
     */
    public function post()
    {
        return $this->getParsedBody();
    }

    /**
     * @param $name
     * @return \Slim\Http\UploadedFile
     */
    public function getUploadedFile($name)
    {
        return isset($this->getUploadedFiles()[$name]) ? $this->getUploadedFiles()[$name] : null;
    }

    /**
     * @return array
     */
    public function getMessage()
    {
        $messageList = [];
        $messages = Slim::$app->flash->getMessage(DataConst::FLASH_MSG_KEY) ?: [];

        foreach ($messages as $alert) {
            $messageList[] = json_decode($alert, true);
        }

        return $messageList;
    }

    /**
     * @param array $default
     * @return array
     */
    public function getOldInput($default = [])
    {
        if ( $data = Slim::get('flash')->getMessage(DataConst::FLASH_OLD_INPUT_KEY) ) {
            return json_decode($data[0], true);
        }

        return $default;
    }

    /**
     * @param string $name
     * @param mixed $default
     * @param string $filter
     * @return mixed
     */
    public function get($name, $default = null, $filter = 'raw')
    {
        $value = !isset($this->getParams()[$name]) ? $default : $this->getParams()[$name];

        return $this->filtering($value, $filter);
    }

    /**
     * Get part of it - 获取其中的一部分, 可以设置过滤
     * @param array $needKeys
     * $needKeys = [
     *     'name',
     *     'password',
     *     'status' => 'int'
     * ]
     * @return array
     */
    public function getPart(array $needKeys=[])
    {
        $needed = [];

        foreach ($needKeys as $key => $value) {
            if ( is_int($key) ) {
                $needed[$value] = $this->getParam($value)
            } else {
                $needed[$key] = $this->filtering($key, $value);
            }
        }

        return $needed;
    }

    /**
     * e.g: `http://xxx.com`
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->uri->getBaseUrl();
    }

    /**
     * path + queryString
     * e.g. `/content/add?type=blog`
     * @return string
     */
    public function getRequestUri()
    {
        return $this->getRequestTarget();
    }

    /**
     * Is this an XHR request?
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->isXhr();
    }

    /**
     * Is this an pjax request?
     * pjax = pushState + ajax
     * @return bool
     */
    public function isPjax()
    {
        return $this->isAjax() && ($this->getHeaderLine('X-PJAX') === 'true');
    }

    public function getPjaxContainer()
    {
        return $this->getHeaderLine('X-PJAX-Container');
    }

    /**
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (substr($name, 0, 3) === 'get' && $arguments) {
            $filter = substr($name, 3);
            $default = isset($arguments[1]) ? $arguments[1] : null;

            return $this->get($arguments[0], $default, lcfirst($filter));
        }

        throw new \BadMethodCallException("Method $name is not a valid method");
    }

    /**
     * @param $value
     * @param $filter
     * @param null $default
     * @return mixed|null
     */
    public function filtering($value, $filter)
    {
        if ( $filter === static::FILTER_RAW) {
            return $value;
        }

        // is a custom filter
        if ( !is_string($filter) || !isset($this->filterList[$filter]) ) {
            $result = $value;

            // is custom callable filter
            if ( is_callable($filter) ) {
                $result = call_user_func($filter, $value);
            }

            return $result;
        }

        // is a defined filter
        $filter = $this->filterList[$filter];

        if ( !in_array($filter, DataConst::dataTypes()) ) {
            $result = call_user_func($filter, $value);
        } else {
            switch ( lcfirst(trim($filter)) ) {
                case DataConst::TYPE_BOOL :
                case DataConst::TYPE_BOOLEAN :
                    $result = (bool)$value;
                    break;
                case DataConst::TYPE_DOUBLE :
                case DataConst::TYPE_FLOAT :
                    $result = (float)$value;
                    break;
                case DataConst::TYPE_INT :
                case DataConst::TYPE_INTEGER :
                    $result = (int)$value;
                    break;
                case DataConst::TYPE_STRING :
                    $result = (string)$value;
                    break;
                case DataConst::TYPE_ARRAY :
                    $result = (array)$value;
                    break;
                case DataConst::TYPE_OBJECT :
                    $result = (object)$value;
                    break;
                default:
                    $result = $value;
                    break;
            }
        }

        return $result;
    }
}
