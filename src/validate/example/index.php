<?php

spl_autoload_register(function($class)
{
    $file = __DIR__ . '/' . $class. '.php';

    if (is_file($file)) {
        include $file;
    }
});


$filedConfig = [
            // 用户id
            'userId' => [
                    'type'    => 'int',
                    'default' => 0
                ],
            // 擅长 标签id
            'tagId' => [
                    'type'    => 'int',
                    'default' => 0
                ],

            // 空闲日期
            'freeTime' =>  [
                    'type'    => 'int',
                    'default' => 0
                ],
            // 距离要求
            'distanceRequire' =>  [
                    'type'    => 'int',
                    'default' => 0
                ],
            // 备注
            'note'  =>  [
                    'type' => 'string',
                    'default' => ''
                ],
            // 插入时间
            'insertTime'  =>  [
                    'type' => 'int',
                    'default' => 0,
                ],
            // 插入时间
            'lastReadTime'  =>  [
                    'type' => 'int',
                    'default' => 0,
                ],
        ];

$_POST = [
    'userId' => 'sdfdffffffffff',
    'tagId' => '234535',
    // 'freeTime' => 'sdfdffffffffff',
    'distanceRequire' => 'sdfdffffffffff',
    'note' => 'sdfdffffffffff',
    'insertTime' => '',
    'lastReadTime' => 'sdfdffffffffff',
];

$rules = [
    ['tagId,userId,freeTime', 'required', 'msg' => '{field} is required!'],
    ['note', 'email'],
    ['tagId', 'size', 'min'=>4, 'max'=>567, 'msg' => '{field} must is big!'], // 4<= tagId <=567
    ['freeTime', 'size', 'min'=>4, 'max'=>567, 'msg' => '{field} must is big!'], // 4<= tagId <=567
    ['userId', function($value){ echo $value."\n"; return false;}, 'msg' => '{field} check filare!'],
];

/*
$model = new TestModel();
$ret = $model->load($_POST)->validate();
*/
$model = new DataModel($_POST,$rules);
$ret = $model->validate([], true);

// echo "<pre>";
var_dump($ret,
$model->firstError()
);


// echo "</pre>";