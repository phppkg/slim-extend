# Document

## Language

```
use Slim;

$msg = Slim::$app->language->tran('key');
// can also 
$msg = Slim::$app->language->tl('key'); // tl() is alias method of the tran()

// ... in twig
{{ _global.lang.tran('key') }} 
{{ _global.lang.tran('otherFile:key') }} // translate from 'otherFile'

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

2. allow fetch other config file data, when use multifile. (`Language::$type === static::USE_MULTIFILE`)

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

## Database

- add a db to container

```
... After the instantiation application
$container = $app->getContainer();

// database operator
$container['db'] = function ($c) {
    /** @var $c Slim\Container */
    $options = $c->get('settings')['db'];
    return \slimExt\database\DbFactory::getDbo('db', $options);
};
```

```
// The 'db' is key of the container.
$db = Slim::$app->db; // or Slim::get('db'); 

//----- insert -----

$priValue = $db->insert( $tableName, $this->all());

//----- select -----

// get query by db
$query = $db->newQuery()
         ->select('*')
         ->from($tableName)
         ->limit(10)
         ->order('publishTime DESC');

// Now we can access database
$articles = $db->setQuery($query)->loadAll();

```

more method please see `src/database/AbstractDriver.php`

## Model

- create a model class

```
<?php

use Slim;
use slimExt\base\Model;
use slimExt\DataConst;

/**
 * Class BaseModel
 * @package app\models
 *
 * @property int $createTime
 */
class TestModel extends Model
{
    protected $autoAddTime = true;
    
    // must define 'columns()' method. define all columns of the table.
    public function columns()
    {
        return [
            'id'         => DataConst::TYPE_INT,
            'contentId'  => DataConst::TYPE_INT,
            'tagId'      => DataConst::TYPE_INT,
        ];
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Slim::get('db');
    }

    protected function beforeInsert()
    {
        $this->autoAddTime && $this->createTime = time();
    }
}

```

- how to use

```
<?php

// ---------- find record --------
// one. $model default is object and instance of TestModel.
$model = TestModel::findOne(['name' => 'test']);
// $model = TestModel::findByPk($id);

// more, $list = TestModel[]; 
$list = TestModel::findAll([
            'name' => 'test',
        ], 'id,status,type', [ 'indexKey' => 'id' ]);

// use query
$query = TestModel::query()->where("contentId = $contentId")->order('insertTime DESC');
$result = TestModel::setQuery($query)->loadAll();

// 
$query = TestModel::query($where)
            ->select('mt.id,mt.name,mt.type, count(t1.id) as total')
            ->leftJoin('@@contents as t1 ON mt.id = t1.categoryId')
            ->group('t1.categoryId');
$data = TestModel::setQuery($query)->loadAssocList();

// euqals to 
$data = TestModel::findAll($where, 'mt.id,mt.name,mt.type, count(t1.id) as total', [
    'class' => 'assoc',
    'leftJoin' => '@@contents as t1 ON mt.id = t1.categoryId',
    'group' => 't1.categoryId',
]);

// ---------- insert record --------

$model = new TestModel($data);
// OR 
// $model = TestModel::load($data);

$model->insertTime = time();

if ( ! $priValue = $model->insert() ) {
    $errmsg = $model->hasError() ? $model->firstError() : 'insert failure!!';
}

....

// ---------- update record --------
$model = TestModel::findByPk($id);
$model->updateTime = time();
if ( !$model->update() ) {
    $errmsg = $model->hasError() ? $model->firstError() : 'update failure!!';
}

// ---------- delete record --------

// ---------- other --------

$query = TestModel::query(['contentId' => $contentId])
    ->select('mt.contentId, t1.id, t1.name')
    ->leftJoin(Tags::tableName() . ' AS t1 ON mt.tagId = t1.id');

$list = TestModel::setQuery($query)->loadAll();

```