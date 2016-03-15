<?php
/**
 * @author Inhere
 * @version v1.0
 * Use : validate
 * Date : 2015-1-5
 */
namespace ulue\libs\validate;

use ulue\libs\validate\String;
// usulueee\core\utils\StaticInvoker;

abstract class Validate
{


    static public $rules = [
        'require'   =>  '/\S+/',
        'email'     =>  '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
        'url'       =>  '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/',
        'url_all'   =>  '/^(http|https|ftp):\/\/([A-Z0-9][A-Z0-9_-]*(?:\.[A-Z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i',
        'currency'  =>  '/^\d+(\.\d+)?$/', # 货币
        'number'    =>  '/^\d+$/',
        'zip'       =>  '/^\d{6}$/',
        'integer'   =>  '/^[-\+]?\d+$/',
        //正整数
        'positive_integer'   =>  '/^[0-9]*[1-9][0-9]*$/',
        //负整数
        'negative_integer'   =>  '/^-[0-9]*[1-9][0-9]*$/',
        //小数
        'decimal_number'   =>  '/^(-?\d+)(\.\d+)?$/',
        'double'    =>  '/^[-\+]?\d+(\.\d+)?$/',
        'english'   =>  '/^[A-Za-z]+$/',
        //汉字(字符)
        'chinese'   =>  '/^[\u4e00-\u9fa5]+$/',
        //中文及全角标点符号(字符)
        'all_chinese'   =>  '/^[\u3000-\u301e\ufe10-\ufe19\ufe30-\ufe44\ufe50-\ufe6b\uff01-\uffee]+$/',
        'html_tag'   =>  '/^<(.*)(.*)>.*<\/\1>|<(.*) \/>$/',
        'y/m/d'   =>  '/^(\d{4}|\d{2})\/(([12][0-9])|(3[01])|(0?[1-9]))\/((1[0-2])|(0?[1-9]))$/',
        'y-m-d'   =>  '/^(\d{4}|\d{2})-((1[0-2])|(0?[1-9]))-(([12][0-9])|(3[01])|(0?[1-9]))$/',
    ];

    public function __construct()
    {
        # code...
    }

   /**
    * @extends from StaticInvoker::allowInvokerCall()
    * @return array |string
    */
    /*protected function allowInvokerCall()
    {
        return 'email, url, number, english, chinese';
    }*/

    public function addRule($name,$rule)
    {
        if (String::owner()->isString($name) && String::owner()->isString($rule)) {
            self::$rules[$name] = $rule;
        }
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param string $value  要验证的数据
     * @param string $rule 验证规则 require email url currency number integer english
     * @return boolean
     */
    static public function match($value,$rule)
    {
        $value    = trim($value);
        $validate = self::$rules;

        // 检查是否有内置的正则表达式
        if (isset($validate[strtolower($rule)])){
            $rule       =   $validate[strtolower($rule)];
        }

        if ( empty($rule)) {
            \Trigger::error('请传入验证规则！');
        }

        return preg_match($rule,$value)===1;
    }

    static public function url($value)
    {
        return self::match($value,'url');
    }

    static public function email($value)
    {
        return self::match($value,'email');
    }

    static public function number($value)
    {
        return self::match($value,'number');
    }

    static public function integer($value)
    {
        return self::match($value,'integer');
    }

    static public function double($value)
    {
        return self::match($value,'double');
    }

    static public function english($value)
    {
        return self::match($value,'english');
    }


}
