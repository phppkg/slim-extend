## Language

> [Return](index.md)

config the language translator.

```
// language
'language'       => [
    'lang'     => $config->get('language', 'en'),
    'basePath' => '@resources/languages',
    'defaultFile' => 'default',

    'langFiles' => [
        // file key => file path
        // if no file key, default use file name. e.g: app
        'app.yml',
        'contents.yml',
        'user.yml',
    ],
],
```

use language translator:

```
use Slim;

// If no file is specified it reads 'basePath + lang + defaut.yml'
$msg = Slim::$app->language->tran('key');
// can also 
$msg = Slim::$app->language->tl('key'); // tl() is alias method of the tran()

// ... in twig
{{ _globals.lang.tran('key') }} 
{{ _globals.lang.tran('app:key') }} // translate from 'app.yml'

```

more information

1. allow multi arguments. `tran(string $key , array [$arg1 , $arg2], string $default)`

example

```
 // on language config file
userNotFound: user [%s] don't exists!

 // on code
$msg = Slim::$app->language->tran('userNotFound', 'demo');
// $msg : user [demo] don't exists!
```

2. allow fetch other config file data

@example
```
// on default config file (e.g. `en/default.yml`)
userNotFound: user [%s] don't exists!

// on app config file (e.g. `en/app.yml`)
userNotFound: the app user [%s] don't exists!

// on code
// will fetch value at `en/default.yml`
$msg = Slim::$app->language->tran('userNotFound', 'demo');
//output $msg: user [demo] don't exists!

// will fetch value at `en/app.yml`
$msg = Slim::$app->language->tran('app:userNotFound', 'demo');
//output $msg: the app user [demo] don't exists!

```