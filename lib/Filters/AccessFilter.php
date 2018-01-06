<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;

use inhere\library\helpers\Arr;

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

                // 1. use defined mark char:
                //     '?' guest user
                //     '@' logged user
                //     '*' all user.
                // 2. use custom role name. like 'member', 'admin' (the role name is must be unique, and it is save on dabatase.)
                // 3. can also use role id: 12 43 (it is not recommend)
                // Notice: there are role relation is OR.
                'roles' => ['*'],

                // can add a callback, is optional. TODO ...
                'callback' => function ($action, $user) {}
            ],
            // more rule
            [ ... ],
            [ ... ],
        */
    ];

    /**
     * the role filed name in the user(\Slim::$app->user).
     * @var string
     */
    public $userRoleField = 'role';

    /**
     * on access denied
     * @var callable
     */
    public $onDenied;

    /**
     * {@inheritDoc}
     */
    protected function doFilter($action)
    {
        /** @var \inhere\libraryPlus\auth\User */
        $user = \Slim::$app->user;
        $allow = true;

        foreach ($this->rules as $rule) {
            // no limit
            if (
                !$rule
                || !($actions = (array)Arr::get($rule, 'actions'))
                || !($roles = (array)Arr::get($rule, 'roles'))
            ) {
                continue;
            }

            // don't match current action
            if (self::MATCH_ALL !== $actions[0] && !in_array($action, $actions, true)) {
                continue;
            }

            $allow = Arr::get($rule, 'allow', false);

            // find match all user, char: *
            if (in_array(self::MATCH_ALL, $roles, true)) {
                break;
            }

            // find match logged user, char: @
            if ($user->id && in_array(self::MATCH_LOGGED, $roles, true)) {
                break;
            }

            // find match guest user, char: ?
            if (!$user->id && in_array(self::MATCH_LOGGED, $roles, true)) {
                break;
            }

            // user role: string OR array. e.g 'admin' OR [ 'admin', 'editor']
            $userRoles = $user->{$this->userRoleField};

            if ($userRoles && array_intersect((array)$userRoles, $roles)) {
                break;
            }

            // use custom callback
            if (($cb = Arr::get($rule, 'callback')) && $cb($action, $user)) {
                break;
            }
        }

        // deny access
        if (!$allow && ($cb = $this->onDenied)) {
            return call_user_func($cb, $action, $user);
        }

        return (bool)$allow;
    }
}
