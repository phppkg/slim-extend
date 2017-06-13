<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 下午9:48
 */

namespace slimExt\web;

use inhere\library\helpers\EnvHelper;
use inhere\library\helpers\PhpHelper;
use Slim;
use slimExt\base\App;
use slimExt\DataCollector;

/**
 * Todo ...
 * Class Module
 * @package slimExt\web
 *
 * Recommend, For the module's controller:
 *
 * ```
 *    use Slim;
 *    use slimExt\base\Controller;
 *    use app\modules\{admin}\Module;
 *
 *    ... ...
 *
 *    protected function addTwigGlobalVar()
 *    {
 *        $vars = parent::addTwigGlobalVar();
 *
 *        $module = Slim::$app->module(Module::NAME);
 *
 *        $vars[Module::NAME . 'Config'] = $module->config;
 *        $vars[Module::NAME . 'Params'] = $module->config->get('params',[]);
 *
 *        return $vars;
 *    }
 * ```
 */
abstract class Module
{
    /**
     * @var string
     */
    const NAME = '';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    protected $path = '';

    public $layout = 'default';

    /**
     * @var DataCollector
     */
    public $config;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->prepare();

        $this->init();
    }

    protected function prepare()
    {
        $name = static::NAME;

        if (!$name || !preg_match('/^[a-zA-Z][\w-]+$/i', $name)) {
            throw new \RuntimeException('required define module name (property $name)');
        }

        // get path
        $reflect = new \ReflectionClass($this);
        $this->path = dirname($reflect->getFileName());

        $globalFile = Slim::alias('@config') . '/module-' . $name . '.yml';
        $configFile = $this->path . '/config.yml';

        // runtime env config
        $this->config = DataCollector::make($configFile, DataCollector::FORMAT_YML)
            ->loadYaml(is_file($globalFile) ? $globalFile : '');

        //add path alias
        // Slim::alias('@' . $name, $this->path);
        // add twig views path
        // Slim::get('twigRenderer')->getLoader()->addPath($this->path . '/resources/views');
        // or php views path
        // Slim::get('renderer')->setTemplatePath($this->path . '/resources/views');
    }

    protected function init()
    {
        /*
         * Some init logic
         */
    }

    /**
     * register module to application
     * @param App $app
     */
    public static function register($app)
    {
        if (EnvHelper::isCli()) {
            static::registerCommands($app);
        } else {
            static::registerRoutes($app);
        }
    }

    /**
     * register route to web application
     * @param App $app
     */
    protected static function registerRoutes($app)
    {
        // require __DIR__ . '/routes.php';
    }

    /**
     * register command to console application
     * @param App $app
     */
    protected static function registerCommands($app)
    {
        // $app->add('...');
    }

}
