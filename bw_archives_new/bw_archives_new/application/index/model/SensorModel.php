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


class SensorModel extends Model
{
    protected $table = 'oa_sensor';

    //获取传感器最后一条信息
    public function getInfoBySid($name,$ip)
    {
        return $this->where("sensor_name",$name)
            ->where("sensor_ip",$ip)
            ->find();
    }

    //更新传感器的信息
    public function updateDataBySid($data,$sid)
    {
        //$data['updated_at'] = date("Y-m-d H:i:s");
        return $this->where("id",$sid)->update($data);
    }

    //新建传感器
    public function createData($data)
    {
        #$data['created_at'] = date("Y-m-d H:i:s");
        # $data['updated_at'] = date("Y-m-d H:i:s");
        $this->save($data);
        return $this->getLastInsID();
    }

    //获取传感器列表
    public function getlist()
    {
        $list = $this->select();
        return $list;

//        $list = $this->alias('s')->where("s.student_id=".$id)
//            ->join(['oa_category'=>'oc'],'s.category_id = oc.id','LEFT')
//            ->field("s.*,oc.branch_id,oc.name AS ocname,oc.num ")
//            ->order("nian","DESC")
//            ->order("yue","DESC")
//            ->order("ri","DESC")
//            ->select  return $list;

    }
    // //删除
    public function deleteDoc($id){

        $result=$this->where('id',$id)->setField('delete_mark', '1');

        return $result;

    }
    public function getListAll($data,$type)
    {


        /*$list = $this->alias('s')->where("s.student_id",'in',"$id")
            ->join(['oa_category'=>'oc'],'s.category_id = oc.id','LEFT')
            ->field("s.*,oc.branch_id,oc.name AS ocname,oc.num ")
            ->group('s.category_id')
            ->order("nian","DESC")
            ->order("yue","DESC")
            ->order("ri","DESC")
            ->select();*/
        $list = $this->select();
        return $list;


    }


    public function getOne($id)
    {
        $list = $this->alias('s')->where("s.id=".$id)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->field("s.*,os.name AS osname,os.file_number")
            ->find();
        return $list;
    }


    public function getAllList($data,$type)
    {
        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];

        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;

        $where  = "os.type = ".$type ;

        if(isset($data['sc_number']) && $data['sc_number'] !="")
        {
            $where      .= "  and os.sc_number = '".$data['sc_number']."'";
            $wherec     = $where;
        }else{
            if (isset($data['file_number']) && $data['file_number'] != "") {
                $where .= "  and os.file_number = '" . $data['file_number'] . "'";
                $wherec = $where;
            } else {

                if (isset($data['grade']) && $data['grade'] > 0)
                    $where .= "  and os.grade = '" . $data['grade'] . "'";

                if (isset($data['department']) && $data['department'] > 0)
                    $where .= "  and os.depart = '" . $data['department'] . "'";

                if (isset($data['category']) && $data['category'] > 0)
                    $where .= "  and s.category_id = '" . $data['category'] . "'";


                if (isset($data['status']) && $data['status'] >= 0) {
                    $where .= "  and s.status = " . $data['status'];
                }
                if (isset($data['name']) && $data['name'] != "") {
                    $where .= "  and s.name like '%" . $data['name'] . "%'";
                }
                if (isset($data['number']) && $data['number'] != "") {
                    $where .= "  and s.number like '%" . $data['number'] . "%'";
                }
                $wherec = $where;
            }
        }

        $list = $this->alias('s')->where($where)->where('delete_mark','0')
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->join(['oa_category'=>'oc'],'s.category_id = oc.id','LEFT')
            ->field("s.*,oc.name AS ocname,os.sc_number AS sc_number,os.name AS osname,os.file_number")
            ->order('s.id','desc')
            ->limit($lmt)
            ->select();
        $arr = array();
        $arr['count'] = $this->alias('s')->where($wherec)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->count();
        $arr['content'] = $list;
        return $arr;

    }

    //更新文件状态
     public function getOneUpdate($data){
        $id=$data['sid'];
         $this->where('id',$id)->setField('status', '0');
         $this->where('student_id',$id)->setField('status', '0');

     }





    //根据获取最后一条数据
    public function getListBySid($studentid)
    {
        $list = $this->alias('s')->where("s.student_id",$studentid)
            ->join(['oa_category'=>'oc'],'s.category_id = oc.id','LEFT')
            ->field("s.*,oc.name AS ocname")
            ->order('s.category_id')
            ->order('s.id','desc')
            ->select();
        return $list;
    }
    //根据获取一条数据
    public function getInfoByid($id)
    {
        return $this->where("id",$id)->find();
    }

    //新根据获取一条数据建

    public function getInfoByIds($ids)
    {
        $data= $this->whereIn("id",$ids)->field('name,number')->select();//column('name','id');
        //$data= $this->whereIn("id",$ids)->value('name');
      return $data;
    }
    public function getBorrow($file_number){
        $borrow_type = $this->where('file_number',$file_number)->where('status = 2 or status = 3')->select();
        if ($borrow_type){
            return 2;
        }else{
            return 1;
        }
    }


    //--添加文件--
    //新建
    public function insertData($data)
    {
        //print_r($data);die;
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        //unset($data['date']);
        $this->saveAll($data);
        //return $this->getLastInsID();
    }
    //新建
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
    //新建
    public function updateDataByIds($data,$ids)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->whereIn("id",$ids)->update($data);
    }


    //更改状态
    public function setStatus($data,$ids)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->where("id","IN",$ids)->update($data);
    }

    //更改状态
    public function deleteData($id)
    {
        return $this->where("id","=",$id)->delete();
    }
}