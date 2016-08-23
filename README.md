# slim-extend

## Description

 slim 3 extend to MVC structure.

Some Feature :

- Global config, Environment support
- Language support
- Command support
- Twig template engine
- Flash Messages
- Monolog
- Pimple Dependency Injection Container
- Controller layer for MVC
- Model layer, database query builder.
- Tracy Errors Handler(by whoops)

## Install

- Use composer

edit `composer.json`

_require_ add

```
"inhere/slim-extend": "dev-master",
```

_repositories_ add 

```
"repositories": [
        {
            "type": "git",
            "url": "https://github.com/inhere/slim-extend"
        }
    ]
```

run: `composer update`

## Usage

- add routes

```
// fixed action
$app->any('/register',  controllers\admin\Register::class . ':index');
$app->post('/register-success',  controllers\admin\Register::class . ':success');
$app->any('/forget-passwd',  controllers\admin\Register::class . ':forgetPasswd');
$app->post('/reset-passwd',  controllers\admin\Register::class . ':forgetPasswd');

// match action
$app->any('/profile[/{action:[a-zA-Z-]+}]', controllers\Profile::class)
    ->setName('profile')
    ->add(AuthCheck::class);
$app->any('/tags[/{action}]', controllers\Tag::class)
    ->setName('tags')
    ->add(AuthCheck::class);
```

**Notice: use match action, route method must is 'any'(`$app->any()`)**

- controllers

```
<?php

namespace app\controllers;

use slimExt\base\Controller;

/**
 * Class Home
 * @package app
 */
class Home extends Controller
{
    public function indexAction($args)
    {
        $this->render('index', [
            'param1' => 'xxx'
        ]);
        //$this->renderTwig('index', [
        //  'param1' => 'xxx'
        //]);
    }
}
```

more see [Document](document.md)