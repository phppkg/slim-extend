<?php

namespace slimExt\base;

use Slim;
use slimExt\filters\AccessFilter;
use slimExt\filters\VerbFilter;
use slimExt\exceptions\NotFoundException;

/**
 * Class Controller
 * @package slimExt\base
 */
abstract class Controller extends RestFulController
{
    protected $layout = '';

    /**
     * @var string
     */
    public $actionSuffix = 'Action';

    /**
     * @var string
     */
    public $defaultAction = 'index';

    /**
     * template helper class name.
     * can use `{{ _globals.helper.propertyName }}` OR `{{ _globals.helper.methodName(arg1[, arg2, ...]) }}`
     * access instance of the $tplHelperClass
     * @var string
     */
    protected $tplHelperClass = '\slimExt\helpers\TwigHelper';

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
     * append variable to templates, if variable not exists.
     * @var array
     */
    protected $appendTplVar = [];

    /**
     * enable request method verify
     * @var bool
     */
    protected $enableMethodVerify = false;

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
     * @return Response
     */
    protected function render($view, array $args = [])
    {
        $response = $this->response ?: Slim::get('response');
        $settings = Slim::get('settings')['renderer'];
        $view  = $this->getViewPath($view, $settings);

        // add tpl global var
        list($varKey, $varList) = $this->handleGlobalVar($settings);
        $args[$varKey] = $varList;

        $this->appendVarToView($args);

        return Slim::get('renderer')->render($response, $view, $args);
    }

    const RETURN_RENDERED = 1;
    const RETURN_RESPONSE = 2;
    const RETURN_BOTH     = 3;

    /**
     * twig tpl render
     * @param $view
     * @param array $args
     * @param int $return
     * @return Response
     */
    protected function renderTwig($view, array $args = [], $return= self::RETURN_RESPONSE)
    {
        $response = $this->response;
        $settings = Slim::get('settings')['twigRenderer'];
        $view  = $this->getViewPath($view, $settings);

        // use twig render
        $twig = Slim::get('twigRenderer');

        $globalVar = $this->addTwigGlobalVar();
        list($globalKey, $globalVar) = $this->handleGlobalVar($settings, $globalVar);

        // add tpl global var
        $twig->getEnvironment()->addGlobal($globalKey, $globalVar);

        // add custom extension
        // $twig->addExtension(new \slimExt\twig\TwigExtension( $c['request'], $c['csrf'] ));
        $this->appendVarToView($args);

        // check var '_renderPartial'
        $args['_renderPartial'] = isset($args['_renderPartial']) ? $args['_renderPartial'] : false;

        // is pjax request
        if ( Slim::$app->request->isPjax() ) {
            $args['_renderPartial'] = true;

            // X-PJAX-URL:https://github.com/inhere/php-librarys
            // X-PJAX-Version: 23434
            $response = $response
                            ->withHeader('X-PJAX-URL', (string)Slim::$app->request->getUri())
                            ->withHeader('X-PJAX-Version', Slim::config('pjax_version', '1.0'));
        }

        // Fetch rendered template {@see \Slim\Views\Twig::fetch()}
        $rendered = $twig->fetch($view, $args);

        if ( $return === static::RETURN_RENDERED ) {
            return $rendered;
        } elseif ( $return === static::RETURN_BOTH ) {
            $response->getBody()->write($rendered);

            return [$response, $rendered];
        }

        // return response
        return $response->write($rendered);
    }

    /**
     * twig tpl render
     * @param $view
     * @param array $args
     * @param int $return
     * @return Response
     */
    protected function renderPartialTwig($view, array $args = [], $return= self::RETURN_RESPONSE)
    {
        $args['_renderPartial'] = true;

        return $this->renderTwig($view, $args, $return);
    }

    /**
     * @return array
     */
    protected function addTwigGlobalVar()
    {
        $globalVar = [
            'user'     => Slim::$app->user,
            'config'   => Slim::get('config'),
            'params'   => Slim::get('config')->get('params', []),
            'lang'     => Slim::get('language'),
            'messages' => Slim::$app->request->getMessage(),
        ];

        if ( $class = $this->tplHelperClass ) {
            $globalVar['helper'] = new $class;
        }

        return $globalVar;
    }

    protected function appendVarToView(array &$args)
    {
        if ($this->appendTplVar) {
            foreach ($this->appendTplVar as $key => $value) {
                if (!isset($args[$key])) {
                    $args[$key] = $value;
                }
            }
        }
    }

    const GLOBAL_VAR_NAME_CONFIG_KEY  = 'global_var_key';
    const GLOBAL_VAR_LIST_CONFIG_KEY  = 'global_var_list';

    /**
     * @param array $settings
     * @param array $varList
     * @return array
     */
    protected function handleGlobalVar(array $settings, array $varList=[])
    {
        // form settings
        if ( !empty($settings[static::GLOBAL_VAR_LIST_CONFIG_KEY]) ) {
            $varList = $varList ?
                array_merge($varList, $settings[static::GLOBAL_VAR_LIST_CONFIG_KEY]) :
                $settings[static::GLOBAL_VAR_LIST_CONFIG_KEY];
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
            $varKey = empty($settings[static::GLOBAL_VAR_NAME_CONFIG_KEY]) ? '' : $settings[static::GLOBAL_VAR_NAME_CONFIG_KEY];

            if ( !$varKey ) {
                $varKey = static::DEFAULT_VAR_KEY;
            }
        }

        return [$varKey, $varList];
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
            // remove `app` e.g: `app\controllers\SimpleAuth`
            $nodes = array_slice($nodes, 2);
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

    public function filters()
    {
        return [
            'access' => [
                'handler' => AccessFilter::class,
                'rules' => [
//                    [
//                        'actions' => ['login', 'error'],
//                        'allow' => true,
//                    ],
//                    [
//                        'actions' => ['logout', 'index'],
//                        'allow' => true,
//                        'roles' => ['@'],
//                    ],
                ],
            ],
            'verbs' => [
                'handler' => VerbFilter::class,
                'actions' => [
                    //'logout' => ['post'],
                ],
            ],
        ];
    }


    /*****************************
     * method call
     *****************************/

    /**
     * when route setting :
     *
     * ```
     * $app->any('/users/{action}', controllers\User::class);
     * $app->any('/users/{action:index|add|edit}', controllers\User::class);
     * ```
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return mixed
     * @throws NotFoundException
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // setting...
        $this->request = $request;
        $this->response = $response;

        // Maybe want to do something
        $this->beforeInvoke($args);

        $action = !empty($args['action']) ? $args['action'] : $this->defaultAction;

        // convert 'first-second' to 'firstSecond'
        if ( strpos($action, '-') ) {
            $action = ucwords(str_replace('-', ' ', $action));
            $action = str_replace(' ','',lcfirst($action));
        }

        Slim::config()->set('urls.action',$action);
        $action .= ucfirst($this->actionSuffix);

        // if enable request method verify
        if ( $this->enableMethodVerify ) {
            $action = strtolower($request->getMethod()) . ucfirst($action);
        }

        if ( method_exists($this, $action) ) {
            $response = $this->$action($args);

            // Might want to customize to perform the action name
            $this->afterInvoke($args);

            return $response;
        }

        throw new NotFoundException('Error Processing Request, Action [' . $action . '] don\'t exists!');
    }

    /**
     * when route setting :
     * ```
     * $app->get('/users/{id}', controllers\User::class . ':view');
     * ```
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $args[2]['action'] = $method;

        return $this->__invoke($args[0], $args[1], $args[2]);
    }
}
