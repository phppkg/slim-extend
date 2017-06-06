<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/23
 * Time: 上午10:38
 */

namespace slimExt\filters;

use inhere\libraryPlus\auth\User;
use Slim;
use inhere\library\helpers\ArrHelper;

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
     * the role filed name in the user(Slim::$app->user).
     * @var string
     */
    public $userRoleField = 'role';

    /**
     * {@inheritDoc}
     */
    protected function doFilter($action)
    {
        /**
         * current user
         * @var User
         */
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
            if (false !== array_search(self::MATCH_ALL, $roles)) {
                break;
            }

            // find match logged user, char: @
            if (false !== array_search(self::MATCH_LOGGED, $roles) && $user->id) {
                break;
            }

            // find match guest user, char: ?
            if (false !== array_search(self::MATCH_LOGGED, $roles) && !$user->id) {
                break;
            }

            // user role: string OR array. e.g 'admin' OR [ 'admin', 'editor']
            $userRoles = $user->{$this->userRoleField};

            if ($userRoles && array_intersect((array)$userRoles, $roles)) {
                break;
            }
        }

        // deny access
        if (!$allow) {
            // when is xhr
            if ($this->request->isXhr()) {
                $data = ['redirect' => $user->loginUrl];

                return $this->response->withJson($data, __LINE__, slim_tl('http:403'), 403);
            }

            return $this->response->withRedirect($user->loginUrl, 403)->withMessage(slim_tl('http403'));
        }

        // allow access
        return true;
    }
}
