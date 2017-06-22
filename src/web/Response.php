<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\web;

use inhere\slimExt\components\JsonMessage;
use Slim;
use Slim\Http\Response as SlimResponse;

/**
 * extension Slim's Response class
 *
 * Class Response
 * @package slimExt\web
 */
class Response extends SlimResponse
{
    /**
     * @param mixed $data
     * @param int $code
     * @param string $msg
     * @param int $status
     * @return SlimResponse
     */
    public function withJson($data, $code = null, $msg = '', $status = 200)
    {
        $code = null === $code ? 0 : (int)$code;

        if ($data instanceof JsonMessage) {
            return parent::withJson($data, $status);
        }

        $data = format_messages($data, $code, $msg);

        return parent::withJson($data, $status);
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param int $encodingOptions
     * @return SlimResponse
     */
    public function withRawJson($data, $status = 200, $encodingOptions = 0)
    {
        return parent::withJson($data, $status, $encodingOptions);
    }

    /**
     * set cookie
     * ```
     * $res->withCookie('name', 'value');
     * ```
     * @param $name
     * @param $value
     * @return static
     */
    public function withCookie($name, $value)
    {
        setcookie($name, $value);

        return $this;
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $url
     * @param int $status
     * @return static
     */
    public function withRedirect($url, $status = null)
    {
        return $this->withStatus($status ?: 301)->withHeader('Location', (string)$url);
    }

    /**
     * Flash messages.
     *
     * Note: This method is not part of the PSR-7 standard.
     *
     * This method prepares the response object to return an HTTP Json
     * response to the client.
     *
     * @param  string|array $msg The msg
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function withMessage($msg)
    {
        // add a new alert message
        $alert = [
            'type' => 'info', // info success primary warning danger
            'title' => 'Info!',
            'msg' => '',
            'closeBtn' => true
        ];

        if (is_string($msg)) {
            $alert['msg'] = $msg;
        } elseif (is_array($msg)) {
            $alert = array_merge($alert, $msg);
            $alert['title'] = ucfirst($alert['type']);
        } else {
            throw new \InvalidArgumentException('params type error!');
        }

        Slim::$app->flash->addMessage(Request::FLASH_MSG_KEY, json_encode($alert));

        return $this;
    }

    /**
     * withInputs
     * @param  array $data
     * @return self
     */
    public function withInput(array $data)
    {
        Slim::$app->flash->addMessage(Request::FLASH_OLD_INPUT_KEY, json_encode($data));
        Slim::$app->logger->info('dev-' . session_id(), $_SESSION);

        return $this;
    }
}
