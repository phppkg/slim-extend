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
    private $tplPath;

    protected function init()
    {
        list($d, $t) = explode(' ', date('Y/m/d H:i'));

        $this->tplPath = SLIM_EXT_PATH . '/resources/tpl';
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
     *  name<red>*</red>     the controller class name.
     *  type      the controller class type, allow: <blue>norm,rest,cli</blue>. (<info>norm</info>)
     *  namespace the controller class namespace. (<cyan>app\controllers</cyan>)
     *  parent    the controller class's parent class. default:
     *  - norm <cyan>slimExt\web\Controller</cyan>
     *  - rest <cyan>slimExt\web\RestController</cyan>
     *  - cli  <cyan>inhere\console\Controller</cyan>
     *  path      the controller class file path. (<cyan>@src/controllers</cyan>)(allow use path alias)
     *  actions   the controller's action names. multiple separated by commas ','. (norm/cli <cyan>index</cyan>,rest <cyan>gets</cyan>)
     * @options
     *  -o,--override    whether override exists's file. (<info>false</info>)
     *  --preview        preview generate code(<info>false</info>)
     *  --suffix         the controller class suffix(<info>Controller</info>)
     *  --action-suffix  the controller action suffix(norm/cli <info>Action</info>,rest <info>Command</info>)
     *  --tpl            custom the controller class tpl file. (<comment>todo ...</comment>)
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
        $actionTpl = 'web-action.tpl';

        if ($type === 'cli') {
            $defNp = 'app\\console\\controllers';
            $suffix = 'Command';
            $defPath = '@src/console/controllers';
            $defParent = 'inhere\console\Controller';
            $actionTpl = 'group-command.tpl';
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
        $file = $path . '/' . $className . '.php';

        $data = [
            'type' => $type,
            'name' => $name,
            'namespace' => $namespace,
            'className' => $className,
            'fullClass' => $fullClass,
            'parentName' => basename(str_replace('\\', '/', $parent)),
            'parentClass' => $parent,
            'actions' => $actions,
            'path' => $path,
            'file' => $file,
        ];

        $output->panel($data, 'controller info', [
            'ucfirst' => false,
        ]);

        if (!$input->sameOpt(['yes', 'y']) && !$this->confirm('Check that the above information is correct')) {
            $output->write(' Exit. Byebye');
            return 0;
        }

        $tplVars = $this->tplVars;
        $tplContent = file_get_contents($this->tplPath . '/controller.tpl');

        foreach ($data as $key => $value) {
            $key = '{@' . $key . '}';
            $tplVars[$key] = $value;
        }

        // padding action methods
        if ($actions = explode(',', $actions)) {
            $actionContents = '';
            $tplAction = file_get_contents($this->tplPath . '/' . $actionTpl);

            foreach ($actions as $action) {
                $actionContents .= str_replace('{@action}', $action, $tplAction);
            }

            $tplVars['{@methods}'] = $actionContents;
        }

        $content = strtr($tplContent, $tplVars);
        if ($input->boolOpt('preview')) {
            $output->write("\n```php\n" . $content . "\n```\n");
        }

        if (is_file($file) && !$this->confirm('Target file exists, override it', false)) {
            $output->write(' Exit. Byebye');
            return 0;
        }

        if (file_put_contents($file, $content)) {
            $output->liteSuccess('Write content to file success!');
            return 0;
        }

        $output->liteError('Write content to file failed!');

        return -1;
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
