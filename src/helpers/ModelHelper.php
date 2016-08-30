<?php

namespace slimExt\helpers;

use inhere\librarys\exceptions\UnknownMethodException;
use Windwalker\Query\Query;

/**
 *
 */
class ModelHelper
{
    /**
     * apply Append Options
     * @param  array  $options
     * @param  Query  $query
     */
    public static function applyAppendOptions($options=[], Query $query)
    {
        foreach ($options as $method => $value) {
            // special method handle
            if ($method === 'bind' && is_array($value)) {
                foreach ($value as $key => $val) {
                    // ':name' => $name. simple bind
                    if (!is_numeric($key) && is_scalar($val)) {
                        $query->bind($key, $value[$key]);
                    // 下面的方式有问题， bind($key, &$value,...) 的第二个参数需要的是一个变量参考。
                    // $val is [':name', $name, \PDO::PARAM_STR, ...]. can be add more param
                    // } elseif ( is_array($val) ) {de($value[$key]);
                    //     call_user_func_array([$query, 'bind'], $value[$key]);
                    }
                }

                continue;
            } elseif ($value instanceof \Closure) {
                $value($query);
                continue;
            }

            if (!method_exists($query, $method)) {
                throw new UnknownMethodException('The class method ['.get_class($query). ":$method] don't exists!");
            }

            is_array($value) ? call_user_func_array([$query,$method], $value) : $query->$method($value);
        }
    }

    /**
     * @param mixed $wheres
     * @param \slimExt\base\Model $model the model class name
     * @param Query $query
     * @example
     * ```
     * ...
     * $result = XxModel::findAll([
     *      'userId = 23',
     *      'publishTime > 0',
     *      'title' => 'test', // equal to "title = 'test'"
     *      'status' => [3, 'or'], // equal to "OR status = 3"
     *      ['categoryId > 23', 'or'], // equal to "OR categoryId > 23"
     *      function ($query) { ... } // a closure
     * ]);
     *
     * ```
     * @return Query
     */
    public static function handleWhere($wheres, $model, Query $query = null)
    {
        $query = $query ?: $model::getQuery(true);

        /* e.g:
        a Closure
        function(Query $q) use ($value) {
            $q->where( 'column = ' . $q->q($value) );
        }
        */
        if (is_object($wheres) and $wheres instanceof \Closure) {
            $wheres($query);

            return $query;
        }

        if ( is_array($wheres) ) {
            $glue = 'AND';

            foreach ($wheres as $key => $where) {
                if (is_object($where) and $where instanceof \Closure) {
                    $where($query);
                    continue;

                // natural int key
                } else if ( is_int($key) ) {
                    // e.g: $where = [ "column = 'value'", 'OR' ];
                    if ( is_array($where) ) {
                        list($where, $glue) = $where;
                        $glue = in_array(strtoupper($glue), ['AND', 'OR']) ? strtoupper($glue) : 'AND';
//d($where, $glue, $query);
                $query->where($where, $glue);
//de($query);
                    }

                    // $where is string, e.g: $where = "column = 'value'"; go on ...

                // string key, $key is a column name
                } elseif ( is_string($key) ) {

                    // e.g: $where = [ 'value', 'OR' ];
                    if ( is_array($where) ) {
                        list($where, $glue) = $where;

                        $glue = in_array(strtoupper($glue), ['AND', 'OR']) ? strtoupper($glue) : 'AND';
                    }

                    // $where is a column value. e.g: $where = 'value'; go on ...

                    $where = $query->qn($key) . ' = ' . $query->q($where);
                }

                $query->where($where, $glue);
            }// end foreach

        } elseif ( $wheres && is_string($wheres) ) {
            $query->where($wheres);
        }

        return $query;
    }

}