<?php
namespace app\controller;

use app\BaseController;
use app\model\Info;
use app\model\User;
use app\model\Artical;
use app\model\Comment;
use app\model\Dig;
use app\model\Star;
use app\model\Subcribe;
use think\facade\Env;
use think\facade\View;
use think\Request;
use think\facade\Session;

class Index extends BaseController
{
    public function index($mid_page_title = '最新发布', $login = '登录',
                          $split = '|', $register = '注册', $postList = array())
    {
        $username = Session::get('username');
        if($username)
        {
            $login = '';
            $split = '';
            $register = 'Hello,'.$username;
        }

        if($mid_page_title == '最新发布')
        {
            $postList = Artical::order('publish_time','desc')->paginate(5);
        }else if(strlen($mid_page_title) == 15)
        {
            $tag = substr($mid_page_title, 0, 6);
            $postList = Artical::where('category',$tag)
                                ->order('publish_time', 'desc')->paginate(5);
        }

        if(count($postList))
        {
            foreach($postList as $key=>$obj)
            {
                //使$obj的分类属性被用户名取代
                $obj->category = User::find($obj->author_id)->username;
            }
        }
        return View::fetch('index', [
            'mid_page_title'    =>      $mid_page_title,
            'login'             =>      $login,
            'split'             =>      $split,
            'register'          =>      $register,
            'postList'          =>      $postList
        ]);
    }
}
