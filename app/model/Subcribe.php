<?php

namespace app\model;
use think\Model;

class Subcribe extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'subcribe_id'; //规定主键
    protected $table = 'subcribe'; //绑定数据表
}