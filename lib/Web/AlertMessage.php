<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/9/2
 * Time: 上午11:49
 */

namespace SlimExt\Web;

/**
 * Class AlertMessage
 * @package SlimExt\Web
 */
final class AlertMessage
{
    // info success primary warning danger
    const INFO = 'info';
    const SUCCESS = 'success';
    const PRIMARY = 'primary';
    const WARNING = 'warning';
    const DANGER = 'danger';

    /**
     * @var string
     */
    public $type = 'info';

    /**
     * @var string
     */
    public $title = 'Notice!';

    /**
     * @var string
     */
    public $msg = '';

    /**
     * @var array
     */
    public $closeBtn = true;

    /**
     * AlertMessage constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $name => $value) {
            if (method_exists($this, $name)) {
                $this->$name($value);
            } elseif (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;
        $this->title = ucfirst($type) . '!';

        return $this;
    }

    /**
     * @param $msg
     * @return $this
     */
    public function msg($msg)
    {
        $this->msg = $msg;

        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return array
     */
    public function all()
    {
        // add a new alert message
        return [
            'type' => $this->type ?: 'info', // info success primary warning danger
            'title' => $this->title ?: 'Info!',
            'msg' => $this->msg,
            'closeBtn' => (bool)$this->closeBtn
        ];
    }

    public function toArray()
    {
        return $this->all();
    }
}
