<?php

namespace app\model;
use think\Model;

class Star extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'star_id'; //规定主键
    protected $table = 'star'; //绑定数据表
}