<?php

namespace SlimExt\Web;

use Inhere\Library\Files\File;
use Psr\Http\Message\ResponseInterface;
use Slim;
use Inhere\Exceptions\NotFoundException;
use SlimExt\Helpers\TwigHelper;

/**
 * Class Controller
 * @package SlimExt\Web
 */
abstract class Controller extends AbstractController
{
    //events
    const EVENT_BEFORE_VIEW = 'beforeView';
    const EVENT_AFTER_VIEW = 'afterView';

    const EVENT_BEFORE_RENDER = 'beforeRender';
    const EVENT_AFTER_RENDER = 'afterRender';

    const ENGINE_TWIG = 'twig';
    const ENGINE_PHP = 'php';

    const TOP_JS = 'topJsFiles';
    const TOP_JS_CODE = 'topJsCode';
    const BTM_JS = 'btmJsFiles';
    const BTM_JS_CODE = 'btmJsCode';

    /**
     * @var string
     */
    public $layout = '';

    /**
     * @var string
     */
    public $defaultAction = 'index';

    /**
     * action name suffix.
     * so, the access's real controller method name is 'action name' + 'suffix'
     * @var string
     */
    public $actionSuffix = 'Action';

    /**
     * the template Engine. e.g 'php' 'twig'
     * @var string
     */
    protected $tplEngine = 'twig';

    /**
     * template helper class name.
     * can use `{{ _globals.helper.propertyName }}` OR `{{ _globals.helper.methodName(arg1[, arg2, ...]) }}`
     * access instance of the $tplHelperClass
     * @var string
     */
    protected $tplHelperClass = TwigHelper::class;

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
    protected $appendTplVars = [
        '_assets' => [
            'cssCode'  => '',
            'cssFiles' => [],
            'topJsCode'  => '',
            'btmJsCode'  => '',
            'topJsFiles'  => [],
            'btmJsFiles'  => [],
        ],
        '_page' => [
            'title' => 'My Site',
            'language' => 'en',
            'description' => 'My Site\'s description',
            'keywords' => 'My Site\' keywords',
        ]
    ];

    /**
     * @var string
     */
    protected $bodyBlock = 'body';

    /**********************************************************
     * the view render handle
     **********************************************************/

    /**
     * php tpl render
     * @param $view
     * @param array $args
     * @return ResponseInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $view, array $args = []): ResponseInterface
    {
        if ($this->tplEngine === self::ENGINE_TWIG) {
            return $this->renderTwig($view, $args);
        }

        $response = $this->response;
        $settings = Slim::get('settings')['renderer'];
        $view = $this->getViewPath($view, $settings);

        // add tpl global var
        list($varKey, $varList) = $this->handleGlobalVar($settings);
        $args[$varKey] = $varList;

        $this->appendVarToView($args);

        /** @var \Slim\Views\PhpRenderer $renderer */
        $renderer = Slim::get('renderer');

