<?php

namespace app\controller;

use app\BaseController;
use app\model\Star;
use app\model\Subcribe;
use think\db\Where;
use think\facade\View;
use think\facade\Session;
use app\model\User;
use app\model\Info;
use app\model\Artical;
use think\facade\Request;
use think\response\Redirect;

class Home extends BaseController
{
    function index ($nav_alt = '欢迎回来, [用户名]', $logout = '', $userName = '[Username]',
                    $email = '[Email]', $idNumber = '[id_number]', $school = '[School]',
                    $major = '[Major]', $selfIntro = '[self_intro]')
    {
        $username = Session::get('username');
        if(!$username){
            return redirect('http://localhost:8000/login/index');
        }
        else{
            $query = User::Where('username',$username)->find();
            $email = $query->email;
            $idNumber = $query->student_id;

            $infoQuery = $query->info()->find();
            if($infoQuery){
                $school = $infoQuery->school;
                $major = $infoQuery->major;
                $selfIntro = $infoQuery->introduce;
            }

            $textList = $query->artical()->order('publish_time','desc')->select();
            $starList1 = $query->star()->order('star_time', 'desc')->select();
            $starList2 = array();
            foreach($starList1 as $key=>$obj)
            {
                $query = Artical::find($obj->text_id);
                Array_push($starList2, $query);
            }

            //我的关注列表
            $mySubcribes = User::where('user_id', 'IN', function ($q){
                    $q->table('subcribe')->where('sub_id', User::where('username', Session::get('username'))->find()->user_id)->field('subed_id');
            })->select();

            //我的粉丝列表
            $myFollowers = User::where('user_id', 'IN', function ($q){
                $q->table('subcribe')->where('subed_id', User::where('username', Session::get('username'))->find()->user_id)->field('sub_id');
            })->select();


            $nav_alt = '欢迎回来, '.$username;
            $logout = '退出登录';
            $userName = $username;
            $avatar = $username.'.jpg';
        }
        return View::fetch('home',[
            'avatar'        =>      $avatar,
            'nav_alt'       =>      $nav_alt,
            'logout'        =>      $logout,
            'userName'      =>      $userName,
            'email'         =>      $email,
            'idNumber'      =>      $idNumber,
            'school'        =>      $school,
            'major'         =>      $major,
            'selfIntro'     =>      $selfIntro,
            'textList'      =>      $textList,
            'starList'      =>      $starList2,
            'mySubcribes'   =>      $mySubcribes,
            'myFollowers'   =>      $myFollowers

        ]);
    }

    //取消关注
    function unSubcribe($idolId)
    {
        $username = Session::get('username');
        $userId = User::where('username', $username)->find()->user_id;
        $query = Subcribe::where('sub_id', $userId)->where('subed_id', $idolId);
        $query->delete();
        echo "<script> alert('已成功取消关注！'); </script>";
        echo "<meta http-equiv='Refresh' content='0.5;URL=http://localhost:8000/home/index'>";
    }

    //取消收藏
    function unStar($textId)
    {
        $username = Session::get('username');
        $userId = User::where('username', $username)->find()->user_id;
        $query = Star::where('user_id', $userId)
                        ->where('text_id', $textId)->find();
        $query->delete();

        $queryText = Artical::find($textId);
        --$queryText->star_count;
        $queryText->save();

        echo "<script> alert('已取消收藏！'); </script>";
        echo "<meta http-equiv='Refresh' content='0.5;URL=http://localhost:8000/home/index'>";
    }

    function editAvatar()
    {
        $username = Session::get('username');
        $file = request()->file('edit_avatar');
        try{
            validate(['image'=>'fileSize:300000|fileExt:jpg,png,gif'])->check(['image'=>$file]);
            $saveFile = \think\facade\Filesystem::disk('public')->putFileAs('images', $file, $username.'.jpg');
            echo "<script> alert('头像更改成功！'); </script>";
        }catch (\think\exception\ValidateException $e)
        {
            echo $e->getMessage().'，请选择以jpg,png,gif为后缀的大小在300KB以内的图片文件';

        }
        echo "<meta http-equiv='Refresh' content='3;URL=http://localhost:8000/home/index'>";
    } 


    function others($authorName)
    {
        Session::set('othersName', $authorName);
        if($authorName == Session::get('username'))
        {
            return redirect('http://localhost:8000/home/index');
        }else{
            $query = User::where('username',$authorName)->find();
            $authorStudentId = $query->student_id;
            $authorRegisterTime = $query->create_time;
            $infoQuery = $query->info()->find();
            if($infoQuery){
                $authorIntroduce = $infoQuery->introduce;
            }else{
                $authorIntroduce = '该用户暂无个人介绍';
            }
            $articalList = $query->artical()->order('publish_time', 'desc')->select();

            //关注列表
            $subcribes = User::where('user_id', 'IN', function ($q){
                $q->table('subcribe')->where('sub_id', User::where('username', Session::get('othersName'))->find()->user_id)->field('subed_id');
            })->select();

            //粉丝列表
            $followers = User::where('user_id', 'IN', function ($q){
                $q->table('subcribe')->where('subed_id', User::where('username', Session::get('othersName'))->find()->user_id)->field('sub_id');
            })->select();

            return View::fetch('others', [
                'authorName'       =>       $authorName,
                'authorStudentId'  =>       $authorStudentId,
                'authorRegisterTime'    =>  $authorRegisterTime,
                'authorIntroduce'  =>       $authorIntroduce,
                'articalList'      =>       $articalList,
                'subcribes'        =>       $subcribes,
                'followers'        =>       $followers
            ]);
        }

    }

    function logout()
    {
        sleep(1);
        return redirect('http://localhost:8000/index/index')->with('username', null);
    }

    function updateInfo()
    {
        $username = Session::get('username');

        $email = Request::post('email');
        $idNumber = Request::post('id_number');
        $school = Request::post('school');
        $major = Request::post('major');
        $selfIntro = Request::post('self_intro');
        $query = User::where('username',$username)->find();
        $infoQuery = $query->info()->find();
        if(!$infoQuery)
        {
            Info::create([
                'user_id'       =>      $query->user_id,
                'school'        =>      $school,
                'introduce'     =>      $selfIntro,
                'major'         =>      $major
            ]);
        }else{
            $query->info()->update([
                'school'        =>      $school,
                'introduce'     =>      $selfIntro,
                'major'         =>      $major
            ]);
        }
        User::where('username',$username)->update([
                'email'         =>      $email,
                'student_id'    =>      $idNumber
        ]);
        return redirect('http://localhost:8000/home/index');
    }
}