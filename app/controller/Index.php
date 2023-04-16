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
use think\facade\Db;
use think\facade\Env;
use think\facade\View;
use think\facade\Session;
use think\facade\Request;

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

        //如果有进行搜索操作
        if(Session::get('search')){
            $postList = Session::get('search');
            $mid_page_title = '搜索结果';
            Session::set('search', null);
        }


        if($mid_page_title == '最新发布')
        {
            $postList = Artical::order('publish_time','desc')->paginate(5);
        }else if(strlen($mid_page_title) == 15)
        {
            $tag = substr($mid_page_title, 0, 6);
            $postList = Artical::where('category',$tag)
                                ->order('publish_time', 'desc')->paginate(5);
        }else if($mid_page_title == '收藏博文') {
            if (!$username) {
                return redirect('http://localhost:8000/login/index');
            } else {
                $postList = Artical::where('artical_id', 'IN', function ($q) {
                    $q->table('star')->where('user_id', User::Where('username', Session::get('username'))->find()->user_id)->field('text_id');
                })->paginate(5);
            }
        }else if($mid_page_title == '我赞博文')
        {
            if (!$username) {
                return redirect('http://localhost:8000/login/index');
            }else {
                $postList = Artical::where('artical_id', 'IN', function ($q) {
                    $q->table('dig')->where('user_id', User::Where('username', Session::get('username'))->find()->user_id)->field('text_id');
                })->paginate(5);
            }
        }else if($mid_page_title == '我评博文')
        {
            if (!$username) {
                return redirect('http://localhost:8000/login/index');
            }else {
                $postList = Artical::where('artical_id', 'IN', function ($q) {
                    $q->table('comment')->where('user_id', User::Where('username', Session::get('username'))->find()->user_id)->field('text_id');
                })->paginate(5);
            }
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

    public function search()
    {
        $text = trim(Request::post('search'));
        if(!strlen($text)){
            echo "<script>alert('搜索无效！');</script>";
            echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/index/index'>";
            return;
        }
        if(str_starts_with($text, 'user:') && strlen($text) >= 6){
            $text_trim = substr($text, 5);
            Session::set('re', $text_trim);
            $query = Artical::where('author_id', 'IN', function ($q) {
                $q->table('user')->where('username', 'like', '%'.Session::get('re').'%')->field('user_id');
            })->paginate(5);
        }else if(str_starts_with($text, 'title:') && strlen($text) >= 7){
            $text_trim = substr($text, 6);
            $query = Artical::where('title', 'like', '%'.$text_trim.'%')->paginate(5);
        }else if(str_starts_with($text, 'content:') && strlen($text) >= 9){
            $text_trim = substr($text, 8);
            $query = Artical::where('content', 'like', '%'.$text_trim.'%')->paginate(5);
        }else{
            $query = Artical::where('content', 'like', '%'.$text.'%')->paginate(5);
        }

        Session::set('search', $query);
        return redirect('http://localhost:8000/index/index');
    }
}
