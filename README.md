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
- RESTFul Controller
- Model layer, database query builder.
- Tracy Errors Handler(by whoops)

## Install

- Use composer

edit `composer.json`

_require_ add

```
"inhere/slim-extend": "dev-master",
```

run: `composer update`

## Usage

### generate class

support: model, command, controller

how to use:

```shell
php bin/app gen
php bin/app gen:model -h
```

- a model

```shell
php bin/console gen:model name=rolePermission table=role_permission type=db fileds="id,int;name,string,名称;priority,int,级别;permissions,string,权限"
```

more see [Document](doc/index.md)
