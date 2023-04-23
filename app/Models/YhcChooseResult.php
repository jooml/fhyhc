<?php

namespace App\Models;

use Illuminate\Support\Facades\App;

class YhcChooseResult extends BaseModel
{
    protected $table = 'yhc_choose_result';
    // 下面用于设置不允许入库字段，一般和$fillable存在一个即可
    protected $guarded  = [];

}
