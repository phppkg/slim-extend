<?php
/**
 * Created by sublime 3.
 * Auth: Inhere
 * Date: 14-9-28
 * Time: 10:35
 * Used: 主要功能是 hi
 */

namespace slimExtend\validate;

/**
 * Class Validator
 * @package slimExtend
 *
 * @property array $data
 */
trait ValidatorTrait
{
    /**
     * 当前验证的场景 -- 如果需要让一个验证器在多个类似情形下使用
     * (在MVC框架中，通常是根据 controller 的 action name 来区分。 e.g. add, edit, register)
     * @var string
     */
    protected $scene = '';

////////////////////////////////////////// validate data //////////////////////////////////////////

    /**
     * 保存所有的验证错误信息
     * @var array[]
     * $_errors = [
     *     [ field => errorMessage1 ],
     *     [ field => errorMessage2 ],
     *     [ field2 => errorMessage3 ]
     * ]
     */
    private $_errors   = [];

    /**
     * 出现一个错误即停止验证
     * 默认 false 即是 全部验证并将错误信息保存到 {@link $_errors}
     * @var boolean
     */
    private $_hasErrorStop   = false;

    /**
     * @var array
     */
    private $_rules   = [];

    /**
     * attribute field translate list
     * @var array
     */
    private $_attrTrans = [];

    /**
     * backup for the property data
     * @var array
     */
    private $_rawData = [];

    /**
     * @var bool
     */
    private $_hasValidated = false;

    /**
     * @return array
     */
    public function rules()
    {
        return [];
        /* e.g:
            return [
                // not set 'scene', enable this rule at all scene.
                [ 'tagId,userId', 'required', 'msg' => '{attr} is required!'],

                // set scene is add -- when `$this->scene == 'add'` enable this rule.
                [ 'tagId', 'size', 'min'=>4, 'max'=>567, 'scene' => 'add' ],

                // use callback and custom error message
                [ 'userId', function($value){ echo "$value ttg tg tt";}, 'msg' => '{attr} is required!'],
            ];
       */
    }

    /**
     * define attribute field translate list
     * @return array
     */
    public function attrTrans()
    {
        return [
            // 'field' => 'translate',
            // e.g. 'name'=>'名称',
        ];
    }

//    public static function scene()
//    {
//        return '';
//    }

    public function beforeValidate(){}

    /**
     * [Validator::required] 验证是必定被调用的
     * @author inhere
     * @date   2015-08-11
     * @param array $onlyChecked 只检查一部分属性
     * @param  boolean $hasErrorStop
     * @return bool
     * @throws \RuntimeException
     */
    public function validate(array $onlyChecked = [],$hasErrorStop=null)
    {
        if ( $this->_hasValidated ) {
            return $this;
        }

        if ( property_exists($this, 'data') ) {
            throw new \InvalidArgumentException('Must be defined attributes \'data (array)\' in the classes used.');
        }

        $data = $this->_rawData = $this->data;
        $this->beforeValidate();
        $this->clearErrors();

        is_bool($hasErrorStop) && $this->hasErrorStop($hasErrorStop);

//        $requireChecked = [];

        // 循环规则
        foreach ($this->collectRules() as $rule) {
            // 要检查的属性(字段)名称
            $names = array_shift($rule);
            $names = is_string($names) ? array_filter(explode(',', $names),'trim') : (array)$names;

            // 要使用的验证器
            $validator = array_shift($rule);

            // 错误提示消息
            $message   = isset($rule['msg']) ? $rule['msg'] : null;
            unset($rule['msg']);

            // 验证设置, 有一些验证器需要设置。 e.g. size()
            $copy = $rule;

            // 循环检查属性
            foreach ($names as $name) {
                if ( $onlyChecked && !in_array($name, $onlyChecked)) {
                     continue;
                }

                $result = ValidatorList::required($data, $name);

                if ($result && $validator !== 'required') {
                    array_unshift($copy, $data[$name]);// 压入当前属性值

                    if ( is_callable($validator) ) {
                        $result = call_user_func_array($validator, $copy);
                        $validator = 'callback';
                    } elseif ( is_string($validator) && method_exists($this, $validator) ) {

                        $result = call_user_func_array( [ $this, $validator ] , $copy);
                    } elseif ( is_callable([ValidatorList::class, $validator]) ) {

                        $result = call_user_func_array( [ ValidatorList::class, $validator ] , $copy);
                    } else {
                        throw new \RuntimeException("validator [$validator] don't exists!");
                    }
                }

                if ($result === false) {
                    $this->_errors[] = [
                        $name => $this->getMessage($validator, [ '{attr}' => $name ], $rule, $message)
                    ];

                    $this->afterValidate();

                    if ( $this->_hasErrorStop ) {
                        break;
                    }
                }
            }

            $message = null;
        }

        $this->afterValidate();

        // fix : deny repeat validate
        $this->_hasValidated = true;

        return $this;
    }

