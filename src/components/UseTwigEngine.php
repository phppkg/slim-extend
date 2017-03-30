<?php
/**
 * Created by Sublime.
 * User: inhere
 * Date: 17/1/13
 * Time: 下午3:12
 */

namespace slimExt\components;

use Slim;
use slimExt\AbstractController;
use slimExt\exceptions\NotFoundException;

/**
 * Class Controller
 * @package slimExt\components
 */
trait UseTwigEngine
{
    /**
     * twig tpl render
     * @param $view
     * @param array $args
     * @return Response|Slim\Http\Response
     */
    protected function renderTwig($view, array $args = [])
    {
        $response = $this->response;
        $settings = Slim::get('settings')['twigRenderer'];
        $view  = $this->getViewPath($view, $settings);

        // use twig render
        $twig = Slim::get('twigRenderer');

        $globalVar = $this->addTwigGlobalVar();
        list($globalKey, $globalVar) = $this->handleGlobalVar($settings, $globalVar);

        // add custom extension
        // $twig->addExtension(new \slimExt\twig\TwigExtension( $c['request'], $c['csrf'] ));
        $this->appendVarToView($args);

        // is pjax request
        if ( Slim::$app->request->isPjax() ) {

            // X-PJAX-URL:https://github.com/inhere/php-library
            // X-PJAX-Version: 23434
            /** @var Response $response */
            $response = $response
                            ->withHeader('X-PJAX-URL', (string)Slim::$app->request->getUri())
                            ->withHeader('X-PJAX-Version', Slim::config('pjax_version', '1.0'));

            $args[$globalKey] = $globalVar;
            $rendered = $twig->getEnvironment()->loadTemplate($view)->renderBlock($this->bodyBlock, $args);

            return $response->write($rendered);
        }

        // add tpl global var
        $twig->getEnvironment()->addGlobal($globalKey, $globalVar);

        // Fetch rendered template {@see \Slim\Views\Twig::fetch()}
        $rendered = $twig->fetch($view, $args);

        // return response
        return $response->write($rendered);
    }

    /**
     * @return array
     */
    protected function addTwigGlobalVar()
    {
        $globalVar = [
            'user'     => Slim::$app->user,
            'config'   => Slim::get('config'),
            'params'   => Slim::get('config')->get('params', []),
            'lang'     => Slim::get('language'),
            'messages' => Slim::$app->request->getMessage(),
        ];

        if ( $class = $this->tplHelperClass ) {
            $globalVar['helper'] = new $class;
        }

        return $globalVar;
    }

}
