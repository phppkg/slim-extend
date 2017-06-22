<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2016/3/3 0003
 * Time: 23:05
 */

namespace slimExt\web;

/**
 * extension Slim's Request class
 * Class Request
 * @package slimExt\web
 *
 */
class Request extends \inhere\http\Request
{
    const FLASH_MSG_KEY = '_alert_messages';
    const FLASH_OLD_INPUT_KEY = '_last_inputs';

    /**
     * @return array
     */
    public function getMessage()
    {
        $messageList = [];
        $messages = \Slim::$app->flash->getMessage(self::FLASH_MSG_KEY) ?: [];

        foreach ($messages as $alert) {
            $messageList[] = json_decode($alert, true);
        }

        return $messageList;
    }

    /**
     * @param array $default
     * @return array
     */
    public function getOldInput(array $default = [])
    {
        if ($data = \Slim::$app->flash->getMessage(self::FLASH_OLD_INPUT_KEY)) {
            return json_decode($data[0], true);
        }

        return $default;
    }
}
