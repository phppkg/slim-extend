<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/25
 * Time: 下午3:12
 */

namespace slimExt\base;

use Slim;

/**
 * Class Base
 * @package app\modules\admin\controllers
 */
class ModuleController extends Controller
{
    /**
     * @return array
     */
    protected function addTwigGlobalVar()
    {
        $vars = parent::addTwigGlobalVar();

        $currentModule = Slim::$app->module();

        $vars[$currentModule->name . 'Config'] = $currentModule->config;
        $vars[$currentModule->name . 'Params'] = $currentModule->config->get('params',[]);

        return $vars;
    }


}