<?php
/**
 * @author inhere
 * @desc some helper function
 */

/**
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function config($key,$default=null)
{
    return \Slim::config($key,$default);
}

function slimExt_tl($key, $args = [], $default = 'No translate.')
{
    return \Slim::get('language')->tl($key, $args, $default);
}
function slimExt_tran($key, $args = [], $default = 'No translate.')
{
    return \Slim::get('language')->tl($key, $args, $default);
}

function is_loc_env()
{
    return defined('RUNTIME_ENV') && RUNTIME_ENV === 'loc';
}

function is_dev_env()
{
    return defined('RUNTIME_ENV') && RUNTIME_ENV === 'dev';
}

function is_pdt_env()
{
    return defined('RUNTIME_ENV') && RUNTIME_ENV === 'pdt';
}

/**
 * page alert messages
 * @param mixed $msg
 * @return array|bool
 */
function alert_messages($msg='')
{
    // get all alert message
    if ( !$msg ) {
        return Slim::$app->request->getMessage();
    }

    // add a new alert message
    Slim::$app->response->withMessage($msg);

    return true;
}


/**
 * xhr 响应的数据格式
 * @param array|int $data 输出的数据
 * @param int $code 状态码
 * @param string $msg
 * @param string $describe
 * @return array
 * @throws \RuntimeException
 */
function format_messages($data, $code = 0, $msg = '', $describe = '')
{
    $jsonData = [
        'code'     => 0,
        'msg'      => '请求响应成功！',
        'describe' => '',
    ];

    // format_messages(int $code, string $msg , string $describe)
    if ( is_numeric($data) ) {
        $info = [
            'code'     => (int)$data,
            'msg'      => $code,
            'describe' => $msg,
        ];
        $data = [];
    } else {
        $info = [
            'code'     => (int)$code,
            'msg'      => $msg,
            'describe' => $describe,
        ];
    }

    $jsonData = array_merge($jsonData, $info);

    // data is not empty
    if ($data) {
        $jsonData['data'] = $data;
    }

    // no describe
    if ($jsonData['describe']) {
        unset($jsonData['describe']);
    }

    return $jsonData;
}

/**
 * suggest use : `$response->withRedirect($url, 301);`
 * @param $url
 * @param int $status
 * @return slimExt\base\Response
 */
function redirect_to($url='/', $status=301)
{
    return Slim::get('response')->withStatus($status)->withHeader('Location', $url);
}

/**
 * @param $file
 * @return mixed
 */
function get_extension($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}
