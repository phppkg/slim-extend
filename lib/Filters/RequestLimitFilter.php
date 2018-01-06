<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/27
 * Time: 下午10:41
 */

namespace SlimExt\Filters;

/**
 * Class RequestLimitFilter
 * - 请求限制过滤
 * @package SlimExt\Filters
 */
class RequestLimitFilter extends BaseFilter
{
    const DEFAULT_ERROR = 'request too frequently!';

    /**
     * @var array
     */
    const DEFAULT_RULE = [
        // check condition
        'condition' => 'ip', // ip, phone, userId, custom(e.g token) ...
        // date, day of the week
        'date' => '',
        // Effective time period of the day. e.g 0-2, 13-34
        'period' => '',
        // interval seconds. default one second
        'interval' => 1,
        // Every {interval} to allow the number of times
        // if equal to 0, don't allow access
        'times' => 100,
        // error message
        'error' => self::DEFAULT_ERROR,
    ];

    /**
     * like redis
     * @var mixed
     */
    public $driver;

    /**
     * @var array[]
     */
    public $rules = [
        '*' => [
            [
                // check condition
                'condition' => 'ip', // ip, phone, userId, custom(e.g token) ...
                // date, day of the week
                'date' => '',
                // Effective time period of the day. e.g 0-2, 13-34
                'period' => '',
                // interval seconds. default one second
                'interval' => 1,
                // Every {interval} to allow the number of times
                'times' => 100,
                //
                'error' => '',
            ]
        ],
//        'action name' => [
//
//        ]
    ];

    /**
     * how to get controller instance? use `\Slim::get('controller')`
     * @param string $action
     * @return mixed
     *
     * Return:
     *     bool     True is allow access, False is Deny
     *     string   Deny, is the error message
     *     Response Deny, A Response instance
     */
    protected function doFilter($action)
    {
        if ($rules = $this->rules[$action]) {
            foreach ($rules as $rule) {
                $this->validateRule($action, $rule);
            }
        }

        if ($rules = $this->rules['*']) {
            foreach ($rules as $rule) {
                $this->validateRule($action, $rule);
            }
        }

        return true;
    }

    /**
     * @param string $action
     * @param array $rule
     */
    protected function validateRule($action, array $rule)
    {

    }

    protected function getRequestIp()
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    protected function getUserId()
    {
        return \Slim::$app->user->id;
    }
}