    public function afterValidate(){}

    /**
     * @return array
     */
    protected function collectRules()
    {
        $availableRules = [];
        $scene = $this->scene;

        // 循环规则, 搜集当前场景的规则
        foreach ($this->getRules() as $rule) {
            if ( isset($rule['scene']) && $rule['scene'] == $scene ) {
                unset($rule['scene']);
                $availableRules[] = $rule;
            } else {
                $availableRules[] = $rule;
            }
        }

        return $availableRules;
    }

//////////////////////////////////// error info ////////////////////////////////////

    public function clearErrors()
    {
        $this->_errors = [];
    }

    /**
     * @param null $val
     */
    public function hasErrorStop($val)
    {
        $this->_hasErrorStop = (bool)$val;
    }

    /**
     * 是否有错误
     * @date   2015-09-27
     * @return boolean
     */
    public function hasError()
    {
        return count($this->_errors) > 0;
    }
    public function fail()
    {
        return $this->hasError();
    }

    /**
     * @param $attr
     * @param $msg
     * @return mixed
     */
    public function addError($attr, $msg)
    {
        $this->_errors[$attr] = $msg;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * 得到第一个错误信息
     * @author inhere
     * @date   2015-09-27
     * @return array
     */
    public function firstError()
    {
        $e =  $this->_errors;

        return array_shift($e);
    }

    /**
     * 得到最后一个错误信息
     * @author inhere
     * @date   2015-09-27
     * @return array
     */
    public function lastError()
    {
        $e =  $this->_errors;

        return array_pop($e);
    }

    /**
     * 默认的错误提示信息
     * @var array
     */
    public static $errMsgs = [
        'int'       => '{attr} must is integer!',
        'bool'      => '{attr} must is boolean!',
        'float'     => '{attr} must is float!',
        'regexp'    => '{attr} Does not meet the conditions',
        'url'       => '{attr} not is url address!',
        'email'     => '{attr} not is email address!',
        'ip'        => '{attr} not is ip address!',
        'required'  => '{attr} is not block!',
        'length'    => '{attr} length must at rang {min} ~ {max}',
        'minLength' => '{attr} min length is {min}',
        'maxLength' => '{attr} max length is {max}',
        'size'      => '{attr} must is integer and at rang {min} ~ {max}',
        'min'       => '{attr} min value is {min}',
        'max'       => '{attr} max value is {max}',
        'in'        => '{attr} must in {range}',
        'string'    => '{attr} must is string',
        'isArray'   => '{attr} must is array',
        'callback'  => '{attr} validation is not through!',
        '_' => '{attr} validation is not through!',
    ];

    /**
     * 各个验证器的提示消息
     * @author inhere
     * @date   2015-09-27
     * @param  string $name 验证器名称
     * @param  array $params 待替换的参数
     * @param array $rule
     * @param  string $msg 提示消息
     * @return string
     */
    public function getMessage($name, array $params, $rule = [], $msg=null)
    {
        if ( !$msg ) {
            $msg = isset(self::$errMsgs[$name]) ? self::$errMsgs[$name]: self::$errMsgs['_'];
        }

        $labels = $this->getAttrTrans();
        $attrName = $params['{attr}'];
        $params['{attr}'] = isset($labels[$attrName]) ? $labels[$attrName] : $attrName;

        foreach ($rule as $key => $value) {
            $params['{' . $key . '}'] = $value;
        }

        return strtr($msg, $params);
    }

    /**
     * @return array
     */
    public function getAttrTrans()
    {
        return array_merge($this->attrTrans(), $this->_attrTrans);
    }

    /**
     * @param array $attrTrans
     * @return $this
     */
    public function setAttrTrans(array $attrTrans)
    {
        $this->_attrTrans = array_merge($this->_attrTrans, $attrTrans);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRule()
    {
        return $this->getRules() ? true : false;
    }

    /**
     * @return array
     */
    public function getRules()
    {
        return $this->_rules ?: $this->rules();
    }

    /**
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules)
    {
        $this->_rules = $rules;

        return $this;
    }

    /**
     * @return string
     */
    public function getScene()
    {
        return $this->scene;
    }

    /**
     * @param string $scene
     * @return static
     */
    public function setScene($scene)
    {
        $this->scene = $scene;

        return $this;
    }

    /**
     * Get all items in collection
     *
     * @return array The collection's source data
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Does this collection have a given key?
     *
     * @param string $key The data key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Set data item
     *
     * @param string $key The data key
     * @param mixed $value The data value
     * @return $this
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Get data item for key
     *
     * @param string $key     The data key
     * @param mixed  $default The default value to return if data key does not exist
     *
     * @return mixed The key's value, or the default value
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }


}
