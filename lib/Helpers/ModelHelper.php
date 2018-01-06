<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/6/18
 * Time: 下午11:17
 */

namespace SlimExt\Helpers;

use inhere\exceptions\UnknownMethodException;
use SlimExt\Base\RecordModel;
use Windwalker\Query\Query;

/**
 * Class ModelHelper
 * @package SlimExt\Helpers
 */
class ModelHelper
{
    /**
     * apply Append query Options
     * @see self::$defaultOptions
     * @param  array $options
     * @param  Query $query
     * @return Query
     * @throws UnknownMethodException
     */
    public static function applyQueryOptions(array $options = [], Query $query)
    {
        foreach ($options as $method => $value) {
            if ($value instanceof \Closure) {
                $value($query);
                continue;
            }

            if (!method_exists($query, $method)) {
                throw new UnknownMethodException('The class method [' . get_class($query) . ":$method] don't exists!");
            }

            is_array($value) ? call_user_func_array([$query, $method], $value) : $query->$method($value);
        }

        return $query;
    }

    /**
     * handle where condition
     * @param array|string|\Closure $wheres
     * @param RecordModel|string $model the model class name, is a string
     * @param Query $query
     * @example
     * ```
     * ...
     * $result = UserModel::findAll([
     *      'userId = 23',      // ==> '`userId` = 23'
     *      'publishTime > 0',  // ==> '`publishTime` > 0'
     *      'title' => 'test',  // value will auto add quote, equal to "title = 'test'"
     *      'id' => [4,5,56],   // ==> '`id` IN ('4','5','56')'
     *      'id NOT IN' => [4,5,56], // ==> '`id` NOT IN ('4','5','56')'
     *
     *      // a closure
     *      function (Query $q) {
     *          $q->orWhere('a < 5', 'b > 6');
     *          $q->where( 'column = ' . $q->q($value) );
     *      }
     * ]);
     *
     * ```
     * @return Query
     */
    public static function handleConditions($wheres, $model, Query $query = null)
    {
        /** @var Query $query */
        $query = $query ?: $model::getQuery(true);

        if (is_object($wheres) && $wheres instanceof \Closure) {
            $wheres($query);

            return $query;
        }

        if (is_array($wheres)) {
            foreach ((array)$wheres as $key => $where) {
                if (is_object($where) && $where instanceof \Closure) {
                    $where($query);
                    continue;
                }

                $key = trim($key);

                // string key: $key contain a column name, $where is column value
                if ($key && !is_numeric($key)) {

                    // is a 'in|not in' statement. eg: $where link [2,3,5] ['foo', 'bar', 'baz']
                    if (is_array($where) || is_object($where)) {
                        $value = array_map(array($query, 'quote'), (array)$where);

                        // check $key exists keyword 'in|not in|IN|NOT IN'
                        $where = $key . (1 === preg_match('/ in$/i', $key) ? '' : ' IN') . ' (' . implode(',', $value) . ')';
                    } else {
                        // check exists operator '<' '>' '<=' '>=' '!='
                        $where = $key . (1 === preg_match('/[<>=]/', $key) ? ' ' : ' = ') . $query->q($where);
                    }
                }

                // have table name
                // eg: 'mt.field', 'mt.field >='
                if (strpos($where, '.') > 1) {
                    $where = preg_replace('/^(\w+)\.(\w+)(.*)$/', '`$1`.`$2`$3', $where);
                    // eg: 'field >='
                } elseif (strpos($where, ' ') > 1) {
                    $where = preg_replace('/^(\w+)(.*)$/', '`$1`$2', $where);
                }

                $query->where($where);
            }// end foreach

        } elseif ($wheres && is_string($wheres)) {
            $query->where($wheres);
        }

        return $query;
    }
}
