<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/20 0020
 * Time: 01:04
 */

namespace slimExtend\twig;

use Slim;
use Slim\Http\Request;
use Slim\Csrf\Guard;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Guard
     */
    protected $csrf;

    public function __construct( Request $request, Guard $csrf)
    {
        $this->request = $request;
        $this->csrf = $csrf;
    }

    public function getName()
    {
        return 'mder';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('csrf_field', array($this, 'csrfField')),
//            new \Twig_SimpleFunction('lang_trans', array($this, 'baseUrl')),
        ];
    }

    /**
     *
     */
    public function csrfField()
    {
        // CSRF token name and value
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $this->request->getAttribute($nameKey);
        $value = $this->request->getAttribute($valueKey);

        return <<<EOF
<input type="hidden" name="$nameKey" value="$name">
<input type="hidden" name="$valueKey" value="$value">
EOF;
    }
}