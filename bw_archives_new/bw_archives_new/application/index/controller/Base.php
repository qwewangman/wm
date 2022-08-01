<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/5/14
 * Time: 10:08
 */

namespace app\index\controller;

use think\Controller;
use think\Jump;

class Base extends Controller{

    public $_type   = ['','B','Y'];
    // 接口结果
    protected $result = array(
        'code' => 200,
        'msg'  => '',
    );
    public function _initialize(){
        $uid    = session('admin');
        $type   = session('type');

        if($type <= 0)
        {
            session("type",1);
        }
        if($uid == null){
            $url = url('/index');
            return $this->redirect($url);
        }
    }

    /**
     * output
     */
    protected function output($result = null)
    {
        if (empty($result)) {
            $result = $this->result;
        }
        echo json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    //生产 -1-卷号 1-入档号  3-迁档号 4-换挡 5-借阅号
    public function createStuNumber($year,$prenum,$tp=1)
    {
        $type   = session('type');
        if($type <= 0)
        {
            $type = 1;
        }
        $num    = $prenum +1;
        if($num <10)
        {
            $num    = "00".$num;
        }elseif($num>=10 && $num <100){
            $num    = "0".$num;
        }

        $tp_arr     = $this->_type;
        $tname      = $tp_arr[$type];
        switch ($tp)
        {
            case -1:
                return $tname.$year."-".$num;
                break;
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
                return $tp.$year.$num;
                break;
        }
    }

    //生产件号
    public function createFileNumber($scnum,$prenum)
    {

        $num    = $prenum +1;
        if($num <10)
        {
            $num    = "00".$num;
        }elseif($num>=10 && $num <100){
            $num    = "0".$num;
        }
        return $scnum."-".$num;
    }

}