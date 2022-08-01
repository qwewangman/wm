<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------



use think\Route;



//后台登录
Route::rule('/','index/index/index');

#前台处理登录
Route::rule('/login','index/index/home_index');



Route::rule('/setpage','index/file/setPage');

#后台处理登录
Route::rule('/login_do','index/index/login_do');

Route::rule('/login_out','index/index/login_out');




Route::group('api', function () {

    Route::rule('/login','index/admin/admin_index');


});








//案卷管理
Route::group('file', function () {
    //立卷
    Route::get('add', 'index/file/addFile');
    Route::get('bigpage', 'index/file/bigPage');
    //立卷
    Route::get('edit/:id', 'index/file/editFile');
    //删除立卷
    Route::rule('del', 'index/file/delFile');
    //传感器列表
    Route::get('getlist', 'index/file/getlist');//传感器列表
    Route::get('getHomeInfo', 'index/file/getHomeInfo');//传感器列表
    Route::get('getcateinfo', 'index/file/getcateinfo');//大棚列表
    Route::get('getcodeinfo', 'index/file/getcodeinfo');//大棚列表
    Route::get('getHouseInfo', 'index/file/getHouseInfo');//大棚列表
    Route::get('getdataInfo', 'index/file/getdataInfo');//温湿度折线图
    //案卷数据ajax
    Route::get('filelist', 'index/file/fileList');
    //案卷数据ajax
    Route::get('filealllist', 'index/file/fileAllList');
    //获取一条数据
    Route::get('getone','index/file/profile');
    //接收档案
    Route::get('receivefile','index/file/receiveFile');
    //处理接收档案
    Route::rule('doreceive','index/file/doReceiveFile');
    //接收档案
    Route::get('changefile','index/file/changeFile');
    //处理接收档案
    Route::rule('dochange/:id','index/file/doChangeFile');
    //编辑传感器
    Route::rule('/save','index/file/saveFile');
    //更新
    Route::rule('/update/:id','index/file/updateFile');
    //判断是否存在学号
    Route::rule('/havnum','index/file/isHavNum');
    //交接
    Route::rule('/join','index/file/join');
    //接收页面
    Route::rule('/receive','index/file/receive');
    //接收列表
    Route::rule('/getreceivelist','index/file/getReceiveList');
    //确认接收
    Route::rule('/setreceive','index/file/setReceive');
    //查看案卷变动
    Route::rule('/change','index/file/change');
    //查看案卷变动
    Route::rule('/getchange','index/file/getChange');
    //查看文件
    Route::rule('/file','index/file/file');
    Route::rule('/getfile','index/file/getFile');
    //打印
    Route::rule('/print','index/file/printFile');
    //上传
    Route::rule('/upload','index/file/upload');
    //上传处理
    Route::rule('/uploadexcel','index/file/uploadExccel');
    //上传处理
    Route::rule('/doupload','index/file/doUpload');
    //迁档展示movefile
    Route::rule('/movefile','index/file/moveFile');//borrowmanage addAllData
    Route::rule('/addAllData','index/file/addAllData');
    //借阅管理
    //附中
    Route::rule('/SearchSNum','index/file/searchSnum');    //搜索学生学号
    //附中 添加学生档案到审核
    Route::rule('/addfilecheck','index/file/addFileCheck');
    //附中 学生列表
    Route::rule('/affiliatedFileList','index/file/affiliatedFileList');   
    //附中 打印
    Route::rule('/affiliatedDyFile','index/file/affiliatedDyFile');
    //下载pdf
    Route::rule('/downloadpdf','index/file/downloadPdf');
    //存储位置
    Route::rule('/ccwz','index/file/ccwz');//filechange
    //案卷变动
    Route::rule('/filechange','index/file/fileChange');
    //存储位置
    Route::rule('/setccwz','index/file/setCcwz');
    //立卷
    Route::rule('/fill','index/file/fill');
    Route::rule('/setfile','index/file/setfile');
    Route::rule('/printCatalogue','index/file/printCatalogue');
    Route::rule('/printFill','index/file/printFill');//
    Route::rule('/printAll','index/file/printAll');//printAllCatalogue
    Route::rule('/printAllCatalogue','index/file/printAllCatalogue');//
    Route::rule('/printAllFill','index/file/printAllFill');//deletejoin
    //删除附中数据
    Route::rule('/deletejoin','index/file/deleteJoin');//changesend
    //改派
    Route::rule('/changesend','index/file/changesSend');

});























