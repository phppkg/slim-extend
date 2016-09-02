<?php

namespace slimExt\helpers;

use inhere\librarys\exceptions\UnknownMethodException;
use Windwalker\Query\Query;

/**
 * Class ModelHelper
 * @package slimExt\helpers
 */
class ModelHelper
{
    /**
     * apply Append Options
     * @param  array $options
     * @param  Query $query
     * @return Query
     */
    public static function applyAppendOptions($options=[], Query $query)
    {
        foreach ($options as $method => $value) {
            if ($value instanceof \Closure) {
                $value($query);
                continue;
            }

            if (!method_exists($query, $method)) {
                throw new UnknownMethodException('The class method ['.get_class($query). ":$method] don't exists!");
            }

            is_array($value) ? call_user_func_array([$query,$method], $value) : $query->$method($value);
        }

        return $query;
    }

    /**
     * @param mixed $wheres
     * @param \slimExt\base\RecordModel|string $model the model class name, is a string
     * @param Query $query
     * @example
     * ```
     * ...
     * $result = XxModel::findAll([
     *      'userId = 23',
     *      'publishTime > 0',
     *      'title' => 'test', // equal to "title = 'test'"
     *      function ($query) { ... } // a closure
     *
     *      // or where by a closure
     *      function ($query) {
     *          $query->orWhere('a < 5', 'b > 6');
     *      }
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
            foreach ($wheres as $key => $where) {
                if (is_object($where) and $where instanceof \Closure) {
                    $where($query);
                    continue;
                // string key, $key is a column name
                } elseif ( is_string($key) && !is_numeric($key) ) {

                    // $where is a column value. e.g: $where = 'value'; go on ...

                    $where = $query->qn($key) . ' = ' . $query->q($where);
                }

                $query->where($where);
            }// end foreach

        } elseif ( $wheres && is_string($wheres) ) {
            $query->where($wheres);
        }

        return $query;
    }

}