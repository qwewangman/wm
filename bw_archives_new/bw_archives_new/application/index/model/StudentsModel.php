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



class StudentsModel extends Model
{
    // 设置当前模型对应的完整数据表名称
    protected $table = 'oa_students';

    // 添加
    public function saveStudent($data)
    {
        $data['addtime'] = date('Y-m-d H:i:s');
        $this->insert($data);
        $finId = $this->getLastInsID();
        return $finId;
    }
    //全卷借阅的修改
    public function updateStatus($id){
        return  $this->where('id',$id)->setField('status', '0');
    }
    //导入数据
    public function addStudent($data)
    {
        foreach($data as $v)
        {
            $this->insert($v);
        }
        $finId = $this->getLastInsID();
        return $finId;
    }
    //查询学生id
    public function selectStudnetId($sc_number){
        $result=$this->field('id')->where('sc_number',$sc_number)->find();
        return $result;

    }
    //未在档
    public function getWzdList($data,$where)
    {
        $num = $data['limit'];
        if($data['page']<=0) $data['page']=1;
        $p = ($data['page']-1)*$num;
        $lmt = "$p,".$num;


        $arr = array();
        $arr['count'] = $this->where($where)->field('id,file_number,grade,courtyard,major,class,sc_number,teacher,user_name,status')->select();
        $arr['content'] = $this->where($where)->field('id,file_number,grade,courtyard,major,class,sc_number,teacher,user_name,status')->order('id desc')->limit($lmt)->select();
        return $arr;
    }
    //查询单条
    public function getFileId($id){
        $arr['content'] = $this->where("id",$id)->select();
        return $arr;
    }
    public function sta_type($id){
        $sta = $this->where('id',$id)->field('status')->find();
        switch ($sta['status']){
            case 0 : $sta['status'] = '未在档';break;
            case 1 : $sta['status'] = '在档';break;
            case 2 : $sta['status'] = '借出';break;
            case 3 : $sta['status'] = '迁出';break;
            case 4 : $sta['status'] = '退档';break;
            case 5 : $sta['status'] = '在档-转研究生';break;
            case 6 : $sta['status'] = '在档-毕业暂存';break;
            case 7 : $sta['status'] = '在档-村官暂存';break;
            case 8 : $sta['status'] = '借出-室内借阅';break;
            case 9 : $sta['status'] = '借出-在线借阅';break;
        }
        return $sta['status'];
    }
    public function stu_type($id){
        $sta = $this->where('id',$id)->field('student_type')->find();
        switch ($sta['student_type']){
            case 0 : $sta['student_type'] = '舞蹈';break;
            case 1 : $sta['student_type'] = '舞蹈学';break;
            case 2 : $sta['student_type'] = '学术学位';break;
            case 3 : $sta['student_type'] = '专业学位';break;
        }
        return $sta['student_type'];
    }

    public function stu_status($id){
        $sta = $this->where('id',$id)->where('status',0)->find();
        if (!empty($sta)){
            return  0;
        }
        return 1;
    }

    public function getStudent_sel($id){ //查询单条
        return $this->where('id',$id)->find();
    }
    public function getStudent_upd($id,$class,$grade){ //年级班级变动
        return $this->where('id',$id)->update(['class'=>$class,'grade'=>$grade]);
    }
    public function upd_courtyard($id,$courtyard,$major){ //院系专业变动
        return $this->where('id',$id)->update(['courtyard'=>$courtyard,'major'=>$major]);
    }

    //北舞附中搜索
    public function getSearchStudent($data){ //查询单条-学号
        $where='';
        if(isset($data['sc_number']) && $data['sc_number'] !="")
        {
            $where      .= "   s.sc_number = '".$data['sc_number']."'";

        }else{
                $where .= "   s.card = '" . $data['card'] . "'";
        }
     $result=$this->alias('s')->where($where)
            ->join(['oa_depart'=>'d'],'s.depart = d.id','LEFT')
            ->join(['oa_major'=>'m'],'s.major = m.id','LEFT')
            ->field("s.id,s.name,file_number,file_derice,sc_number,file_num,country,nation,sex,d.name AS dname,m.name AS mname,grade,class,card,phone,addtime,s.status,s.xj_status")
            ->find();
      return $result;
    }


    //查询in
    public function getFileByIds($ids){
        $re_arr     = $this->where("id","IN",$ids)->select();
        return $re_arr;
    }
}
