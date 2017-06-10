<?php
header('Content-type:text;charset=utf-8');
session_start();//初始化
error_reporting(0);//// 关闭错误报告
$servername = "w.rdc.sae.sina.com.cn:3306";
$username = "j00ll440zz";
$password = "kij30hliyh3yxkz4wzz14hlm2m0mymhk14z33iiw";

$con = mysql_connect($servername,$username,$password);

/*可以加入下面这段话测试*/
if ($con)
    echo "success";
?>    