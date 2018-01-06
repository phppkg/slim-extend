<?php

namespace slimExt\database\helpers;

use slimExt\database\DbFactory;
use Windwalker\Query\QueryElement;
use Windwalker\Compare\Compare;
use Windwalker\Query\Query;

/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/3/30
 * Time: 10:41
 */
class QueryHelper
{
    /**
     * buildConditions
     *
     * @param Query $query
     * @param array $conditions
     *
     * @return  Query
     */
    public static function buildWheres(Query $query, array $conditions)
    {
        foreach ($conditions as $key => $value) {
            // NULL
            if ($value === null) {
                $query->where($query->format('%n = NULL', $key));
            } // If using Compare class, we convert it to string.
            elseif ($value instanceof Compare) {
                $query->where((string)static::buildCompare($key, $value, $query));
            } // If key is numeric, just send value to query where.
            elseif (is_numeric($key)) {
                $query->where($value);
            } // If is array or object, we use "IN" condition.
            elseif (is_array($value) || is_object($value)) {
                $value = array_map(array($query, 'quote'), (array)$value);
                $query->where($query->quoteName($key) . new QueryElement('IN ()', $value, ','));
            } // Otherwise, we use equal condition.
            else {
                $query->where($query->format('%n = %q', $key, $value));
            }
        }
        return $query;
    }

    /**
     * buildCompare
     *
     * @param string|int $key
     * @param Compare $value
     * @param Query $query
     *
     * @return  string
     */
    public static function buildCompare($key, Compare $value, $query = null)
    {
        $query = $query ?: DbFactory::getDbo()->newQuery(true);
        if (!is_numeric($key)) {
            $value->setCompare1($key);
        }
        $value->setHandler(
            function ($compare1, $compare2, $operator) use ($query) {
                return $query->format('%n ' . $operator . ' %q', $compare1, $compare2);
            }
        );
        return (string)$value;
    }

}
