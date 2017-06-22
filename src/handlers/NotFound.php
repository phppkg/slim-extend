<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 16/8/31
 * Time: 上午1:31
 */

namespace slimExt\handlers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NotFound
 * @package slimExt\handlers
 */
class NotFound extends \Slim\Handlers\NotFound
{
    private $renderer;

    private $viewFile;

    /**
     * @var array
     */
    private $appendParams;

    public function __construct($file, array $renderer = [], array $appendParams = [])
    {
        $this->viewFile = $file;
        $this->renderer = $renderer;
        $this->appendParams = $appendParams;
    }

    protected function renderHtmlNotFoundOutput(ServerRequestInterface $request)
    {
        if (!$this->viewFile) {
            return parent::renderHtmlNotFoundOutput($request);
        }

        $this->appendParams['homeUrl'] = (string)$request->getUri()->withPath('')->withQuery('')->withFragment('');

        if (($renderer = $this->renderer) && is_object($renderer) && method_exists($renderer, 'fetch')) {
            return $renderer->fetch($this->viewFile, $this->appendParams);
        }

        return $this->internalRender($this->viewFile, $this->appendParams);
    }

    protected function internalRender($viewFile, array $data = [])
    {
        ob_start();
        $this->protectedIncludeScope($viewFile, $data);
        return ob_get_clean();
    }

    /**
     * @param string $template
     * @param array $data
     */
    protected function protectedIncludeScope($template, array $data)
    {
        extract($data, EXTR_OVERWRITE);
        include $template;
    }
}