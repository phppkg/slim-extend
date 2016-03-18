<?php
/**
 *
 */
namespace slimExtend\helpers;


use slimExtend\exceptions\NotFoundException;

class Helper
{
    /**
     * 获得目录下的文件，可选择类型、是否遍历子文件夹
     * @param string $path 目标目录
     * @param int|bool $readDir 是否包含目录信息
     * @param string|array $ext array('css','html','php') 'css|html|php'
     * @param string $basePath
     * @param array $list
     * @return array
     * @throws NotFoundException
     */
    public static function dirFiles($path, $readDir=false, $ext=null, $basePath = '', &$list=[])
    {
        if (!is_dir($path)) {
            throw new NotFoundException('目录'.$path.' 不存在！');
        }

        $ext = is_array($ext) ? implode('|',$ext) : trim($ext);
        static $id = 0;

        //glob()寻找与模式匹配的文件路径
        foreach( glob($path.'/*', GLOB_NOSORT) as $item) {
            $id++;

            // directory
            if ( is_dir($item) && $readDir){
                $list[$id]['id']   = $id;
                $list[$id]['isFolder']  = true;
                $list[$id]['name'] = basename($item);

                if ( $basePath ) {
                    $list[$id]['path'] = str_replace($basePath, '', $item);
                } else {
                    $list[$id]['path'] = $item;
                }

                $list[$id]['realpath'] = $item;

            // file 如果没有传入$ext 则全部遍历，传入了则按传入的类型来查找
            } elseif ( !$ext || preg_match("/\.($ext)$/i",$item)) {
                $list[$id]  = static::getFileInfo($item, $basePath, false); //文件的上次访问时间
                $list[$id]['isFolder']  = false;
            }
        }

        return $list;
    }

    public static function getFileInfo($file, $basePath='', $check=true)
    {
        if ( $check && !file_exists($file)) {
            throw new NotFoundException("文件 {$file} 不存在！");
        }

        return [
            'name'            => basename($file), //文件名
            'path'            => $basePath ? str_replace($basePath, '', $file) : $file, // 文件相对路径
            'realpath'        => $file, // 文件路径
            'type'            => filetype($file), //类型
            'size'            => ( filesize($file)/1000 ).' Kb', //大小
            'is_write'        => is_writable($file) ? 'true' : 'false', //可写
            'is_read'         => is_readable($file) ? 'true' : 'false',//可读
            'update_time'     => filectime($file), //修改时间
            'last_visit_time' => fileatime($file), //文件的上次访问时间
        ];

    }
}