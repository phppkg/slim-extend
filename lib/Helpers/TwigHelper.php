<?php
/**
 *
 */

namespace SlimExt\Helpers;

use inhere\exceptions\UnknownCalledException;
use Slim;

/**
 * Class TplHelper
 * @package SlimExt\Helpers
 */
class TwigHelper
{
//    public function loadCss($css, $opt = [])
//    {
//        AssetLoad::css($css, $opt);
//    }
//
//    public function loadJs($js, $opt = [])
//    {
//        AssetLoad::css($js, $opt);
//    }

    /**
     * @example
     *
     * in view:
     * {{ _globals.helper.csrfField() | raw }}
     *
     * @param bool $addId
     * @return string
     */
    public function csrfField($addId = true)
    {
        /** @var Slim\Csrf\Guard */
        // $csrf = Slim::get('csrf');

        // CSRF token name and value key
        // $nameKey  = Slim::get('csrf')->getTokenNameKey();
        // $valueKey = Slim::get('csrf')->getTokenValueKey();

        $data = Slim::get('csrf')->generateToken();
        list($nameKey, $valueKey) = array_keys($data);
        list($name, $value) = array_values($data);

        $nameId = $addId ? 'csrf_name' : '';
        $valueId = $addId ? 'csrf_value' : '';

        return <<<EOF
<input type="hidden" id="$nameId" name="$nameKey" value="$name">
<input type="hidden" id="$valueId" name="$valueKey" value="$value">
EOF;
    }

    /**
     * @return array
     */
    public function csrfData()
    {
        return Slim::get('csrf')->generateToken();
    }

    public function __call($method, array $args = [])
    {
        if (function_exists($method)) {
            return call_user_func_array($method, $args);
        }

        throw new UnknownCalledException("Call don't exists method [$method]");
    }
}
