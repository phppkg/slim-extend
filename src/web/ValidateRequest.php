<?php

/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/2/19 0019
 * Time: 23:35
 */

namespace slimExt\web;

use inhere\validate\Validation;

/**
 * Class ValidateRequest
 * @package slimExt
 *
 * @property array $scene 当前验证的场景 通常是根据 controller 的 action name 来区分 e.g. add, edit, register
 */
abstract class ValidateRequest extends Validation
{

}
