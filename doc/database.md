## Database

> [Return](index.md)

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

$priValue = $db->insert( $tableName, $data);

//----- select -----

// use Query, get query by db
$query = $db->newQuery()
         ->select('*')
         ->from($tableName)
         ->limit(10)
         ->order('publishTime DESC');

// Now we can access database
$articles = $db->setQuery($query)->loadAll();

// use sql string
$articles = $db->setQuery('SELECT * FROM table WHERE ...')->loadAll();

```

> more method please see `src/database/AbstractDriver.php`

## Model

- create a model class

```
<?php

use Slim;
use slimExt\base\Model;
use slimExt\DataType;

/**
 * Class BaseModel
 * @package app\models
 *
 * @property int $createTime
 */
class TestModel extends Model
{    
    // must define 'columns()' method. define all columns of the table.
    public function columns()
    {
        return [
            'id'         => DataType::T_INT,
            'contentId'  => DataType::T_INT,
            'tagId'      => DataType::T_INT, // equal to 'tagId' => 'int'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        // default is current class name
        $className = lcfirst( basename( str_replace('\\', '/', get_called_class()) ) );

        // '@@' -- is table prefix placeholder
        // return '@@articles';
        // if no table prefix
        // return 'articles';

        return '@@' . $className;
    }

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return Slim::get('db');
    }
    
    // config column data validate rules
    public function rules()
    {
        return [
            ['title,status, user_id, type, published_at', 'required'],
            ['category_id', 'min', 'value' => 1 ],
            ['title', 'length', 'min' => 2, 'max' => 128 ],
            ['id', 'required', 'on' => 'update' ],
            ['digest, cover, description, keywords', 'string'],

            ['status', function($status)
            {
                return array_key_exists($status, [1,2,3]);
            }, 'msg' => 'param status is error!'],
            
            // check urlAlias 
            ['urlAlias', 'checkUrlAlias', 'when' => function($data)
            {
                return !empty($data['urlAlias']);
            }, 'msg' => 'url-alias is exists!'],


            ['created, updated, count_read', 'safe'],

            // relation table data, only validate(don't save).
            ['tags, user, category', 'isArray'],
            ['category_name', 'string'],
        ];
    }

    protected function checkUrlAlias($urlAlias) 
    {
        // some logic ..
        return true; // or false, make it is fail.
    }
}

```

## how to use

### basic method 

- `query($where)`

return a Query instance.

```
UserModel::query($where);
```


### find 

**method list**

- `findOne($where, $options = [])`
- `findAll($where, $options = [])`
- `findByPk($priValue, $options = [])`

#### some information

- `$where` condition parameter usage

```
$data = TestModel::findAll([
     'userId = 23',      // ==> '`userId` = 23'
     'publishTime > 0',  // ==> '`publishTime` > 0'
     'title' => 'test',  // value will auto add quote, equal to "title = 'test'"
     'id' => [4, 5, 56],   // ==> '`id` IN (4,5,56)'
     'id NOT IN' => [4, 5, 56], // ==> '`id` NOT IN (4,5,56)'

     // a closure
     function (Query $q) {
         $q->orWhere('a < 5', 'b > 6');
         $q->where( 'column = ' . $q->q($value) );
     }
]);
```

- `$options` parameter usage

```
$data = TestModel::findAll($where, [
    'indexKey' => 'id', // set the return data key column.
    'class' => 'assoc', // set the reurn data type, allow: 'assoc' 'model' 'array' '\\stdClass'
    
    // call Query instance's method
    'select' => 'mt.id,mt.name,mt.type, count(t1.id) as total',
    'leftJoin' => '@@contents as t1 ON mt.id = t1.categoryId',
    'group' => 't1.categoryId',
    // 'bind' => [
    //   ':title' => $kw
    //]
    // a closure
    // function ($query) { ... };
]);
```

- how to use

```
<?php

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
$data = TestModel::findAll($where,  [
    'select' => 'mt.id,mt.name,mt.type, count(t1.id) as total',
    'class' => 'assoc',
    'leftJoin' => '@@contents as t1 ON mt.id = t1.categoryId',
    'group' => 't1.categoryId',
    // 'bind' => [
    //   ':title' => $kw
    //]
    // a closure
    // function ($query) { ... };
]);
```


### insert  

```
$model = new TestModel($data);
// OR 
// $model = TestModel::load($data);

$model->insertTime = time();

// will auto validate field, return primary key.
if ( ! $priValue = $model->insert() ) {
    $errmsg = $model->hasError() ? $model->firstError() : 'insert failure!!';
}

....
```

### update 

```
$model = TestModel::findByPk($id);
$model->updateTime = time();
if ( !$model->update() ) {
    $errmsg = $model->hasError() ? $model->firstError() : 'update failure!!';
}
```

### delete

```
// delete by model

$model = TestModel::findByPk($id);
$model->delete();

// delete by primary key

TestModel::deleteByPk($id);
// multi primary key
TestModel::deleteByPk([$id, $id2, $id3]);

// delete by condition
TestModel::deleteBy([
    'name' => 'demo'
]);

```

```
// ---------- other --------

$query = TestModel::query(['contentId' => $contentId])
    ->select('mt.contentId, t1.id, t1.name')
    ->leftJoin(Tags::tableName() . ' AS t1 ON mt.tagId = t1.id');

$list = TestModel::setQuery($query)->loadAll();

```