//文件管理
Route::group('doc', function () {
    //获取文件
    Route::rule('/getdoc','index/doc/getDoc');
    //获取文件列表
    Route::rule('/getdoclist','index/doc/getDocList');
    //获取文件
    Route::rule('/getonedoc','index/doc/getOneDoc');
    //获取文件列表
    Route::rule('/save','index/doc/save');
    //获取文件列表
    Route::rule('/saveall','index/doc/saveAll');
    //添加文件
    Route::rule('/adddoc','index/doc/addDoc');
    //添加文件
    Route::rule('/addonedoc','index/doc/addOneDoc');
    //上传电子版
    Route::rule('/upload','index/doc/upload');
    //批量添加文件
    Route::rule('/addalldoc','index/doc/addAllDoc');
    //添加文件
    Route::rule('/editdoc/:id','index/doc/editDoc');
    //添加文件
    Route::rule('/update/:id','index/doc/updateDoc');//deletedoc
    //删除文件
    Route::rule('/deletedoc','index/doc/deleteDoc');//saveMuch
    Route::rule('/saveMuch','index/doc/saveMuch');

});
//变化管理
Route::group('change', function () {
    //迁出
    Route::rule('/getout','index/change/getOut');
    //获取迁出列表
    Route::rule('/getoutlist','index/change/getOutList');
    //迁出
    Route::rule('/getjie','index/change/getJie');
    //获取迁出列表
    Route::rule('/getjielist','index/change/getJieList');
    //获取迁出列表
    Route::rule('/setjieyue','index/change/setJieYue');
    //借阅详情展示
    Route::rule('/borrowmanage','index/change/borrowManage');//borrowdetail
    Route::rule('/borrowdetail','index/change/borrowDetail');//borrowcontent
    Route::rule('/borrowcontent','index/change/borrowContent');

});

//系统接口
Route::group('system', function () {
    //获取专业
    Route::rule('/getzy','index/admin/getZy');
    //获取院系
    Route::rule('/getyx','index/admin/getYx');
    //获取市区
    Route::rule('/getcity','index/admin/getCity');
    //获取编码规则
    Route::rule('/getcode','index/admin/getCodeRule');
    //设置院系
    Route::rule('/setdepart','index/admin/setDepart');
    Route::rule('/sethouse','index/admin/setHouse');
    //设置院系
    Route::rule('/getdepart','index/admin/getDepart');
    //设置院系
    Route::rule('/updatedepart','index/admin/updateDepart');

    //设置院系专业
    Route::rule('/setmajor','index/admin/setMajor');
    //设置院系专业
    Route::rule('/getmajor','index/admin/getMajor');
    //设置院系专业
    Route::rule('/updatemajor','index/admin/updateMajor');

    //类目管理
    Route::rule('/category','index/admin/category');
    //设置类目管理
    Route::rule('/getcategory','index/admin/getCategory');
    //设置类目管理
    Route::rule('/updatecategory','index/admin/updateCategory');

    //管理员管理
    Route::rule('/user','index/admin/userList');
    //设置管理员管理
    Route::rule('/getuser','index/admin/getUser');
    //设置管理员管理
    Route::rule('/updateuser','index/admin/updateUser');
    //设置农业
    Route::rule('/adduser','index/admin/addUser');//增加人员
    Route::rule('/addpart','index/admin/addpart');
    Route::rule('/addcode','index/admin/addcode');
    Route::rule('/addcate','index/admin/addcate');
    Route::rule('/addhouse','index/admin/addhouse');
    //设施农业添加
    Route::rule('/saveuser','index/admin/saveUser');//人员
    Route::rule('/savepart','index/admin/savepart');//传感器
    Route::rule('/savecode','index/admin/savecode');//地块
    Route::rule('/savecate','index/admin/savecate');//大棚
    Route::rule('/savehouse','index/admin/savehouse');//大棚
    //邮寄地址管理
    Route::rule('/post','index/admin/post');
    //邮寄地址管理
    Route::rule('/addpost','index/admin/addPost');
    //邮寄地址管理
    Route::rule('/getpost','index/admin/getPost');
    //邮寄地址管理
    Route::rule('/savepost','index/admin/savePost');

    //获取类别
    Route::rule('/getcategorylist','index/admin/getCategoryList');
    //上次
    Route::rule('/upload','index/admin/uploadFile');
    //
    Route::rule('/settype','index/admin/setType');
    //批量添加数据addall
    Route::rule('/addall','index/file/addAll');//deleteAddress
    //删除
    Route::rule('/deleteAddress','index/admin/deleteAddress');//updatepost
    //修改
    Route::rule('/updatepost','index/admin/updatePost');//updateDate
    Route::rule('/updateDate','index/admin/updateDate');//updatepost


});















