<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-03-17
 * Time: 11:26
 */

namespace slimExt\console\controllers;

use inhere\console\Controller;

/**
 * Class GeneraterController
 * @package slimExt\console\controllers
 */
class GeneraterController extends Controller
{
    protected static $name = 'gen';

    protected static $description = 'Generater model,controller,logic class';

    /**
     * Generater a model class of the project
     * @usage {command} db=mydb table=user
     * @arguments
     *  table<red>*</red>    the model table name
     *  db        the database service name in the app container. default: <cyan>db</cyan>
     *  name      the model name. default is equals to table name.
     *  namespace the model class namespace. default: <cyan>app\models</cyan>
     *  path      the model class file path. default: <cyan>@src/models</cyan>(allow use path alias)
     */
    public function modelCommand()
    {
        $db = \Slim::db();
        var_dump($db);
    }

    /**
     * Generater a controller class of the project
     * @arguments
     *  type  the controller class type <blue>rest|normal|console<blue>
     *
     */
    public function controllerCommand()
    {}

    /**
     * Generater a console command class of the project
     * @arguments
     *  name      the model name. default is equals to table name.
     *  namespace the model class namespace. default: <cyan>app\models</cyan>
     *  path      the model class file path. default: <cyan>@src/models</cyan>(allow use path alias)
     *
     */
    public function commandCommand()
    {}

    /**
     * Generater a logic class of the project
     * @arguments
     *  type  Who do you want to clear cache of type
     *
     */
    public function logicCommand()
    {}
}
