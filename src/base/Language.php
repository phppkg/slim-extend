<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/2/24
 * Time: 15:04
 */

namespace slimExt\base;

use Slim;
use inhere\librarys\language\LanguageManager;

/**
 * Class Language
 * @package slimExt\base
 *
 * property $type
 *  if type equal to 1, use monofile. this is default.
 *
 *  if type equal to 2, use multifile.
 *
 *
 * how to use language translate ?
 *
 * 1. allow multi arguments. `tran(string $key , array [$arg1 , $arg2], string $default)`
 *
 * @example
 * ```
 *  // on language config file
 * userNotFound: user [%s]  don't exists!
 *
 *  // on code
 * $msg = Slim::$app->language->tran('userNotFound', 'demo');
 * ```
 *
 * 2. allow fetch other config file data, when use multifile. (`static::$type === static::USE_MULTIFILE`)
 *
 * @example
 * ```
 * // on default config file (e.g. `en/default.yml`)
 * userNotFound: user [%s] don't exists!
 *
 * // on app config file (e.g. `en/app.yml`)
 * userNotFound: the app user [%s] don't exists!
 *
 * // on code
 * // will fetch value at `en/default.yml`
 * $msg = Slim::$app->language->tran('userNotFound', 'demo');
 * //output $msg: user [demo] don't exists!
 *
 * // will fetch value at `en/app.yml`
 * $msg = Slim::$app->language->tran('app:userNotFound', 'demo');
 * //output $msg: the app user [demo] don't exists!
 *
 * ```
 *
 *
 */
class Language extends LanguageManager
{
    /**
     * language config file path
     * @var string
     */
    protected $path = '@resources/languages';

    /**
     * type of language config
     * @var int
     */
    protected $type = 1;

    protected function prepare($options, $fileType)
    {
        // maybe use path alias
        $options['path'] = Slim::alias($options['path']);

        parent::prepare($options, $fileType);
    }
}
