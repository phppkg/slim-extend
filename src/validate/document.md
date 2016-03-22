## simple validator

### how to use

- Method 1: create a new class
    e.g.
```
<?php

    use slimExtend\validate\Validator;

    class PageRequest extends Validator
    {
        public function rules()
        {
            return [
                ['tagId,userId,freeTime', 'required', 'msg' => '{attr} is required!'],
                ['tagId', 'size', 'min'=>4, 'max'=>567], // 4<= tagId <=567
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
                ['tagId', 'number', 'when' => function($data) 
                {
                    return isset($data['status']) && $data['status'] > 2;
                }],
                ['userId', 'number', 'scene' => 'other' ],
                ['status', function($status)
                { 

                    if ( .... ) {
                        return true;
                    }
                    return false;
                }],
            ];
        }
        
        // define field attribute's translate.
        public function attrTrans()
        {
            return [
              'userId' => '用户Id',
            ];
        }
        
        // custom validator message
        public function messages()
        {
            return [
              'required' => '{attr} 是必填项。',
            ];
        }
    }
```

- Method 2: direct use
```
<?php
    use slimExtend\validate\Validator;

    class SomeClass 
    {
        public function demo()
        {
            $valid = Validator::make($_POST,[
                // add rule
                ['title', 'min', 'min' => 40],
                ['freeTime', 'number'],
            ])->validate();

            if ( $valid->fail() ) {
                return $valid->getErrors();
            }

            // 
            // some logic ... ...
        }
    }
```

### keywords 

- scene 

- when

### Existing validators 

validator | description | rule example
----------|-------------|------------
`int`   | validate int | ....
`number`    | validate number | ....
`bool`  | validate bool | ....
`float` | validate float | ....
`regexp`    | validate regexp | ....
`url`   | validate url | ....
`email` | validate email | ....
`ip`    | validate ip | ....
`required`  | validate required | ....
`length`    | validate length | ....
`minLength` | validate minLength | ....
`maxLength` | validate maxLength | ....
`size`  | validate size | ....
`min`   | validate min | ....
`max`   | validate max | ....
`in`    | validate in | ....
`string`    | validate string | ....
`array`   | validate is Array | ....
`callback`  | validate by custom callback | ....