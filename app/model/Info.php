<?php

namespace app\model;
use think\Model;

class Info extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'info_id'; //规定主键
    protected $table = 'info'; //绑定数据表
}