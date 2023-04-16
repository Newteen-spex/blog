<?php

namespace app\controller;

use app\BaseController;
use app\model\Dig;
use app\model\Star;
use app\model\Subcribe;
use app\model\Comment;
use think\facade\Session;
use think\facade\View;
use think\facade\Request;
use app\model\User;
use app\model\Artical as ArticalModel;
use think\response\Redirect;

class Artical extends BaseController
{
    function index()
    {
        return View::fetch('artical');
    }

    //关注作者(文章主页)
    function subcribe($textAuthorId, $textId)
    {
        $username = Session::get('username');
        if($username){
            $userId = User::where('username', $username)->find()->user_id;
        }

        if(!$username)
        {
            return \redirect('http://localhost:8000/login/index');
        }else if($textAuthorId != $userId)
        {
            $subQuery = Subcribe::where('sub_id', $userId)->where('subed_id', $textAuthorId)->find();
            if(!$subQuery)
            {
                Subcribe::create([
                    'sub_id'    =>    $userId,
                    'subed_id'  =>    $textAuthorId,
                    'sub_time'  =>    date('y-m-d h:i:s')
                ]);
                echo "<script> alert('关注成功！'); </script>";
            }else {
                echo "<script> alert('您已关注该作者！'); </script>";
            }
        }else{
            echo "<script> alert('您不能关注你自己！'); </script>";
        }
        echo "<meta http-equiv='Refresh' content='0.5;URL=http://localhost:8000/artical/textLook/textId/".$textId."'>";
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

    //收藏文章
    function star($articalId)
    {
        if(!Session::get('username'))
        {
            return \redirect('http://localhost:8000/login/index');
        }else
        {
            //收藏文章涉及Artical表和Star表
            $text = ArticalModel::find($articalId);
            ++$text->star_count;
            $text->save();

            $userQuery = User::where('username', Session::get('username'))->find();
            $userId = $userQuery->user_id;
            Star::create([
                'user_id'       =>      $userId,
                'text_id'       =>      $articalId,
                'star_time'   =>     date('y-m-d h:i:s')
            ]);

            return;
        }
    }

    //点赞文章(主页点赞按钮处理)
    function digText($textId)
    {

        $username = Session::get('username');
        if(!$username)
        {
            echo "<script>alert('请先登录！');</script>";
            echo "<meta http-equiv='Refresh' content='0.5;URL=http://localhost:8000/login/index'>";
        }else
        {
            $userId = User::where('username', $username)->find()->user_id;
            //点赞之前要判断是否存在用户有点赞此篇文章的记录
            $digQuery = Dig::where('user_id', $userId)->where('text_id', $textId)->find();
            if(!$digQuery)
            {
                //收藏文章涉及Artical表和Star表
                $text = ArticalModel::find($textId);
                ++$text->dig_count;
                $text->save();

                Dig::create([
                    'user_id'       =>      $userId,
                    'text_id'       =>      $textId,
                    'dig_time'   =>     date('y-m-d h:i:s')
                ]);
                echo "<script> alert('点赞成功！'); </script>";
            }else
            {
                echo "<script> alert('您已点赞过该文章！'); </script>";
            }
            echo "<meta http-equiv='Refresh' content='0.1;URL=http://localhost:8000/index/index'>";
        }

    }

    //点赞文章（文章页点赞按钮处理）
    function digArtical($textId, $digFlag)
    {
        $username = Session::get('username');
        if(!$username)
        {
            return \redirect('http://localhost:8000/login/index');
        }else
        {
            $userId = User::where('username', $username)->find()->user_id;
            $digQuery = Dig::where('user_id', $userId)->where('text_id', $textId)->find();
            if($digFlag == '点赞')
            {
                $text = ArticalModel::find($textId);
                ++$text->dig_count;
                $text->save();

                Dig::create([
                    'user_id'       =>      $userId,
                    'text_id'       =>      $textId,
                    'dig_time'   =>     date('y-m-d h:i:s')
                ]);
                echo "<script> alert('点赞成功！'); </script>";
            }else if($digFlag == '取消点赞')
            {
                $digQuery->delete();

                $queryText = \app\model\Artical::find($textId);
                --$queryText->dig_count;
                $queryText->save();

                echo "<script> alert('已取消点赞！'); </script>";
            }
            echo "<meta http-equiv='Refresh' content='0.5;URL=http://localhost:8000/artical/textLook/textId/".$textId."'>";
        }
    }

    //发表评论
    function upComment($textId)
    {
        $username = Session::get('username');
        if(!$username){
            return \redirect('http://localhost:8000/login/index');
        }

        $userId = User::where('username', $username)->find()->user_id;
        $commentTime = date('y-m-d h:i:s');
        $content = Request::post('comment');

        //对评论内容修剪
        $content = trim($content);
        $content = htmlspecialchars($content);
        $content = str_replace("\n","<br>",$content);
        $content = str_replace(" ","&nbsp",$content);

        //修改数据库
        $textQuery = ArticalModel::find($textId);
        ++$textQuery->comment_count;
        $textQuery->save();

        Comment::create([
            'user_id'       =>      $userId,
            'text_id'       =>      $textId,
            'comment_time'  =>      $commentTime,
            'content'       =>      $content
        ]);
        echo  "<script> alert('评论发布成功！'); </script>";
        echo "<meta http-equiv='Refresh' content='0;URL=http://localhost:8000/artical/textLook/textId/".$textId."/tag/#commentList"."'>";
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


    function textLook($textId, $tag = '#pageHead', $star = false)
    {
        //如果文章还没开启时间追踪，则开启
        if(!Session::get('temp')){
            $arr = [];
            $arr = array_pad($arr, 1000, 0);
            Session::set('temp', $arr);
        }

        //检查文章是否为初次浏览
        if(Session::get('temp')[$textId] == 0){
            //浏览次数加一
            $look = ArticalModel::find($textId);
            ++$look->look_count;
            $look->save();
            $arrTemp = Session::get('temp');
            $arrTemp[$textId] = time();
            Session::set('temp', $arrTemp);
        }else{
            if(time() - (int)Session::get('temp')[$textId] > 15){
                //浏览次数加一
                $look = ArticalModel::find($textId);
                ++$look->look_count;
                $look->save();
                //更新
                $arrTemp = Session::get('temp');
                $arrTemp[$textId] = time();
                Session::set('temp', $arrTemp);
            }
        }

        //主要用于改变右上角样式
        if(Session::get('username'))
        {
            $username = Session::get('username');
            $queryUser = User::where('username',$username)->find();
            $userId = $queryUser->user_id;
        }else
        {
            $userId = -1;
        }

        //用于改变点赞按钮的样式
        $digQuery = Dig::where('user_id', $userId)->where('text_id', $textId)->find();
        if(!$digQuery){
            $digStatus = '点赞';
        }else{
            $digStatus = '取消点赞';
        }

        //点击收藏按钮判断
        if($star && !Session::get('username'))
        {
            return \redirect('http://localhost:8000/login/index');
        }else if($star && Session::get('username'))
        {
            //收藏之前要判断是否存在用户收藏此篇文章的记录
            $starQuery = Star::where('user_id', $userId)->where('text_id', $textId)->find();
            if(!$starQuery)
            {
                //收藏文章涉及Artical表和Star表
                $text = ArticalModel::find($textId);
                ++$text->star_count;
                $text->save();

                Star::create([
                    'user_id'       =>      $userId,
                    'text_id'       =>      $textId,
                    'star_time'   =>     date('y-m-d h:i:s')
                ]);
                echo "<script> alert('收藏成功！'); </script>";
            }else
            {
                echo "<script> alert('您已收藏该文章！'); </script>";
            }
        }

        $queryArtical = ArticalModel::find($textId);
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


        //查询文章关联的评论
        $commentQuery = Comment::where('text_id', $textId)->order('comment_time', 'desc')->select();
        foreach ($commentQuery as $key=>$obj){
            //增加属性
            $obj->userName = User::find($obj->user_id)->username;
        }

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
            'closeText'     =>          $closeText,
            'textId'        =>          $textId,
            'digStatus'     =>          $digStatus,
            'commentTable'  =>          $commentQuery
        ]);
    }
}