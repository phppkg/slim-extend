<?php

namespace slimExtend\base;

use Slim;

/**
 * Class RestFulController
 * @package slimExtend\base
 *
 * how to use. e.g:
 * ```
 * class Book extends slimExtend\base\RestFulController
 * {
 *     public function get($request, $response, $args)
 *     {}
 *     public function post($request, $response, $args)
 *     {}
 *     public function put($request, $response, $args)
 *     {}
 *     public function delete($request, $response, $args)
 *     {}
 *     ... ...
 * }
 * ```
 */
abstract class RestFulController
{
    /**
     * __construct
     */
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        /*
        Some init logic
        */
    }

    /**
     * @param array $data
     * @param array $required
     * @return null|string return error msg if has error.
     */
    protected function collectData(array &$data, array $required=[])
    {
        foreach ( $required as $key => $field ) {
            // 可以检查子级
            if ( is_array($field) ) {
                if ( !isset($data[$key]) ) {
                    return '缺少必要参数 ' . $key;
                }
                $subData = $data[$key];

                foreach ($field as $subField) {
                    if ( is_string($subField)
                        && (!isset($subData[$subField]) || $subData[$subField] === '')
                    ) {
                        return "缺少必要参数  {$key}[{$subField}]";
                    }
                }
            } else if (!isset($data[$field]) || $data[$field] === '') {
                return '缺少必要参数 ' . $field;
            }
        }

        return null;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     */
    protected function beforeInvoke(Request $request, Response $response, array $args)
    {
        return false;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     * @throws \RuntimeException
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        // Maybe want to do something
        if ( $result = $this->beforeInvoke($request, $response, $args)) {
            return $result;
        }

        // default restFul action name
        $action = strtolower($request->getMethod());

        if ( method_exists($this, $action) ) {
            return $this->$action($request, $response, $args);
        }

        // Might want to customize to perform the action name
        if ( $result = $this->afterInvoke($request, $response, $args)) {
            return $result;
        }

        throw new \RuntimeException('Error Processing Request');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return bool
     */
    protected function afterInvoke(Request $request, Response $response, array $args)
    {
        return false;
    }

}
