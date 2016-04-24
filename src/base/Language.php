<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/2/24
 * Time: 15:04
 */

namespace slimExt\base;

use Slim;

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
 */
class Language extends \inhere\tools\language\Language
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

    /**
     * default file name, when use multifile. (self::type == self::TYPE_MULTIFILE)
     * @var string
     */
    protected $defaultFile = 'default';

    protected function prepare($options, $fileType)
    {
        // maybe use path alias
        $options['path'] = Slim::alias($options['path']);

        parent::prepare($options, $fileType);
    }

    /**
     * language translate
     *
     * 1. allow multi arguments. `tran(string $key , mixed $arg1 , mixed $arg2, ...)`
     *
     * @example
     * ```
     *  // on language config
     * userNotFound: user [%s] don't exists!
     *
     *  // on code
     * $msg = Slim::$app->language->tran('userNotFound', 'demo');
     * ```
     *
     * 2. allow fetch other config file data, when use multifile. (`static::$type === static::TYPE_MULTIFILE`)
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
     * //output: user [demo] don't exists!
     * $msg = Slim::$app->language->tran('userNotFound', 'demo');
     *
     * // will fetch value at `en/app.yml`
     * //output: the app user [demo] don't exists!
     * $msg = Slim::$app->language->tran('app:userNotFound', 'demo');
     *
     * ```
     *
     * @param $key
     * @param array $args
     * @param string $default
     * @param string $lang
     * @return string
     */
//    public function translate($key, $args = [], $default = 'No translate.', $lang = '')

}
