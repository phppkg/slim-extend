# Document

## Database

- add a db to container

```
... After the instantiation application
$container = $app->getContainer();

// database operator
$container['db'] = function ($c) {
    /** @var $c Slim\Container */
    $options = $c->get('settings')['db'];
    return \slimExtend\database\DbFactory::getDbo('db', $options);
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
...
use Slim;
use slimExtend\base\Model;

/**
 * Class BaseModel
 * @package app\models
 *
 * @property int $createTime
 */
class TestModel extends Model
{
    protected $autoAddTime = true;

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
// ---------- find record --------
// one. $model default is object and instance of TestModel.
$model = TestModel::findOne(['name' => 'test']);
// $model = TestModel::findByPk($id);

// more, $list = TestModel[]; 
$list = TestModel::findAll([
            'name' => 'test',
        ], 'id,status,type', 'id');

// use query
$query = TestModel::query()->where("contentId = $contentId")->order('insertTime DESC');
$result = TestModel::setQuery($query)->loadAll();


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
if ( ! $model->update() ) {
    $errmsg = $model->hasError() ? $model->firstError() : 'update failure!!';
}

// ---------- delete record --------

// ---------- other --------

$query = TestModel::query(['contentId' => $contentId])
    ->select('mt.contentId, t1.id, t1.name')
    ->leftJoin(Tags::tableName() . ' AS t1 ON mt.tagId = t1.id');

$list = TestModel::setQuery($query)->loadAll();

```