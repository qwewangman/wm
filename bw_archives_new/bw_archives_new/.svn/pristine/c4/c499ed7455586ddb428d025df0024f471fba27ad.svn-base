<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/1/22
 * Time: 10:18
 */


namespace app\index\model;

use think\Db;
use think\Model;

class ChangeModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_change';

    public function saveChange($result){
        $this->insert(['s_id'=>$result,'addtime'=>date('Y-m-d H:i:s'),'tys'=>5]);
        $finId = $this->getLastInsID();
        return $finId;
    }
    public function getChange_lj($id){  //查询该用户立卷的动态信息
        $info = $this->where('s_id',$id)->where('tys',5)->find();
        if (empty($info)){
            return false;
        }
        $student = Db::table('oa_students')->where('id',$id)->find();
        $info['student_name'] = $student['user_name'];
        return $info;
    }
    public function getChange_join($id){
        $info = $this->where('s_id',$id)->where('tys',6)->find();
        if (empty($info)){
            return false;
        }
        $student = Db::table('oa_students')->where('id',$id)->find();
        $info['student_name'] = $student['user_name'];
        return $info;
    }
    public function change_num($id){ //取某用户变动数量
        $num = $this->where('s_id',$id)->count();
        return $num;
    }
    public function change_type($tys){
        $res = Db::table('oa_change')->where('tys',$tys)->find();
        switch ($res['tys']){
            case 1: $res['tys'] = '迁出';break;
            case 2: $res['tys'] = '保留学籍';break;
            case 3: $res['tys'] = '降级';break;
            case 4: $res['tys'] = '院系变更';break;
            case 5: $res['tys'] = '立卷';break;
            case 6: $res['tys'] = '交接';break;
        }
        return $res['tys'];
    }
    public function getchange($data){
        $num = $data['limit'];
        if($data['page']<=0) $data['page']=1;
        $p = ($data['page']-1)*$num;
        $lmt = "$p,".$num;
        //把某学生的记录全部查出
        $result = [];
        $info = $this->where('s_id',$data['id'])->limit($lmt)->order('addtime desc')->select();
        $result['count'] = $this->where('s_id',$data['id'])->order('addtime desc')->count();
        foreach ($info as $k=>&$v){
            $v['tys'] = $this->change_type($v['tys']);
        }
        foreach ($info as $k=>$v){
            $student = Db::table('oa_students')->where('id',$data['id'])->find();
            $student['tys'] = $v['tys'];
            $student['addtime'] = $v['addtime'];
            $url = url('/record_details',['id'=>$v['id'],'tys'=>$v['tys']]);
            $student['url'] = "<a href='$url' class='layui-btn layui-btn-primary layui-btn-xs' lay-event='detail'>详情</a>";
            $result['content'][] = $student;
        }
        return $result;
    }
    //毕业迁出
    public function graduationMove($data)
    {
         $data['student_id']=$data['id'];
         unset($data['ctype']);
         $this->insert($data);
    }
}
