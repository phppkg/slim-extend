## controller

> [Return](index.md)

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

use SlimExt\Base\Controller;

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
