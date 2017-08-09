<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace slimExt\console\controllers;

use inhere\console\Controller;
use inhere\library\files\Directory;
use inhere\validate\Validation;
use slimExt\web\RestController;

/**
 * Class GeneratorController
 * @package slimExt\console\controllers
 *
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
     * Generate a model class of the project
     * @usage {script} {command} type=db db=mydb name=user [...]
     * @arguments
     *  name      the model name<red>*</red>
     *  db        the database service name in the app container(<cyan>db</cyan>)
     *  type      the model type. allow: data,db(<cyan>data</cyan>)
     *            - data: it is a php data model
     *            - db: it is a database table data model
     *
     *  table     the model table name, default is equals to model 'name'
     *  namespace the model class namespace (<cyan>app\models</cyan>)
     *  parent    the model class's parent class
     *            - no db: <cyan>slimExt\base\Model</cyan>
     *            - use db: <cyan>slimExt\base\RecordModel</cyan>
     *
     *  path      the model class file path. allow use path alias(<cyan>@src/models</cyan>)
     *  fields    define the model fields. when the argument "type=data"
     *            format - filed1,type,trans;filed2=DEFAULT_VALUE,type,trans;filed3,type,trans
     *            e.g. fields="username,string,Username;password,string,Password;role,int,Role Type;"
     *
     * @options
     *  -y,--yes         don't ask anything(<info>false</info>)
     *  -o,--override    whether override exists's file(<info>false</info>)
     *  --preview        preview generate's code(<info>false</info>)
     *  --validate-rules generate field validate rules(<info>false</info>)
     *  --suffix         the model class suffix(<info>Model</info>)
     *  --tpl            custom the model class tpl file(<comment>todo ...</comment>)
     *
     * @example
     * {script} {command} name=user type=db fields="username,string,Username;role,int,Role Type;"
     * @param \inhere\console\io\Input $input
     * @param \inhere\console\io\Output $output
     * @return int
     */
    public function modelCommand($input, $output)
    {
//        $db = \Slim::db();
//        $output->printVars($input->getRequiredArg('table'), $db);
        $types = ['data', 'db'];
        $vd = Validation::make($input->getArgs(), [
            ['name', 'required', 'msg' => 'the argument "name" is required. please input by name=VALUE'],
            ['type', 'in', $types, 'default' => 'data', 'msg' => 'the argument "type" only allow: ' . implode(',', $types)],
            ['fields', 'required', 'when' => function($data) {
                return !isset($data['type']) || $data['type'] === 'data';
            }, 'msg' => 'the argument "fields" cannet be empty, when "type=data"(is defualt value)'],
            ['table,db,name,parent,path,namespace,fields', 'string'],
        ])->validate();

        if ($vd->fail()) {
            $output->liteError($vd->firstError());

            return 70;
        }

        // $data = $vd->all();
        // $data = $vd->getSafeData();

        $name = $vd->getValid('name');
        $type = $vd->getValid('type');
        $suffix = $input->getOpt('suffix', 'Model');

        $useDb = $type === 'db';
        $defNp = 'app\\models';
        $defPath = '@src/models';
        $defParent = \slimExt\base\Model::class;

        $dbService = $vd->get('db', 'db');
        $table = $vd->get('table', $name);
        $fields = trim($vd->get('fields'), ';');

        if ($useDb) {
            $defParent = \slimExt\base\RecordModel::class;
        }

        $path = \Slim::alias($vd->get('path', $defPath));
        $namespace = $vd->get('namespace', $defNp);
        $className = ucfirst($name) . $suffix;
        $fullClass = $namespace . '\\' . $className;
        $parent = $vd->get('parent', $defParent);
        $file = $path . '/' . $className . '.php';

        $data = [
            'name' => $name,
            'db' => $useDb ? $dbService : null,
            'table' => $useDb ? '@@' . $table : null,
            'className' => $className,
            'namespace' => $namespace,
            'fullClass' => $fullClass,
            'parentName' => basename(str_replace('\\', '/', $parent)),
            'parentClass' => $parent,
            'methods' => '',
            'fields' => $fields,
            'path' => $path . '（<comment>' . (is_dir($path) ? 'exists' : 'not-exists') . '</comment>)',
            'file' => $file . '(<comment>' . (is_file($file) ? 'exists' : 'not-exists') . '</comment>)',
        ];

        $yes = $input->sameOpt(['yes', 'y']);
        $output->panel($data, 'modle class info', [
            'ucfirst' => false,
        ]);

        if (!$yes && !$this->confirm('Check that the above information is correct')) {
            $output->write('Exit. Bye');
            return 0;
        }

        $rules = [];
        $data['fullCommand'] = $input->getFullScript();
        $data['columns'] = $data['rules'] = $data['translates'] = $data['properties'] = $data['defaultData'] = '';
        $fields = explode(';', trim($fields, '; '));
        $indent8 = str_repeat(' ', 8);
        $indent = str_repeat(' ', 12);
        foreach ($fields as $value) {
            if (!$value) {
                continue;
            }

            $info = explode(',', trim($value, ','));

            if (!$info || !$info[0]) {
                continue;
            }

            $field = trim($info[0]);

            if (strpos($field, '=')) {
                list($field, $value) = explode('=', $field);
                $value = is_numeric($value) ? $value : "'$value'";
                $data['defaultData'] .= "\n{$indent8}'{$field}' => {$value},";
            }

            $type = isset($info[1]) && strpos($info[1], 'int') !== false ? 'int' : 'string';
            $trans = isset($info[2]) ? trim($info[2]) : ucfirst($field);

            $rules[$type][] = $field;
            $data['columns'] .= "\n{$indent}['{$field}' => '{$type}'],";
            $data['translates'] .= "\n{$indent}'{$field}' => '{$trans}',";
            $data['properties'] .= "\n * @property $type \${$field}";
        }

        foreach ($rules as $type => $list) {
            $fieldStr = implode(',', $list);
            $data['rules'] .= "\n{$indent}['{$fieldStr}', '{$type}'],";
        }

        $this->appendTplVars($data);
        $tplContent = file_get_contents($this->tplPath . '/model.tpl');

        return $this->writeContent($file, $tplContent, $yes);
    }

    /**
     * Generate a web|console controller class of the application
     * @usage {command} name=test type=norm actions=index,create,update,delete
     * @arguments
     *  name      the controller class name.<red>*</red>
     *  type      the controller class type, allow: norm,rest,cli. (<cyan>norm</cyan>)
     *  namespace the controller class namespace. (<cyan>app\controllers</cyan>)
     *  parent    the controller class's parent class.
     *            default:
     *              - norm <cyan>slimExt\web\Controller</cyan>
     *              - rest <cyan>slimExt\web\RestController</cyan>
     *              - cli  <cyan>inhere\console\Controller</cyan>
     *  path      the controller class file path. allow use path alias. (<cyan>@src/controllers</cyan>)
     *  actions   the controller's action names. multiple separated by commas ','. (norm/cli: <cyan>index</cyan>,rest: <cyan>gets</cyan>)
     * @options
     *  -o,--override    whether override exists's file. (<info>false</info>)
     *  --preview        preview generate's code(<info>false</info>)
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
            ['name', 'required', 'msg' => 'the argument "name" is required. please input by name=VALUE'],
            ['name', 'string', 'min' => 2, 'max' => 32],
            ['type', 'in', $types, 'msg' => 'the argument "type" only allow: ' . implode(',', $types)],
            ['path', 'string'],
        ])->validate();

        if ($vd->fail()) {
            $output->liteError($vd->firstError());

            return 70;
        }

        $name = $vd->getValid('name');
        $type = $vd->getValid('type', 'norm');

        $defNp = 'app\\controllers';
        $actSuffix = 'Action';
        $defPath = '@src/controllers';
        $defParent = \slimExt\web\Controller::class;
        $defActions = 'index';
        $actionTpl = 'web-action.tpl';
        $properties = '';

        if ($type === 'cli') {
            $defNp = 'app\\console\\controllers';
            $actSuffix = 'Command';
            $defPath = '@src/console/controllers';
            $defParent = Controller::class;
            $actionTpl = 'group-command.tpl';
            $properties = <<<EOF
    /**
     * the group name
     * @var string
     */
    protected static \$name = '$name';

    /**
     * the group description message
     * @var string
     */
    protected static \$description = 'the group description message. [<info>by Generator</info>]';
