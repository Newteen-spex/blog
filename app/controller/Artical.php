<?php

namespace app\controller;

use app\BaseController;
use think\facade\Session;
use think\facade\View;
use think\facade\Request;
use app\model\User;
use app\model\Artical as ArticalModel;

class Artical extends BaseController
{
    function index()
    {
        return View::fetch('artical');
    }

    function upload($flag = false, $textId = null)
    {
        $title = Request::post('title');
        //对文章排版做些修改,方便模板输出时能够正确显示
        $content = Request::post('content');
        $content = trim($content);
        $content = htmlspecialchars($content);
        $content = str_replace("\n","<br>",$content);
        $content = str_replace(" ","&nbsp",$content);

        $category = Request::post('category');
        $username = Session::get('username');
        $query = User::where('username',$username)->find();
        if(!$flag){
            ArticalModel::create([
                'author_id'     =>      $query->user_id,
                'title'         =>      $title,
                'content'       =>      $content,
                'category'      =>      $category,
                'publish_time'   =>     date('y-m-d h:i:s')
            ]);
        }else{
            $text = ArticalModel::find($textId);
            $text->title = $title;
            $text->content = $content;
            $text->category = $category;
            $text->publish_time = date('y-m-d h:i:s');
            $text->save();
        }

        echo "<script>alert('文章发布成功！');</script>";
        echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/home/index'>";

    }

    //编辑文章
    function edit($textId)
    {
        $query = ArticalModel::where('artical_id',$textId)->find();
        $title = $query->title;
        $content = $query->content;
        $category = $query->category;

        //将文章排版变正常
        $content = str_replace("&nbsp"," ",$content);
        $content = str_replace("<br>","\n",$content);
        $content = htmlspecialchars_decode($content);

        return View::fetch('editUped', [
            'title'     =>      $title,
            'content'   =>      $content,
            'category'  =>      $category,
            'textId'    =>      $textId
        ]);
    }

    //删除文章
    function delete($textId)
    {
        ArticalModel::destroy($textId);
        echo "<script>alert('文章已删除！');</script>";
        echo "<meta http-equiv='Refresh' content='1;URL=http://localhost:8000/home/index'>";
    }


    function textLook($textId, $tag = '#pageHead')
    {
        if(Session::get('username'))
        {
            $username = Session::get('username');
            $queryUser = User::where('username',$username)->find();
            $userId = $queryUser->user_id;
        }else
        {
            $userId = -1;
        }
        $queryArtical = ArticalModel::where('artical_id',$textId)->find();
        $authorId = $queryArtical->author_id;
        $textTitle = $queryArtical->title;
        $authorName = User::find($authorId)->username;
        $textTime = $queryArtical->publish_time;
        $textContent = $queryArtical->content;
        $textTag = $queryArtical->category;
        $textDig = $queryArtical->dig_count;
        $textStar = $queryArtical->star_count;
        $textComment = $queryArtical->comment_count;
        $textScan = $queryArtical->look_count;

        //查询相关文章
        $closeText = ArticalModel::where('category',$textTag)->
                                   where('artical_id', '<>', $textId)->limit(5)->select();

        return View::fetch('artical', [
            'userId'        =>          $userId,
            'authorId'      =>          $authorId,
            'textTitle'     =>          $textTitle,
            'authorName'    =>          $authorName,
            'textTime'      =>          $textTime,
            'textContent'   =>          $textContent,
            'textTag'       =>          $textTag,
            'textDig'       =>          $textDig,
            'textStar'      =>          $textStar,
            'textComment'   =>          $textComment,
            'textScan'      =>          $textScan,
            'tag'           =>          $tag,
            'closeText'     =>          $closeText
        ]);
    }
}