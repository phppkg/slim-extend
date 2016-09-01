<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/1
 * Time: 下午4:51
 */

namespace slimExt\base;

/**
 * Class AccessChecker
 * @package slimExt\base
 */
class AccessChecker implements CheckAccessInterface
{
    /**
     * @param $userId
     * @param $permission
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, $params = [])
    {
        return true;
    }
}