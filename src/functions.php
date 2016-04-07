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
    if ($key &&  is_string($key) ) {
        return \Slim::config()->get($key,$default);
    }

    // set, when $key is array
    if ($key && is_array($key) ) {
        \Slim::config()->loadArray($key);
    }

    return $default;
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

function html_minify(\Slim\Http\Response $res, $body)
{
    $search = array('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '/\n/','/\>[^\S ]+/s','/[^\S ]+\</s','/(\s)+/s');
    $replace = array(' ', ' ','>','<','\\1');
    $squeezedHTML = preg_replace($search, $replace, $body);

    return $res->getBody()->write($squeezedHTML);
}

/**
 * cookie get or set
 * @param  string|array $name
 * @param  mixed $default
 * @param array $params
 * @return mixed
 */
function cookie($name, $default=null, array $params = [])
{
    // set, when $name is array
    if ($name && is_array($name) ) {
        $p = array_merge([
            'expire'   => null,
            'path'     => null,
            'domain'   => null,
            'secure'   => null,
            'httponly' => null
        ],$params);

        foreach ($name as $key => $value) {
            if ($key && $value && is_string($key) && is_scalar($value)) {
                $_COOKIE[$key] = $value;
                setcookie($key, $value, $p['expire'], $p['path'], $p['domain'], $p['secure'], $p['httponly']);
            }
        }

        return $name;
    }

    // get
    if ($name && is_string($name)) {
        return isset($_COOKIE[$name]) ? $_SESSION[$name] : $default;
    }

    return $default;
}

/**
 * session get or set
 * @param  string|array $name
 * @param  mixed $default
 * @return mixed
 */
function session($name, $default=null)
{
    if (!isset($_SESSION)) {
        throw new \RuntimeException('session set or get failed. Session don\'t start.');
    }

    // set, when $name is array
    if ($name && is_array($name) ) {
        foreach ($name as $key => $value) {
            if (is_string($key)) {
                $_SESSION[$key] = $value;
            }
        }

        return $name;
    }

    // get
    if ($name && is_string($name)) {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
    }

    return $default;
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
 * @return slimExtend\base\Response
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
