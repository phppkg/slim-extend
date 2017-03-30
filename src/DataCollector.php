<?php
/**
 * @referee  windwalker-registry {@link https://github.com/ventoviro/windwalker-registry}
 */

namespace slimExt;

use RuntimeException;
use slimExt\exceptions\ParseDataException;

/**
 * Class DataCollector - 数据收集器 (数据存储器 - DataStorage)
 * @package slimExt
 * 支持 链式的子节点 设置 和 值获取
 * e.g:
 * ```
 * $data = [
 *      'foo' => [
 *          'bar' => [
 *              'yoo' => 'value'
 *          ]
 *       ]
 * ];
 * $config = new DataCollector();
 * $config->get('foo.bar.yoo')` equals to $data['foo']['bar']['yoo'];
 *
 * ```
 *
 * 简单的数据对象可使用Slim内置的 Collection @see \Slim\Collection
 * ```
 * $config = new \Slim\Collection($data)
 * $config->get('foo');
 * ```
 */
class DataCollector extends \inhere\library\collections\DataCollector
{
    /**
     * name
     * @var string
     */
    protected $name = 'slim-application';

}
