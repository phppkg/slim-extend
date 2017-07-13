<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace slimExt\console\controllers;

use inhere\console\Controller;
use inhere\validate\Validation;

/**
 * Class GeneratorController
 * @package slimExt\console\controllers
 */
class GeneratorController extends Controller
{
    protected static $name = 'gen';

    protected static $description = 'Generator model,controller,logic class. [<info>built in</info>]';

    private $tplVars = [];

    protected function init()
    {
        list($d, $t) = explode(' ', date('Y/m/d H:i'));

        $this->tplVars = [
            '{@date}' => $d,
            '{@time}' => $t,
            '{@author}' => 'Generator',
        ];
    }

    /**
     * Generator a model class of the project
     * @usage {command} db=mydb table=user
     * @arguments
     *  table<red>*</red>    the model table name
     *  db        the database service name in the app container. default: <cyan>db</cyan>
     *  name      the model name. default is equals to table name.
     *  namespace the model class namespace. default: <cyan>app\models</cyan>
     *  path      the model class file path. default: <cyan>@src/models</cyan>(allow use path alias)
     *
     * @options
     *  --tpl   custom the controller class tpl file. (<comment>todo ...</comment>)
     *
     * @param \inhere\console\io\Input $input
     * @param \inhere\console\io\Output $output
     * @return int
     */
    public function modelCommand($input, $output)
    {
        $db = \Slim::db();

        $output->printVars($db);

        return 0;
    }

    /**
     * Generator a web|console controller class of the application
     * @arguments
     *  name<red>*</red>     the controller class name. will auto add suffix: Controller
     *  type      the controller class type, allow: <blue>norm,rest,cli</blue>. (<info>norm</info>)
     *  namespace the controller class namespace. (<cyan>app\controllers</cyan>)
     *  parent    the controller class's parent class. default:
     *  - norm <cyan>slimExt\web\Controller</cyan>
     *  - rest <cyan>slimExt\web\RestController</cyan>
     *  - cli  <cyan>inhere\console\Controller</cyan>
     *  path      the controller class file path. (<cyan>@src/controllers</cyan>)(allow use path alias)
     *  actions   the controller's action names. multiple separated by commas. (norm/cli <cyan>index</cyan>,rest <cyan>gets</cyan>) (will auto add suffix: Action|Command)
     * @options
     *  -f      whether force override exists's file. (<info>false</info>)
     *  --tpl   custom the controller class tpl file. (<comment>todo ...</comment>)
     *
     * @param \inhere\console\io\Input $input
     * @param \inhere\console\io\Output $output
     * @return int
     */
    public function controllerCommand($input, $output)
    {
        // $name = $input->getRequiredArg('name');
        $types = ['rest', 'norm', 'cli'];
        $vd = Validation::make($input->getArgs(), [
            ['name', 'required', 'msg' => 'the argument [name] is required. please input by name=VALUE'],
            ['name', 'string', 'min' => 2, 'max' => 32],
            ['type', 'in', $types, 'msg' => 'the argument [type] only allow: ' . implode(',', $types)],
            ['path', 'string'],
        ])->validate();

        if ($vd->fail()) {
            $output->liteError($vd->firstError());

            return 70;
        }

        $name = $vd->getValid('name');
        $type = $vd->getValid('type', 'norm');

        $defNp = 'app\\controllers';
        $suffix = 'Action';
        $defPath = '@src/controllers';
        $defParent = 'slimExt\web\Controller';
        $defActions = 'index';

        if ($type === 'cli') {
            $defNp = 'app\\console\\controllers';
            $suffix = 'Command';
            $defPath = '@src/console/controllers';
            $defParent = 'inhere\console\Controller';
        } elseif ($type === 'rest') {
            $defActions = 'gets';
            $defParent = 'slimExt\web\RestController';
        }

        $path = \Slim::alias($vd->get('path', $defPath));
        $namespace = $vd->get('namespace', $defNp);
        $className = ucfirst($name) . 'Controller';
        $fullClass = $namespace . '\\' . $className;
        $actions = $vd->get('actions', $defActions);
        $parent = $vd->get('parent', $defParent);

        $output->panel([
            'type' => $type,
            'name' => $name,
            'namespace' => $namespace,
            'class name' => $className,
            'full class' => $fullClass,
            'parent name' => basename(str_replace('\\', '/', $parent)),
            'parent class' => $parent,
            'path' => $path,
            'actions' => $actions,
        ], 'controller info', [
            'ucfirst' => false,
        ]);

        return 0;
    }

    /**
     * Generator a console command class of the project
     * @arguments
     *  name      the model name. default is equals to table name.
     *  namespace the model class namespace. default: <cyan>app\models</cyan>
     *  path      the model class file path. default: <cyan>@src/models</cyan>(allow use path alias)
     *
     * @return int
     */
    public function commandCommand()
    {
        return 0;
    }

    /**
     * Generator a logic class of the project
     * @arguments
     *  type  Who do you want to clear cache of type
     *
     * @return int
     */
    public function logicCommand()
    {
        return 0;
    }
}
