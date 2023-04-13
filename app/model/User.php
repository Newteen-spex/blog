<?php

namespace app\model;

use think\Model;
//根据数据库中的表来创建一个相应的类，方便之后直接在控制器中使用
class User extends Model
{
    protected $connection = 'mysql';//将模型和数据库连接绑定，对应database.php中的设置
    protected $pk = 'user_id'; //规定主键
    protected $table = 'user'; //绑定数据表

    public function info()
    {
        return $this->hasOne(Info::class, 'user_id');
    }

    public function artical()
    {
        return $this->hasMany(Artical::class,'author_id');
    }

}