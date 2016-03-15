<?php

namespace slimExtend\base;

use Slim;
use slimExtend\helpers\TplHelper;

/**
 * Class BaseController
 * @package slimExtend\base
 */
abstract class BaseController extends RestFulController
{
    /**
     * @var string
     */
    public $actionSuffix = 'Action';

    /**
     * @var string
     */
    public $defaultAction = 'index';

    /**
     * current controller's default templates path.
     * tpl file: `$this->tplPath . '/' . $view`
     * @var string
     */
    protected $tplPath = '';

    /**
     * If the files in the child directory, set the child directory.
     * if not empty,tpl file: `$this->tplPath . '/' . $tplPathPrefix . '/' . $view`
     * @var string
     */
    protected $tplPathPrefix = '';

    /**
     * templates globals var key name, default is `DEFAULT_VAR_KEY`
     * @var array
     */
    protected $tplGlobalVarKey = '';

    const DEFAULT_VAR_KEY = '_globals';

    /**
     * templates globals var
     * @var array
     */
    protected $tplGlobalVarList = [];

    /**
     * __construct
     */
    public function __construct()
    {
        // save to container
        Slim::set('controller', $this);

        parent::__construct();
    }

    /**
     * php tpl render
     * @param $view
     * @param array $args
     * @param Response|null $response
     * @return Response
     */
    protected function render($view, Response $response, array $args = [])
    {
        $response = $response ?: Slim::get('response');
        $settings = Slim::get('settings')['renderer'];
        $view  = $this->getViewPath($view, $settings);

        // add tpl global var
        list($varKey, $varList) = $this->handleGlobalVar([], $settings);
        $args[$varKey] = $varList;

        return Slim::get('renderer')->render($response, $view, $args);
    }

    const RETURN_RENDERED = 1;
    const RETURN_RESPONSE = 2;
    const RETURN_BOTH     = 3;

    /**
     * twig tpl render
     * @param $view
     * @param Response|null $response
     * @param array $args
     * @param int $return
     * @return Response
     */
    protected function renderTwig($view, Response $response, array $args = [], $return=self::RETURN_RESPONSE)
    {
//        $response = $response ?: Slim::get('response');
        $settings = Slim::get('settings')['twigRenderer'];
        $view  = $this->getViewPath($view, $settings);

        // use twig render
        $twig = Slim::get('twigRenderer');

        $globalVar = $this->addTwigGlobalVar();
        list($globalKey, $globalVar) = $this->handleGlobalVar($globalVar, $settings);

        // add tpl global var
        $twig->getEnvironment()->addGlobal($globalKey, $globalVar);

        // add custom extension
        // $twig->addExtension(new \slimExtend\twig\TwigExtension( $c['request'], $c['csrf'] ));

        // Fetch rendered template {@see \Slim\Views\Twig::fetch()}
        $rendered = $twig->fetch($view, $args);

        if ( $return === self::RETURN_RENDERED ) {
            return $rendered;
        } elseif ( $return === self::RETURN_BOTH ) {
            $response->getBody()->write($rendered);

            return [$response, $rendered];
        }

        // return response
        $response->getBody()->write($rendered);

        return $response;
    }

    /**
     * @return array
     */
    protected function addTwigGlobalVar()
    {
        return [
            'user'     => Slim::$app->user,
            'page'     => Slim::get('pageSet')->loadObject(Slim::get('pageAttr')),
            'config'   => Slim::get('config'),
            'lang'     => Slim::get('language'),
            'helper'   => new TplHelper,
            'messages' => Slim::$app->request->getMessage(),
        ];
    }

    /**
     * @param array $varList
     * @param array $settings
     * @return array
     */
    protected function handleGlobalVar(array $varList=[], array $settings)
    {
        // form settings
        if ( !empty($settings['global_var_list']) ) {
            $varList = $varList ?
                array_merge($varList, $settings['global_var_list']) :
                $settings['global_var_list'];
        }

        // form current controller
        if ( $this->tplGlobalVarList ) {
            $varList = $varList ?
                array_merge($varList, $this->tplGlobalVarList) :
                $this->tplGlobalVarList;
        }

        // global var name at template
        if ( $this->tplGlobalVarKey ) {
            $varKey = $this->tplGlobalVarKey;
        } else {
            $varKey = empty($settings['global_var_key']) ? '' : $settings['global_var_key'];

            if ( !$varKey ) {
                $varKey = self::DEFAULT_VAR_KEY;
            }
        }

        return [$varKey, $varList];
    }

    /**
     * @param Request $request
     * @return string
     */
    public function csrfField(Request $request)
    {
        // CSRF token name and value
        $nameKey  = Slim::get('csrf')->getTokenNameKey();
        $valueKey = Slim::get('csrf')->getTokenValueKey();
        $name  = $request->getAttribute($nameKey);
        $value = $request->getAttribute($valueKey);

        return <<<EOF
<input type="hidden" name="$nameKey" value="$name">
<input type="hidden" name="$valueKey" value="$value">
EOF;
    }

    /**
     * get current controller's default templates path
     * @return string
     */
    protected function getTplPath()
    {
        if ( !$this->tplPath ) {
            $calledClass = get_class($this);
            $nodes = explode('\\', trim($calledClass,'\\'));
            $nodes = array_slice($nodes, 2); // remove `app` e.g: `app\controllers\SimpleAuth`
            $nodePath = implode('/', array_map(function($node){
                return lcfirst($node);
            }, $nodes));

            $prefix = $this->tplPathPrefix ? '/'.$this->tplPathPrefix : '';
            $this->tplPath = $prefix . '/' . $nodePath;
        }

        return $this->tplPath;
    }

    /**
     * get action's view file path
     * @param  string $view
     * @param  array $settings
     * @return string
     */
    protected function getViewPath($view, array $settings)
    {
        $tpl_suffix = $settings['tpl_suffix'];
        $suffix = get_extension($view);

        // no extension
        if ( !$suffix ||  $suffix !== trim($tpl_suffix,'. ') ) {
            $view .= '.' . trim($tpl_suffix,'. ');
        }

        // if only file name, will auto add this tplPath.
        // e.g: $this->tplPath = '/blog/news'; $view = 'index.twig' --> `/blog/news/index.twig`
        if ( $view[0] !== '/' ) {
            $view = $this->getTplPath() . '/' . $view;
        }

        return $view;
    }

    /**
     * beforeInvoke
     *  Might want to customize to perform the action name
     * @param  Request $request
     * @param  Response $response
     * @param  array $args
     * @return mixed
     */
    protected function beforeInvoke(Request $request, Response $response, array $args)
    {
        $action = $this->defaultAction;

        if ( !empty($args['action']) ) {
            $action = $args['action'];
        }

        $action .= ucfirst($this->actionSuffix);

        if ( method_exists($this, $action) ) {
            return $this->$action($request, $response, $args);
        }

        return false;
    }
}