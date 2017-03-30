<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\base;

use inhere\library\utils\JsonMessage;
use Slim;
use Slim\Http\Response as SlimResponse;

/**
 * extension Slim's Response class
 *
 * Class Response
 * @package slimExt\base
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
    public function withJson($data, $code = 0, $msg = '', $status = 200)
    {
        if ( $data instanceof JsonMessage) {
            return parent::withJson($data, $status, 0);
        }

        $data = format_messages($data, $code, $msg);

        return parent::withJson($data, $status, 0);
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
     * $res->withCookie(['name' => 'value']);
     * ```
     * @param array $data
     * @param array $params
     * @return static
     */
    public function withCookie(array $data, array $params = [])
    {
        cookie($data, null, $params);

        return $this;
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $url
     * @param int $status
     * @return SlimResponse
     */
    public function withRedirect($url, $status = 301)
    {
        return $this->withStatus($status)->withHeader('Location', (string)$url);
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
            'type'      => 'info', // info success primary warning danger
            'title'     => 'Info!',
            'msg'       => '',
            'closeBtn'  => true
        ];

        if ( is_string($msg) ) {
            $alert['msg'] = $msg;
        } elseif ( is_array($msg) ) {
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
     * @param  array  $data
     * @return self
     */
    public function withInput(array $data)
    {
        Slim::$app->flash->addMessage(Request::FLASH_OLD_INPUT_KEY, json_encode($data));
        Slim::$app->logger->info('dev-' . session_id(), $_SESSION);

        return $this;
    }
}
