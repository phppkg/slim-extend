<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExtend;

use inhere\validate\Validator;
use Slim\Http\Request;

/**
 * Class ValidateRequest
 * @package slimExtend
 *
 * @property array $scene 当前验证的场景 通常是根据 controller 的 action name 来区分 e.g. add, edit, register
 */
abstract class ValidateRequest extends Validator
{

}