<?php
/**
 * Created by SlimExt Generator.
 * User: {@author}
 * Date: {@date}
 * Time: {@time}
 * Gen by command:
 *
 * ```shell
 * {@fullCommand}
 * ```
 */

namespace {@namespace};

use {@parentClass};

/**
 * Class {@className}
 * @package {@namespace}
 *{@properties}
 */
class {@className} extends {@parentName}
{
    /**
     * define some default value
     * @var array
     */
    protected $data = [{@defaultData}
    ];

    /**
     * define the table name
     * @var string
     */
    public static function tableName()
    {
        return '{@table}';
    }

    /**
     * define fields of the model
     * @return array
     */
    public function columns()
    {
        return [{@columns}
        ];
    }

    /**
     * define fields validate rules
     * @return array
     */
    public function rules()
    {
        return [{@rules}
        ];
    }

    public function translates()
    {
        return [{@translates}
        ];
    }

    protected function beforeInsert()
    {
        // do something before insert
    }

    protected function beforeUpdate()
    {
        // do something before update
    }

    protected function afterSave()
    {
        // do something after insert/update
    }
}
