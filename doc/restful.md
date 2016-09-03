# RESTFUL

> [Return](index.md)

## reference yii2

- GET /users: 逐页列出所有用户；
- HEAD /users: 显示用户列表的概要信息；
- POST /users: 创建一个新用户；
- GET /users/123: 返回用户为123的详细信息;
- HEAD /users/123: 显示用户 123 的概述信息;
- PATCH /users/123 and PUT /users/123: 更新用户123;
- DELETE /users/123: 删除用户123;
- OPTIONS /users: 显示关于末端 /users 支持的动词;
- OPTIONS /users/123: 显示有关末端 /users/123 支持的动词。

## controller 

if use controller layer. the controller class is must be extends the `slimExt\rest\Controller`

```
namespace app\controllers;

use app\models\Tags;
use Slim;
use slimExt\rest\Controller;

/**
 * Class Test
 * @package app\controllers
 */
class Test extends Controller
{
    /**
     * @return Slim\Http\Response|static
     */
    public function getsAction()
    {
        $keyword = $this->request->getTrimmed('keywords','');

        $list = Tags::searchByName($keyword);

        return $this->response->withJson(['list' => $list]);
    }

    public function getAction($id)
    {
        // can also return array. it will be translate to json.
        return [
            'test' => 'data',
            'id' => $id
        ];
    }
}
```