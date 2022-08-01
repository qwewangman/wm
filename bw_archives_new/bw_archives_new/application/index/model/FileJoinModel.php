<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/5/14
 * Time: 13:41
 */

namespace app\index\model;

use think\Model;
use think\Db;


class FileJoinModel extends Model
{
    protected $table = 'oa_park';
    //查询单条
    public function getFileStudentId($student_id){
        $arr = $this->where('student_id',$student_id)->find();
        return $arr;
    }
    // 添加
    public function addStudentJoin($data)
    {
        $this->insert($data);
        $finId = $this->getLastInsID();
        return $finId;
    }

    //获取学生列表
    public function getStudentsList($data){
        $where['f.status'] = $data['type'];
        if(empty($data['sc_number']))
            $data['sc_number'] = '';
        
        $list = Db::table('oa_file_join')->alias('f')->where($where)
            ->join(['oa_students'=>'s'],'s.id = f.student_id','LEFT')
            ->field("f.id,s.id AS stu_id,s.name,s.sex,s.sc_number,f.status,f.join_date")
            ->order('f.join_date','desc')
            ->select();

        $arr = array();
        $arr['content'] = $list;
        return $arr;
    }
    //附中  删除
        public function delStudentOne($data){

            return  $this->where('student_id',$data['id'])->delete();
        }

    //获取已提交数量
    public  function  getCount($st=2)
    {
        $info = $this->where("status",$st)->count();

        return $info;
    }
    //获取已提交列表
    public  function  getList()
    {
        $list = $this->select();
        return $list;
    }


    //更改状态
    public function setReceive($data,$ids)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['receive_time'] = date("Y-m-d H:i:s");
        return $this->where("id","IN",$ids)->update($data);
    }


    //修改in
    public function updateJoinByIds($save_arr,$ids)
    {

        $save_arr['join_date']     = date("Y-m-d");
        $save_arr['updated_at']     = date("Y-m-d H:i:s");
        return $this->where("id","IN",$ids)->update($save_arr);
    }
}