<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\base;

use Slim;
use inhere\validate\ValidationTrait;
use Windwalker\Query\Query;

/**
 * Class BaseModel
 * @package slimExt
 *
 */
abstract class Model extends Collection
{
    use ValidationTrait;

    const SCENE_DEFAULT = 'default';

    /**
     * @var bool
     */
    protected $enableValidate = true;

    /**
     * Validation class name
     */
    //protected $validateHandler = '\inhere\validate\Validation';


    /**
     * @param $data
     * @return static
     */
    public static function load($data)
    {
        return new static($data);
    }
}
