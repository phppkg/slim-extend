<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/2/24
 * Time: 15:04
 */

namespace slimExtend\base;

use slimExtend\DataCollector;
use Slim;

/**
 * Class Language
 * @package slimExtend\base
 *
 * property $type
 *  if type equal to 1, use monofile. this is default.
 *
 *  if type equal to 2, use multifile.
 *
 *
 */
class Language extends DataCollector
{
    /**
     * current use language
     * @var string
     */
    protected $lang = 'en';

    /**
     * language config file path
     * @var string
     */
    protected $path = '@src/resources/languages';

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

    /**
     * file separator char, when use multifile.
     * e.g:
     *  Slim::$app->language->tran('app:createPage');
     * will fetch `createPage` value at the file `{$this->path}/{$this->lang}/app.yml`
     * @var string
     */
    protected $fileSeparator = ':';

    /**
     * loaded main language config file, data saved in {@link self::$data}
     * @var string
     */
    protected $mainFile = '';

    /**
     * loaded other config file list.
     * @var array
     */
    protected $otherFiles = [];

    /**
     * saved other config file data
     * @var DataCollector[]
     */
    protected $others = [];

    // use monofile. e.g: at config dir `{$this->path}/en.yml`
    const TYPE_MONOFILE  = 1;

    // use multifile. e.g: at config dir `{$this->path}/en/default.yml` `en/app.yml`
    const TYPE_MULTIFILE = 2;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        parent::__construct(null, self::FORMAT_PHP, 'language');

        $this->prepare($options);
    }

    protected function prepare($options)
    {
        foreach (['lang', 'path', 'defaultFile'] as $key) {
            if ( isset($options[$key]) ) {
                $this->$key = $options[$key];
            }
        }

        if ( isset($options['type']) && in_array($options['type'], $this->getTypes()) ) {
            $this->type = (int)$options['type'];
        }

        // maybe use path alias
        $this->path = Slim::alias($this->path);

        $this->mainFile = $this->type === self::TYPE_MONOFILE ?
            $this->path . DIR_SEP . "{$this->lang}.yml" :
            $this->getDirectoryFile($this->defaultFile);

        // check
        if ( !is_file($this->mainFile) ) {
            throw new \RuntimeException("Main language file don't exists! File: {$this->mainFile}");
        }

        // load main language file data.
        $this->loadYaml($this->mainFile);
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
     * 2. allow fetch other config file data, when use multifile. (`self::$type === self::TYPE_MULTIFILE`)
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
     * @return string
     */
    public function translate($key)
    {
        if ( !$key ) {
            throw new \InvalidArgumentException('A lack of parameters or error.');
        }

        $args    = func_get_args();
        $args[0] = $this->get($key);

        // if use multifile.
        if ( $this->type === self::TYPE_MULTIFILE ) {
           $this->handleMultiFile($key, $args);
        }

        if ( !$args[0] ) {
            throw new \InvalidArgumentException('No corresponding configuration of the translator. KEY: ' . $key);
        }

        // There are multiple parameters?
        return func_num_args()>1 ? call_user_func_array('sprintf', $args) : $args[0];
    }
    public function tran($key)
    {
        return call_user_func_array([$this,'translate'], func_get_args());
    }

    /**
     * @param $key
     * @param array $args
     */
    protected function handleMultiFile($key, array &$args)
    {
        $key = trim($key, $this->fileSeparator);

        // Will try to get the value from the other config file
        if ( ($pos = strpos($key, $this->fileSeparator)) >0 ) {
            $name    = substr($key, 0, $pos);
            $realKey = substr($key,$pos+1);

            // check exists
            if ( $collector = $this->getOther($name) ) {
                $args[0] = $collector->get($realKey);
            }
        }
    }

    /**
     * @param $name
     * @return string
     */
    public function getDirectoryFile($name)
    {
        return $this->path . DIR_SEP . $this->lang . DIR_SEP . trim($name) . '.yml';
    }

    /**
     * @param $name
     * @return DataCollector
     */
    public function getOther($name)
    {
        // the first time fetch, instantiate it
        if ( !isset($this->others[$name]) ) {
            $otherFile = $this->getDirectoryFile($name);

            if ( is_file($otherFile) ) {
                $this->otherFiles[$name]  = $otherFile;
                $this->others[$name] = new DataCollector($otherFile, self::FORMAT_YML, $name);
            }
        }

        return isset($this->others[$name]) ? $this->others[$name] : [];
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return [self::TYPE_MONOFILE, self::TYPE_MULTIFILE];
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDefaultFile()
    {
        return $this->defaultFile;
    }

}