EOF;
        } elseif ($type === 'rest') {
            $defActions = 'gets';
            $defParent = RestController::class;
        }

        $path = \Slim::alias($vd->get('path', $defPath));
        $suffix = $input->getOpt('suffix', 'Controller');
        $namespace = $vd->get('namespace', $defNp);
        $className = ucfirst($name) . $suffix;
        $fullClass = $namespace . '\\' . $className;
        $actions = $vd->get('actions', $defActions);
        $parent = $vd->get('parent', $defParent);
        $file = $path . '/' . $className . '.php';

        $data = [
            'type' => $type,
            'name' => $name,
            'className' => $className,
            'namespace' => $namespace,
            'fullClass' => $fullClass,
            'parentName' => basename(str_replace('\\', '/', $parent)),
            'parentClass' => $parent,
            'actions' => $actions . ", suffix: $actSuffix",
            'path' => $path . '(<comment>' . (is_dir($path) ? 'exists' : 'not-exists') . '</comment>)',
            'file' => $file . '(<comment>' . (is_file($file) ? 'exists' : 'not-exists') . '</comment>)',
        ];

        $yes = $input->sameOpt(['yes', 'y']);
        $output->panel($data, 'controller info', [
            'ucfirst' => false,
        ]);

        if (!$yes && !$this->confirm('Check that the above information is correct')) {
            $output->write('Exit. Bye');
            return 0;
        }

        $data['methods'] = '';
        $data['properties'] = $properties;

        if ($type === 'rest') {
            $data['methods'] = <<<EOF
    /**
     * the method Mapping
     * @return array
     */
    protected function methodMapping()
    {
        \$mapping = parent::methodMapping();
        // \$mapping['gets'] = 'index';
        // \$mapping['post.login'] = 'login';

        return \$mapping;
    }

EOF;
        }

        // padding action methods
        if ($actions = explode(',', $actions)) {
            $actSuffix = ucfirst($actSuffix);
            $actionContents = '';
            $tplAction = file_get_contents($this->tplPath . '/' . $actionTpl);

            foreach ($actions as $action) {
                $actionContents .= str_replace(['{@action}', '{@suffix}'], [$action, $actSuffix], $tplAction);
            }

            $data['methods'] .= $actionContents;
        }


        $this->appendTplVars($data);
        $tplContent = file_get_contents($this->tplPath . '/controller.tpl');

        return $this->writeContent($file, $tplContent, $yes);
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

    private function appendTplVars(array $data)
    {
        foreach ($data as $key => $value) {
            $key = '{@' . $key . '}';
            $this->tplVars[$key] = $value;
        }
    }

    private function writeContent($file, $tplContent, $yes = false)
    {
        $content = strtr($tplContent, $this->tplVars);

        $preview = $this->input->boolOpt('preview');
        if ($preview || (!$yes && $this->confirm('do you want preview code'))) {
            $this->output->write("\n```php\n" . $content . "\n```\n");
        }

        if (is_file($file)) {
            if (!$yes && !$this->confirm('Target file exists, override it', false)) {
                $this->output->write('Exit. Bye');
                return 0;
            }
        } elseif (!$yes && !$this->confirm('Now, will write content to file')) {
            $this->output->write('Exit. Bye');
            return 0;
        }

        if (!is_dir(dirname($file))) {
            Directory::create(dirname($file));
        }

        if (file_put_contents($file, $content)) {
            $this->output->liteSuccess("Write content to file success!\nFILE: $file");
            return 0;
        }

        $this->output->liteError('Write content to file failed!');

        return -1;
    }
}
