# slim-extend

## Description

## Install

- use composer

edit `composer.json`

_require-dev_ add

```
"inhere/slim-extend": "dev-master",
```

_repositories_ add 

```
"repositories": [
        {
            "type": "git",
            "url": "https://git.oschina.net/inhere/slim-extend"
        }
    ]
```

run: `composer update`

## Usage

- add the middleware into slim application

```
$app->add(new \inhere\extend\middleware\WhoopsTool());
```

## Options

- Opening referenced files with your favorite editor or IDE

```
$app = new App([
    'settings' => [
        'debug'         => true,
        'extend.editor' => 'sublime' // Support click to open editor
    ]
]);
```

>>>>>>> init project