        return $renderer->render($response, $view, $args);
    }

    /**
     * twig tpl render
     * @param $view
     * @param array $args
     * @return ResponseInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function renderTwig(string $view, array $args = []): ResponseInterface
    {
        $response = $this->response;
        $settings = Slim::get('settings')['twigRenderer'];
        $view = $this->getViewPath($view, $settings);

        // use twig render
        /** @var \Slim\Views\Twig $twig */
        $twig = Slim::get('twigRenderer');

        $globalVar = $this->addTwigGlobalVar();
        list($globalKey, $globalVar) = $this->handleGlobalVar($settings, $globalVar);

        // add custom extension
        // $twig->addExtension(new \SlimExt\twig\TwigExtension( $c['request'], $c['csrf'] ));
        $this->appendVarToView($args);
        $args['_IS_PJAX'] = false;

        // is pjax request
        if (Slim::$app->request->isPjax()) {
            // X-PJAX-URL: https://github.com/inhere/library
            // X-PJAX-Version: 23434
            /** @var Response $response */
            $response = $response
                ->withHeader('X-PJAX-URL', (string)Slim::$app->request->getUri())
                ->withHeader('X-PJAX-Version', config('params.pjax_version', '1.0'));

            $args[$globalKey] = $globalVar;
            $args['_IS_PJAX'] = true;
            $rendered = $twig->getEnvironment()->loadTemplate($view)->renderBlock($this->bodyBlock, $args);

            return $response->write($rendered);
        }

        // add tpl global var
        $twig->getEnvironment()->addGlobal($globalKey, $globalVar);

        // Fetch rendered template {@see \Slim\Views\Twig::fetch()}
        $rendered = $twig->fetch($view, $args);

        // return response
        return $response->write($rendered);
    }

    /**
     * render a string to browser
     * @param string $string
     * @return ResponseInterface
     */
    public function renderString(string $string): ResponseInterface
    {
        return $this->response->write($string);
    }

    /////////////////////////////////////////////////////////////
    /// assets manage
    /////////////////////////////////////////////////////////////

    /**
     * @param $title
     * @return $this
     */
    protected function setTitle($title)
    {
        $this->appendTplVars['_page']['title'] = $title;

        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    protected function addCssCode($code)
    {
        $this->appendTplVars['_assets']['cssCode'] .= "$code\n";

        return $this;
    }

    /**
     * @param string|array $file
     * @param null|string $key
     * @return $this
     */
    protected function addCss($file, $key = null)
    {
        $jsFiles = $this->appendTplVars['_assets']['cssFiles'];

        if (\is_string($file)) {
            if ($key) {
                $jsFiles[$key] = $file;
            } else {
                $jsFiles[] = $file;
            }
        } else {
            $jsFiles = array_merge($jsFiles, $file);
        }

        $this->appendTplVars['_assets']['cssFiles'] = $jsFiles;

        return $this;
    }

    /**
     * @param string $code
     * @param string $pos
     * @return $this
     */
    protected function addJsCode($code, $pos = self::BTM_JS_CODE)
    {
        $this->appendTplVars['_assets'][$pos] .= "$code\n";

        return $this;
    }

    /**
     * @param string|array $file
     * @param string $pos
     * @param null|string $key
     * @return $this
     */
    protected function addJs($file, $pos = self::BTM_JS, $key = null)
    {
        $jsFiles = $this->appendTplVars['_assets'][$pos];

        if (\is_string($file)) {
            if ($key) {
                $jsFiles[$key] = $file;
            } else {
                $jsFiles[] = $file;
            }
        } else {
            $jsFiles = array_merge($jsFiles, $file);
        }

        $this->appendTplVars['_assets'][$pos] = $jsFiles;

        return $this;
    }

    /////////////////////////////////////////////////////////////
    /// view variable collection
    /////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    protected function addTwigGlobalVar(): array
    {
        $globalVar = [
            'user' => Slim::$app->user,
            'config' => Slim::get('config'),
            'params' => Slim::get('config')->get('params', []),
            'lang' => Slim::get('language'),
            'messages' => Slim::$app->request->getMessage(),
        ];

        if ($class = $this->tplHelperClass) {
            $globalVar['helper'] = new $class;
        }

        return $globalVar;
    }

    protected function appendVarToView(array &$args)
    {
        foreach ($this->appendTplVars as $key => $value) {
            if (!isset($args[$key])) {
                $args[$key] = $value;
            }
        }
    }

    /**
     * @param $var
     * @param $val
     * @return $this
     */
    protected function addTplVar($var, $val)
    {
        $this->appendTplVars[$var] = $val;

        return $this;
    }

    /**
     * @param array $vars
     * @return $this
     */
    protected function addTplVars(array $vars)
    {
        foreach ($vars as $var => $val) {
            $this->appendTplVars[$var] = $val;
        }

        return $this;
    }

    const GLOBAL_VAR_NAME_CONFIG_KEY = 'global_var_key';
    const GLOBAL_VAR_LIST_CONFIG_KEY = 'global_var_list';

    /**
     * @param array $settings
     * @param array $varList
     * @return array
     */
    protected function handleGlobalVar(array $settings, array $varList = []): array
    {
        // form settings
        if (!empty($settings[static::GLOBAL_VAR_LIST_CONFIG_KEY])) {
            $varList = $varList ?
                array_merge($varList, $settings[static::GLOBAL_VAR_LIST_CONFIG_KEY]) :
                $settings[static::GLOBAL_VAR_LIST_CONFIG_KEY];
        }

        // form current controller
        if ($this->tplGlobalVarList) {
            $varList = $varList ?
                array_merge($varList, $this->tplGlobalVarList) :
                $this->tplGlobalVarList;
        }

        // global var name at template
        if ($this->tplGlobalVarKey) {
            $varKey = $this->tplGlobalVarKey;
        } else {
            $varKey = empty($settings[static::GLOBAL_VAR_NAME_CONFIG_KEY]) ? '' : $settings[static::GLOBAL_VAR_NAME_CONFIG_KEY];

            if (!$varKey) {
                $varKey = static::DEFAULT_VAR_KEY;
            }
        }

        return [$varKey, $varList];
    }

    /**
     * get current controller's default templates path
     * @return string
     */
    protected function getTplPath(): string
    {
        if (!$this->tplPath) {
            $calledClass = \get_class($this);
            $ctrlName = trim(strrchr($calledClass, '\\'), '\\');

            $prefix = $this->tplPathPrefix ? '/' . $this->tplPathPrefix : '';
            $this->tplPath = $prefix . '/' . lcfirst(substr($ctrlName, 0, - 10));
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
        $viewSuffix = $settings['suffix'];
        $suffix = File::getSuffix($view, 1);

        // no extension
        if (!$suffix || $suffix !== trim($viewSuffix, '. ')) {
            $view .= '.' . trim($viewSuffix, '. ');
        }

        // if only file name, will auto add this tplPath.
        // e.g: $this->tplPath = '/blog/news'; $view = 'index.twig' --> `/blog/news/index.twig`
        if ($view[0] !== '/') {
            $view = $this->getTplPath() . '/' . $view;
        }

        return $view;
    }

    /**********************************************************
     * call the controller method
     **********************************************************/

    /**
     * when route setting as (no define action name):
     *
     * ```
     * $app->any('/users/{action}', controllers\User::class);
     * $app->any('/users/{action:index|add|edit}', controllers\User::class);
     * ```
     *
     * @param array $args
     * @return ResponseInterface
     * @throws NotFoundException
     */
    protected function processInvoke(array $args)
    {
        $action = !empty($args['action']) ? $args['action'] : $this->defaultAction;

        if (!$action) {
            throw new NotFoundException('The name of the method is not specified!', __LINE__);
        }

        // convert 'first-second' to 'firstSecond'
        if (strpos($action, '-')) {
            $action = ucwords(str_replace('-', ' ', $action));
            $action = str_replace(' ', '', lcfirst($action));
        }

        config()->set('urls.action', $action);
        $actionMethod = $action . ucfirst($this->actionSuffix);

        if (!method_exists($this, $actionMethod)) {
            throw new NotFoundException('Error Processing Request, Action [' . $actionMethod . '] don\'t exists!');
        }

        // if enable request action security filter
        if (true !== ($result = $this->doSecurityFilter($action))) {
            return $this->onSecurityFilterFail($result);
        }

        $resp = $this->$actionMethod($args);

        // if the action return is array data
        if (\is_array($resp)) {
            $resp = $this->response->withRawJson($resp);
        }

        return $resp;
    }

    /**
     * when route have been setting action name:
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

        return $this($args[0], $args[1], $args[2]);
    }
}
