<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExtend;

use slimExtend\validate\ValidatorTrait;
use Slim\Http\Request;

/**
 * Class ValidateRequest
 * @package slimExtend
 *
 */
abstract class ValidateRequest
{
    use ValidatorTrait;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param Request|null $request
     */
    public function __construct(Request $request=null)
    {
        $this->data = $request->getParams();
    }
}