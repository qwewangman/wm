<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/1/22
 * Time: 10:18
 */


namespace app\index\model;

use think\Model;

class UserModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_admin';

    // 获取所有用户的所有数据
    public function getUser($name,$pwd)
    {
        $more_datas = $this->where("login_name",$name)->where('password',$pwd)->where('status',1)->find();          // 查询所有用户的所有字段资料
        if (empty($more_datas)) {                 // 判断是否出错
            return false;
        }
        return $more_datas;   // 返回修改后的数据
    }

    public function getUserName($id)
    {
        return $this->where("id",$id)->find();
    }

    //查询管理员
    public function getList()
    {
        return $this->where("status",1)->select();
    }
    //查询管理员
    public function getAllList()
    {
        return $this->select();
    }
    //新建
    public function createData($data)
    {
      //  $data['created_at'] = date("Y-m-d H:i:s");
     //   $data['updated_at'] = date("Y-m-d H:i:s");
        $this->save($data);
        return $this->getLastInsID();
    }
    //修改
    public function updateData($data,$id)
    {
        $data['updated_at'] = date("Y-m-d H:i:s");
        return $this->update($data,"id=".$id);
    }
}
