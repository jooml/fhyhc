<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BaseModel extends Model
{
    protected $dateFormat = 'U';
    public $timestamps = false;

    //操作类型
    const ACT_ADD = 'add';
    const ACT_EDIT = 'edit';
    const ACT_EDIT_STATUS = 'edit_status';
    const ACT_DEL = 'del';
    const ACT_TRANSFER = 'transfer';
    const ACT_RECOVER = 'recover';
    const ACT_NAME = [
        self::ACT_ADD => '添加',
        self::ACT_EDIT => '修改',
        self::ACT_EDIT_STATUS => '修改状态',
        self::ACT_DEL => '删除',
        self::ACT_TRANSFER => '商品转移',
        self::ACT_RECOVER => '从回收站恢复',
    ];

    //状态类型
    const STATUS_ENABLE = 1;  //开启
    const STATUS_DISABLE = 2;  //关闭

    /**
     * 获取表名
     *
     * @return mixed
     */
    public static function getTableName()
    {
        return with(new static)->getTable();
    }


}
