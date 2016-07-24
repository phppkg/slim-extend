<?php
/**
 *
 */
namespace slimExt\helpers;

use Slim;

class TplHelper
{
    /**
     * @example
     *
     * in view:
     * {{ _globals.helper.csrfField() | raw }}
     *
     * @param Request $request
     * @return string
     */
    public function csrfField()
    {
        /** @var Slim\Csrf\Guard */
        $csrf = Slim::get('csrf');

        // CSRF token name and value
        $nameKey  = Slim::get('csrf')->getTokenNameKey();
        $valueKey = Slim::get('csrf')->getTokenValueKey();

        list($name,$value) = array_values(Slim::get('csrf')->generateToken());

        return <<<EOF
<input type="hidden" id="csrf_name" name="$nameKey" value="$name">
<input type="hidden" id="csrf_value" name="$valueKey" value="$value">
EOF;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function csrfData()
    {
        return Slim::get('csrf')->generateToken();
    }


    public function __call($method, array $args=[])
    {
        if ( function_exists($method) ) {
            return call_user_func_array($method, $args);
        }
    }
}