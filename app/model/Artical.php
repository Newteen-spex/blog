<?php

namespace app\model;
use think\Model;

class Artical extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'artical_id'; //规定主键
    protected $table = 'artical'; //绑定数据表

    public function star()
    {
        return $this->hasMany(Star::class,'text_id');
    }

}