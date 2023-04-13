<?php

namespace app\model;
use think\Model;

class Comment extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'comment_id'; //规定主键
    protected $table = 'comment'; //绑定数据表
}