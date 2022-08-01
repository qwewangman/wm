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

class EmigrationModel extends Model
{
    // 控制器历史数据表
    protected $table = 'oa_history';

    public function getList($data){
        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];
        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;
        $where  = '' ;
        //------------------------------
        if(isset($data['sc_number']) && $data['sc_number'] !="")
        {
            $where .= " createTime like '%" . $data['sc_number'] . "%'";
            $wherec     = $where;
        }else{
            if (isset($data['file_number']) && $data['file_number'] != "") {
                $where .= "  and file_number = '" . $data['file_number'] . "'";
                $wherec = $where;
            } else {

                if (isset($data['name']) && $data['name'] != "") {
                    $where .= "ip = '" . $data['name'] . "'";;
                }

                $wherec = $where;
            }
        }

        //---------------------------------
        $list = $this->where($where)
            ->limit($lmt)
            ->select();
        $arr = array();
        $arr['count'] = $this->where($wherec)
            ->count();

        $arr['content'] = $list;
        return $arr;

    }

    //折线图的数据
    public function dataInfo(){

//        $where  = '' ;
//
//            if (isset($data['beginDate'])&&$data['beginDate'] != "") {
//                $where .= " createTime like '%" . $data['beginDate'] . "%' ";
//            }
//
//            if (isset($data['endDate'])&&$data['endDate'] != "") {
//                $where .= " and createTime like '%" . $data['endDate'] . "%' ";
//            }
        $list = $this->select();
        foreach($list as $v){
            $v['createTime']  = date("Y-m-d" ,strtotime($v['createTime']) );
        }
        return $list;
    }

    //新建
    public function createData($data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        $data['student_id']=$data['id'];
        $this->save($data);
        return $this->getLastInsID();
    }
    //根据年级获取最后一条数据
    public function getInfoByGrade($grade)
    {
        return $this->where("nian",$grade)->order("id","DESC")->find();
    }

    //新建
    /*public function getList($data,$type)
    {

        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];
        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;
        $where  = "os.type = ".$type ;
        //------------------------------
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

                if (isset($data['category']) && $data['category'] > 0)
                    $where .= "  and s.category_id = '" . $data['category'] . "'";


                if (isset($data['status']) && $data['status'] >= 0) {
                    $where .= "  and s.status = " . $data['status'];
                }
                if (isset($data['file_derice']) && $data['file_derice'] != "") {
                    $where .= "  and os.card = " . $data['file_derice'];
                }
                if (isset($data['name']) && $data['name'] != "") {
                    $where .= "  and os.name like '%" . $data['name'] . "%'";
                }
                $wherec = $where;
            }
        }

        //---------------------------------
        $list = $this->alias('s')->where($where)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','INNER')
            ->field("s.*,os.name AS name,os.file_number,os.major AS major,os.depart AS depart,os.sc_number AS sc_number,os.grade AS grade")
            ->order('s.id','desc')
            ->limit($lmt)
            ->select();
        $arr = array();
        $arr['count'] = $this->alias('s')->where($wherec)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','LEFT')
            ->count();
        //$arr['count'] = $this->count();
        $arr['content'] = $list;
        return $arr;
    }*/
    //查询个人迁出相关信息
    public function getOne($id){
        $list = $this->alias('s')->where('s.student_id',$id)
            ->join(['oa_students'=>'os'],'s.student_id = os.id','INNER')
            ->join(['oa_change'=>'oc'],'s.student_id = oc.student_id','INNER')
            ->field("s.*,os.name AS name,os.file_number,os.major AS major,os.depart AS depart,os.sc_number AS sc_number,os.grade AS grade,oc.batch")
            ->find();
         return $list;
    }
    //新建
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
}
