<?php

class DataModel
{
    private $_data = [];

    private $_rules = [];

    /**
     * 保存所有的验证错误信息
     * @var array
     */
    private $_errors   = [];

    private $_fieldNotes   = [];

    /**
     * 出现一个错误即停止验证
     * 默认 false 即是 全部验证并将错误信息保存到 {@link $_errors}
     * @var boolean
     */
    private $_hasErrorStop   = false;


    static public $defaultMsgs = [
        'int'       => '{attr} must is integer!',
        'bool'      => '{attr} must is boolean!',
        'float'     => '{attr} must is float!',
        'regexp'    => '{attr} Does not meet the conditions',
        'url'       => '{attr} not is url address!',
        'email'     => '{attr} not is email address!',
        'ip'        => '{attr} not is ip address!',
        'required'  => '{attr} is not block!',
        'length'    => '{attr} length must at rang {min},{max}',
        'minLength' => '{attr} min length is {min}',
        'maxLength' => '{attr} max length is {max}',
        'size'      => '{attr} integer must at rang {min},{max}',
        'min'       => '{attr} min value is {min}',
        'max'       => '{attr} max value is {max}',
        'in'       => '{attr} must in {range}',
        'string'       => '{attr} must is string',
        'isArray'       => '{attr} must is array',
        'callback'  => '{attr} Validation is not through',
        '_default_'  => '{attr} Validation is not through',
    ];

    public function __construct(array $data=[], array $rules=[])
    {
        $this->_data = $data;
        $this->_rules = $rules;
    }

    public function load(array $data)
    {
        $this->_data = $data;

        return $this;
    }

    public function hasErrorStop($value=true)
    {
        $this->_hasErrorStop = (bool)$value;

        return $this;
    }

    /**
     * [Validator::required] 验证是必定被调用的
     * @author inhere
     * @date   2015-08-11
     * @param  array      $rules
     *   [
     *       ['tagId,userId,freeTime', 'required', 'msg' => '{field} is required!'],
     *       ['tagId', 'size', 'min'=>4, 'max'=>567, 'msg' => '{field} is required!'], // 4<= tagId <=567
     *       ['userId', function($value){ echo "ttttt";}, 'msg' => '{field} is required!'],
     *   ];
     * @param  boolean    $hasErrorStop
     * @return self
     */
    public function validate(array $rules=[], $hasErrorStop=false)
    {
        if ($rules) {
            $this->_rules = array_merge($this->_rules, $rules);
        }

        $this->hasErrorStop($hasErrorStop);

        $requireChecked = [];

        foreach ($this->_rules as $rule) {
            $fields = array_shift($rule);
            $fields = is_string($fields) ? array_filter(explode(',', $fields),'trim') : (array)$fields;
            $validator = array_shift($rule);
            $message   = isset($rule['msg']) ? $rule['msg'] : null;
            unset($rule['msg']);
            $params    = $rule;
            $result    = false;

            foreach ($fields as $field) {

                if (!in_array($field, $requireChecked)) {
                    $result = Validators::required($this->_data, $field);
                }

                if ($result && $validator != 'required') {
                    array_unshift($params, $field);

                    if (is_callable($validator)) {
                        $result = call_user_func_array($validator, $params);
                        $validator = 'callback';
                    } elseif ( is_callable(['Validators', $validator]) ) {

                        $result = call_user_func_array( [ 'Validators', $validator ] , $params);
                    } else {
                        throw new Exception("validator $validator don't exists!");
                    }
                }

                if ($result === false) {
                    $this->_errors[] = [
                        $field => $this->getMessage($validator, $field, $message)
                    ];

                    if ( $this->_hasErrorStop ) {
                        return false;
                    }
                }
            }

            $message = null;
        }

        return !$this->hasError();
    }

    /**
     * 字段名注释
     * @return array
     */
    public function fieldNotes($fieldNotes=[])
    {
        // $fieldNotes;
        return [
            'userId'          => '用户id',
            'tagId'           => '擅长标签',
            'freeTime'        => '空闲日期',
            'distanceRequire' => '距离要求',
            'note'            => '备注',
            'insertTime'      => '插入时间',
            'lastReadTime'    => '最后阅读时间',
        ];
    }

    public function getNotes()
    {
        return $this->_fieldNotes;
    }

    public function clearErrors()
    {
        return $this->_errors = [];
    }

    public function hasError()
    {
        return !empty($this->_errors);
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function firstError()
    {
        $e =  $this->_errors;

        return array_shift($e);
    }

    public function lastError()
    {
        $e =  $this->_errors;

        return array_pop($e);
    }

    public function getMessage($name, $field, $msg=null)
    {
        $defaultMsgs = self::$defaultMsgs;
        $msg = $msg ? : (isset($defaultMsgs[$name]) ? $defaultMsgs[$name]: $defaultMsgs['_default_']);

        $notes = $this->fieldNotes();

        $fieldNote = isset($notes[$field]) ? $notes[$field] : $field;

        return strtr($msg, [
            '{field}' => $fieldNote
            ]);
    }

    public function __get($name)
    {
        $method = 'get' . ucfirst($name);

        if ( isset($this->_data[$name]) ) {
            return $this->_data[$name];
        // } elseif ( method_exists($this, $method)) {
        //     return $this->$method();
        }

        return null;
    }

}