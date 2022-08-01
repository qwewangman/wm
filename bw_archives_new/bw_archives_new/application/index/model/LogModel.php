<?php
/**
 * Created by PhpStorm.
 * User: 25352
 * Date: 2018/1/22
 * Time: 10:18
 */


namespace app\index\model;

use think\Model;

class LogModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_action_log';

    //新建
    public function createData($data)
    {
       # $data['created_at'] = date("Y-m-d H:i:s");
        $this->save($data);
        return $this->getLastInsID();
    }
    //新建传感器
    public function createAllData($data)
    {
        return $this->saveAll($data);
    }

    //
    public function getList($stu_id)
    {

        $list = $this->where("stu_id=".$stu_id)
            ->order('id','desc')
            ->select();

        return $list;

    }

}
