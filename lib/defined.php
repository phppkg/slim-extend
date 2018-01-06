<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/3/18
 * Time: 11:14
 */

defined('DIR_SEP') || define('DIR_SEP', DIRECTORY_SEPARATOR);
defined('URL_SEP') || define('URL_SEP', '/');

define('PDT_ENV', 'pdt');
define('PRE_ENV', 'pre');
define('TEST_ENV', 'test');
define('DEV_ENV', 'dev');
define('LOC_ENV', 'loc');

define('SLIM_EXT_PATH', __DIR__);

//if (!class_exists('Slim', false)) {
//    class Slim extends \slimExt\BaseSlim
//    {}
//}
