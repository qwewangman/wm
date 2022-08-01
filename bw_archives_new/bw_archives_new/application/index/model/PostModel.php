<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/1/22
 * Time: 10:18
 */


namespace app\index\model;

use think\Model;

class PostModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_post';



    //查询管理员
    public function getList()
    {
        return $this->where("status",1)->select();
    }
    //修改
    public function getOne($id)
    {
        return $this->where("id",$id)->find();
    }
    //删除地址
    public function deleteDoc($id){
        $result=$this->where('id',$id)->setField('delete_status', '1');

        return $result;
    }
    //查询管理员
    public function getAllList($data)
    {
        $num    = $data['limit'];
        $page   = $data['page']<=0?1:$data['page'];

        $p      = ($page-1)*$num;
        $lmt    = $p.",".$num;
        $where='';
        if (isset($data['province']) && $data['province'] != "") {
            $where .= "   province like '%" . $data['province'] . "%'";
            $wherec = $where;
        } else{
            $wherec='';
        }

        $list = $this->order("id","")->where($where)->limit($lmt)->where('delete_status',0)->select();

        $arr = array();
        $arr['count'] = $this->where($wherec)->where('delete_status',0)->count();
        //$arr['count'] = $this->count();
        $arr['content'] = $list;
        return $arr;
    }
    //新建
    public function createData($data)
    {
        $data['created_at'] = date("Y-m-d H:i:s");
        $data['updated_at'] = date("Y-m-d H:i:s");
        $this->save($data);
        return $this->getLastInsID();
    }

    //修改数据
    public function updateDateAll($data,$id){

        $data['updated_at'] = date("Y-m-d H:i:s");
        $this->update($data,"id=".$id);

    }
    //批量添加邮编地址
    public function createDataAll($data)
    {

        $this->saveAll($data);
        return $this->getLastInsID();
    }
    //修改
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
}
