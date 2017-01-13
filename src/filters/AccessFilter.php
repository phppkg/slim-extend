<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;

use Slim;

/**
 * Class AccessFilter
 * auth/permission check
 * @package slimExt\filters
 */
class AccessFilter extends BaseFilter
{
    /**
     * access rules
     * @var array
     */
    public $rules = [
    /*
        // first rule
        [
            // action list
            // ['logout', 'index'], ['*']
            'actions' => [],

            // true - allow access
            // false - deny access
            'allow' => true,

            // use defined mark char: '?' guest user '@' logged user '*' all user.
            // use custom role name. like 'member', 'admin' (the role name is must be unique, and it is save on dabatase.)
            // can also use role id: 12 43 (it is not recommend)
            'roles' => ['*'],
        ],
        // more rule
        [ ... ],
        [ ... ],
    */
    ];

    protected function doFilter( $action )
    {
        // current user
        $user = Slim::$app->user;
        $allow = true;

        foreach ($this->rules as $rule) {
            // no limit
            if (
                !$rule
                || ($actions = (array)ArrHelper::get('actions', $rule))
                || ($roles = (array)ArrHelper::get('roles', $rule))
            ) {
                continue;
            }

            // don't match current action
            if (self::MATCH_ALL !== $actions[0] || !in_array($action, $actions)) {
                continue;
            }

            $allow = ArrHelper::get('allow', $rule, false);

            // find match all user, char: *
            if ( false !== array_search(self::MATCH_ALL, $roles) ) {
                break;
            }

            // find match logged user, char: @
            if ( false !== array_search(self::MATCH_LOGGED, $roles) ) {
                # code...
            }

            // find match guest user, char: ?
            if ( false !== array_search(self::MATCH_LOGGED, $roles) ) {
                # code...
            }
        }

        // deny access
        if ( !$allow ) {
            return $this->response->withStatu(403);
        }

        // allow access
        return true;
    }
}
