<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExtend\base;

use slimExtend\DataConst;
use inhere\validate\StrainerList;
use Slim;
use Slim\Http\Request as SlimRequest;
use Slim\Http\Uri;

/**
 * extension Slim's Request class
 * Class Request
 * @package slimExtend\base
 *
 * @method      string   getRaw()       getRaw($name, $default = null)      Get raw data
 * @method      integer  getInt()       getInt($name, $default = null)      Get a signed integer.
 * @method      integer  getNumber()    getNumber($name, $default = null)   Get an unsigned integer.
 * @method      float    getFloat()     getFloat($name, $default = null)    Get a floating-point number.
 * @method      boolean  getBool()      getBool($name, $default = null)     Get a boolean.
 * @method      boolean  getBoolean()   getBoolean($name, $default = null)  Get a boolean.
 * @method      string   getString()    getString($name, $default = null)
 * @method      string   getEmail()     getEmail($name, $default = null)
 * @method      string   getUrl()       getUrl($name, $default = null)      Get URL
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
        // trim($var)
        'string'  => 'trim',

        // abs((int)$var)
        'number'  => StrainerList::class . '::abs',
        // will use filter_var($var ,FILTER_SANITIZE_URL)
        'email'   => StrainerList::class . '::email',
        // will use filter_var($var ,FILTER_SANITIZE_URL)
        'url'     => StrainerList::class . '::url',
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
        if ( !isset($this->getParams()[$name]) ) {
            return $default;
        }

        $var = $this->getParams()[$name];

        return $this->doFilter($var, $filter, $default);
    }

    /**
     * Get part of it - 获取其中的一部分
     * @param array $needKeys
     * @return array
     */
    public function getPart(array $needKeys=[])
    {
        $needed = [];

        foreach ($needKeys as $key) {
            $needed[$key] = $this->getParam($key);
        }

        return $needKeys ? $needed : $this->getParams();
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
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (substr($name, 0, 3) === 'get') {
            $filter = substr($name, 3);
            $default = null;

            if (isset($arguments[1])) {
                $default = $arguments[1];
            }

            return $this->get($arguments[0], $default, lcfirst($filter));
        }

        throw new \BadMethodCallException("Method $name is not a valid method");
    }

    /**
     * @param $var
     * @param $filter
     * @param null $default
     * @return mixed|null
     */
    protected function doFilter($var, $filter, $default = null)
    {
        if ( $filter === static::FILTER_RAW || !is_scalar($var)) {
            return $var;
        }

        if ( !isset($this->filterList[$filter]) ) {

            // is custom callable filter
            if ( is_callable($filter) ) {
                $value = call_user_func($filter, $var);
            } else {
                $value = $default;
            }

            return $value;
        }

        $filter = $this->filterList[$filter];

        if ( !in_array($filter, DataConst::dataTypes()) ) {
            $var = call_user_func($filter, $var);
        } else {
            switch ( lcfirst(trim($filter)) ) {
                case DataConst::TYPE_ARRAY :
                    $var = (array)$var;
                    break;
                case DataConst::TYPE_BOOL :
                case DataConst::TYPE_BOOLEAN :
                    $var = (bool)$var;
                    break;
                case DataConst::TYPE_DOUBLE :
                case DataConst::TYPE_FLOAT :
                    $var = (float)$var;
                    break;
                case DataConst::TYPE_INT :
                case DataConst::TYPE_INTEGER :
                    $var = (int)$var;
                    break;
                case DataConst::TYPE_OBJECT :
                    $var = (object)$var;
                    break;
                case DataConst::TYPE_STRING :
                    $var = trim((string)$var);
                    break;
                default:
                    $var = $default;
                    break;
            }
        }

        return $var;
    }
}