<?php
namespace app\index\controller;

use think;
use think\Db;
use think\Route;

use think\Session;
use app\index\model\FilesModel;

class User extends think\Controller
{

    public function index()
    {
        $u_arr = session('admin');


        if($u_arr == null){

            return $this->fetch();
        }else{
            if($u_arr['type'] == 2)
            {
                $url = url('/file/join');
            }else{
                $url = url('/home');
            }

            return $this->redirect($url);
        }
    }
    public function login_do()
    {
        $data   = input('param.');
        $name   = $data['admin_name'];
        $pwd    = $data['admin_pwd'];

        $user_model = New UserModel();
        $arr        = $user_model->getUser($name,md5($pwd));
        if(empty($arr))
        {
            $this->error('账号或密码有误');
            $url = url('/index');
        }else{
            $session_arr    = array();
            $session_arr['id']      = $arr['id'];
            $session_arr['name']    = $arr['user_name'];
            $session_arr['type']    = $arr['type'];
            Session::set('admin',$session_arr);
            Session::set('user_name',$arr['user_name']);

            if($arr['type'] == 2)
            {
                $url = url('/file/join');
            }else{
                $url = url('/home');
            }

        }
        echo "<script>location.href='$url';</script>";
    }

    //首页
    public function home()
    {
        $uid = session('admin');
        if($uid == null){
            $url = url('/index');
            return $this->redirect($url);
        }
        //1在档 2借出
        $where = 'status in (1,2)';
        $dcl = Db::table('oa_students')->where('status',0)->count(); //0 未收取  查出记录条数
        $kc  = Db::table('oa_students')->where($where)->count(); //在档和借出的记录条数
        $bks = Db::table('oa_students')->where('file_number like "B%"')->count(); //研究生总人数
        $yjs = Db::table('oa_students')->where('file_number like "Y%"')->count(); //本科生总人数
        $this->assign('dcl',$dcl);
        $this->assign('kc',$kc);
        $this->assign('bks',$bks);
        $this->assign('yjs',$yjs);
        return $this->fetch('home');
    }

    //用户退出
    public function login_out()
    {
        Session::delete('admin');
        $admin = session('admin');
        if(!isset($admin))
        {
            $url = '/';
            echo "<script> location.href='$url' </script>";
        }else{
            $this->error();
        }
    }
}
