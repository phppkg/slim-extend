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
     * @param string $permission It is a permission name OR url path
     * e.g
     * name: (使用权限名称来控制权限，更灵活方便，但开始时不容易理解)
     *   createPost managePost
     * path: (使用url path来指定权限，粒度更细，更易理解 但比较固定，不方便更改 )
     *   /post/add /post/*
     *
     * 推荐结合 name 和 urlpath 来制定和管理权限
     * @param array $params
     * @return bool
     */
    public function checkAccess($userId, $permission, $params = [])
    {
        // use url path
        if ( $permission{0} === '/' ) {
            # code...
        } else {

        }

        return true;
    }
}
