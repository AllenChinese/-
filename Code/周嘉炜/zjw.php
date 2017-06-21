<?php
include 'connection.php';
header('Content-type:text;charset=utf-8');

define("TOKEN", "allenzjw");//Token密钥

mysql_select_db("app_jiajiaobang", $con);//连接数据库MySQL

$wechatObj = new wechatCallbackapiTest();//类由抽象转成实体化
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R \r\n".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
            
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
				case "shortvideo":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T \r\n".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = array();
                $content[] = array("Title"=>"欢迎关注大学生家教系统公众号",  "Description"=>" 济南大学是山东省人民政府和国家教育部共建的综合性大学、山东省重点建设大学，具有学士、硕士、博士学位授予权。\n\n请回复 \"菜单\" 关键字试试吧！", "PicUrl"=>"http://www.ujn.edu.cn/data/upload/ueditor/20161129/583c6532060c1.JPG", "Url" =>"http://www.ujn.edu.cn/");
                //$content = "欢迎关注大学生家教系统公众号 \n请回复 \"菜单\" 关键字试试吧！";
                if (!empty($object->EventKey)){
                    $content .= "\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey);
                }
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
                        $content = array();
                        $content[] = array("Title"=>"Allen工作室", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "scancode_waitmsg":
                if ($object->ScanCodeInfo->ScanType == "qrcode"){
                    $content = "扫码带提示：类型 二维码 结果：".$object->ScanCodeInfo->ScanResult;
                }else if ($object->ScanCodeInfo->ScanType == "barcode"){
                    $codeinfo = explode(",",strval($object->ScanCodeInfo->ScanResult));
                    $codeValue = $codeinfo[1];
                    $content = "扫码带提示：类型 条形码 结果：".$codeValue;
                }else{
                    $content = "扫码带提示：类型 ".$object->ScanCodeInfo->ScanType." 结果：".$object->ScanCodeInfo->ScanResult;
                }
                break;
            case "scancode_push":
                $content = "扫码推事件";
                break;
            case "pic_sysphoto":
                $content = "系统拍照";
                break;
            case "pic_weixin":
                $content = "相册发图：数量 ".$object->SendPicsInfo->Count;
                break;
            case "pic_photo_or_album":
                $content = "拍照或者相册：数量 ".$object->SendPicsInfo->Count;
                break;
            case "location_select":
                $content = "发送位置：标签 ".$object->SendLocationInfo->Label;
                break;
			case "ShakearoundUserShake":
				$content = "摇一摇\nUuid：".$object->ChosenBeacon->Uuid.
				"\nMajor：".$object->ChosenBeacon->Major.
				"\nMinor：".$object->ChosenBeacon->Minor.
				"\nDistance：".$object->ChosenBeacon->Distance.
				"\nRssi：".$object->ChosenBeacon->Rssi.
				"\nMeasurePower：".$object->ChosenBeacon->MeasurePower.
				"\nChosenPageId：".$object->ChosenBeacon->ChosenPageId
				;
				break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }

        if(is_array($content)){
            $result = $this->transmitNews($object, $content);
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收文本消息
    private function receiveText($object)
    {
        $keyword = trim($object->Content);
		if(strpos($keyword,"+") == true){//输入字符串中有没有+
        	$newkeyword = explode("+",$keyword);//进行字符串切割
            if(strstr($newkeyword[0], "学生账号绑定")){
            	$mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
                //===============执行插入数据的操作==========================

                $sql    =   "INSERT INTO `wx_student`(`stu_name`,`stu_school`,`stu_grade`,`stu_major`,`stu_phone`,`s_star`) VALUES
                                ('$newkeyword[1]','$newkeyword[2]','$newkeyword[3]','$newkeyword[4]','$newkeyword[5]','0')";

                $mysql->runSql($sql);    //执行插入数据的操作

                if($mysql->errno() != 0 )
                {
                    die( "Error:" . $mysql->errmsg() );
                }
                else
                {
                   $content = "恭喜您绑定成功!";
                }
            }else if(strstr($newkeyword[0], "家长账号绑定")){
                $mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
                //===============执行插入数据的操作==========================

                $sql    =   "INSERT INTO `wx_user`(`user_name`,`user_phone`,`u_star`) VALUES
                                ('$newkeyword[1]','$newkeyword[2]','0')";

                $mysql->runSql($sql);    //执行插入数据的操作

                if($mysql->errno() != 0 )
                {
                    die( "Error:" . $mysql->errmsg() );
                }
                else
                {
                    $content = "恭喜您绑定成功!";
                }
            }else if(strstr($newkeyword[0], "明星教员查询")){
                $content = $newkeyword[0];
              	$mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
                $sql    =   "SELECT * FROM wx_student order by `s_star` desc";
                $result =   mysql_query($sql);
				$rows = array();
                if( mysql_num_rows( $result ) ){
                  while( $row = mysql_fetch_array( $result ) )
                  {
                   $rows[] = $row;
                  }
                  $count=mysql_num_rows($result);
                      if($count > 1){//多图文
                       	$content = array();
                        foreach($rows as $value){
                            $num = $num + 1;
                            if($num <= $newkeyword[1]){
                            	$name = $value[1];
                                $school = $value[2];
                                $grade = $value[3];
                                $major = $value[4];
                                $phone = $value[5];
                                $star = $value[6];
                                $Description .= "姓名：$name\n学校：$school\n年级：$grade\n专业：$major\n电话：$phone\n点赞数：$star\n————————————————————\n";
                            }                       	
                        }
              			$content[] = array("Title" =>"明星教员排名情况", 
                                    "Description" =>$Description,                                                   
                                    "PicUrl" =>"", 
                                    "Url" =>"");
                      }
                }else{
                	$content = '没有相关的信息';
                }
            }else if(strstr($newkeyword[0], "家教信息发布")){
                $mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
                //===============执行插入数据的操作==========================

                $sql    =   "INSERT INTO `wx_message`(`mes_name`,`mes_detail`,`mes_money`,`mes_address`,`mes_phone`) VALUES
                                ('$newkeyword[1]','$newkeyword[2]','$newkeyword[3]','$newkeyword[4]','$newkeyword[5]')";

                $mysql->runSql($sql);    //执行插入数据的操作

                if($mysql->errno() != 0 )
                {
                    die( "Error:" . $mysql->errmsg() );
                }
                else
                {
                    $content = "家教信息发布成功!";
                }
            }else if(strstr($newkeyword[0], "家教信息查询")){
                $mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
                $sql    =   "SELECT * FROM `wx_message` WHERE (`mes_name` LIKE '%$newkeyword[1]%') OR (`mes_detail` LIKE '%$newkeyword[1]%') OR (`mes_money` LIKE '%$newkeyword[1]%') OR (`mes_address` LIKE '%$newkeyword[1]%')";
                $result =   mysql_query($sql);
				$rows = array();
                if( mysql_num_rows( $result ) ){
                  while( $row = mysql_fetch_array( $result ) )
                  {
                   $rows[] = $row;
                  }
                  $count=mysql_num_rows($result);
                      if($count > 1){//多图文
                       	$content = array();
                        foreach($rows as $value){
                        	$name1 = $value[1];
                          	$con1 = $value[2];
                          	$money1 = $value[3];
                          	$address1 = $value[4];
                          	$phone1 = $value[5];
                           	$Description .= "姓名：$name1\n电话：$phone1\n工资：$money1\n地址：$address1\n详情：$con1\n————————————————————\n";
                        }
              			$content[] = array("Title" =>"家教信息", 
                                    "Description" =>$Description,                                                   
                                    "PicUrl" =>"", 
                                    "Url" =>"");
                      }else{//单图文
                      	 	$content = array();
                          	$name = $rows[0][1];
                          	$con = $rows[0][2];
                          	$money = $rows[0][3];
                          	$address = $rows[0][4];
                          	$phone = $rows[0][5];
                            $content[] = array("Title" =>"家教信息", 
                                    "Description" =>"姓名：$name\n".
                                                    "电话：$phone\n".
                                                    "工资：$money\n".
                                                    "地址：$address\n".
                                                    "详情：$con\n",                                                   
                                    "PicUrl" =>"", 
                                    "Url" =>"");
                      }
                 }else{
                  $content = '没有要找的内容';
                 }
            }else if(strstr($newkeyword[0], "家长评价反馈")){
                $mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
				if($newkeyword[2] == '好'){                
                	$sql = "SELECT * FROM `wx_student` WHERE stu_phone = $newkeyword[1]";
                    $result =   mysql_query($sql);
                  	if($row = mysql_fetch_array($result)){ 	 
                        $row[6]++;
                        $sql = "UPDATE wx_student SET s_star='$row[6]' WHERE stu_phone = $newkeyword[1]";
                        $result = mysql_query($sql);
                        if ($result){ 
                           $content = '感谢您的评价！';
                        }
                        else{ 
                           $content = '评价未成功，请核实后重新输入！';
                        }
                    }else{
                    	$content = '您想评价的对象不存在或未进行账号绑定，请核实后重新输入！';
                    }
                }else if($newkeyword[2] == '不好'){
                   $sql = "SELECT * FROM `wx_student` WHERE stu_phone = $newkeyword[1]";
                    $result =   mysql_query($sql);
                  	if($row = mysql_fetch_array($result)){ 	 
                        $row[6]--;
                        $sql = "UPDATE wx_student SET s_star='$row[6]' WHERE stu_phone = $newkeyword[1]";
                        $result = mysql_query($sql);
                        if ($result){ 
                           $content = '感谢您的评价！';
                        }
                        else{ 
                           $content = '评价未成功，请核实后重新输入！';
                        }
                    }else{
                    	$content = '您想评价的对象不存在或未进行账号绑定，请核实后重新输入！';
                    }
                }else{
                	$content = '请输入正确的评价格式！';
                }
                /*$sql    =   "INSERT INTO `wx_student_feedback`(`s_name`,`s_phone`,`s_feedback`,`s_detail`) VALUES
                                ('$newkeyword[1]','$newkeyword[2]','$newkeyword[3]','$newkeyword[4]')";

                $mysql->runSql($sql);    //执行插入数据的操作

                if($mysql->errno() != 0 )
                {
                    die( "Error:" . $mysql->errmsg() );
                }
                else
                {
                     $content = "评价成功，感谢您的评价！";
                }*/
            }else if(strstr($newkeyword[0], "学生评价反馈")){
                $mysql  =   new SaeMysql(); //该类在初始化的过程中就完成了链接数据库的工作
				if($newkeyword[2] == '好'){                
                	$sql = "SELECT * FROM `wx_user` WHERE user_phone = $newkeyword[1]";
                    $result =   mysql_query($sql);
                  	if($row = mysql_fetch_array($result)){ 	 
                        $row[3]++;
                        $sql = "UPDATE wx_user SET u_star='$row[3]' WHERE user_phone = $newkeyword[1]";
                        $result = mysql_query($sql);
                        if ($result){ 
                           $content = '感谢您的评价！';
                        }
                        else{ 
                           $content = '评价未成功，请核实后重新输入！';
                        }
                    }else{
                    	$content = '您想评价的对象不存在或未进行账号绑定，请核实后重新输入！';
                    }
                }else if($newkeyword[2] == '不好'){
                   $sql = "SELECT * FROM `wx_user` WHERE user_phone = $newkeyword[1]";
                    $result =   mysql_query($sql);
                  	if($row = mysql_fetch_array($result)){ 	 
                        $row[3]--;
                        $sql = "UPDATE wx_user SET u_star='$row[3]' WHERE user_phone = $newkeyword[1]";
                        $result = mysql_query($sql);
                        if ($result){ 
                           $content = '感谢您的评价！';
                        }
                        else{ 
                           $content = '评价未成功，请核实后重新输入！';
                        }
                    }else{
                    	$content = '您想评价的对象不存在或未进行账号绑定，请核实后重新输入！';
                    }
                }else{
                	$content = '请输入正确的评价格式！';
                }
            }
        }else{
        	 //多客服人工回复模式
            if (strstr($keyword, "请问在吗") || strstr($keyword, "在线客服")){
                $result = $this->transmitService($object);
                return $result;
            }

            //自动回复模式
            if (strstr($keyword, "菜单")){
                $content = array();
                $content[] = array("Title" =>"家教系统基础功能指南", 
                        "Description" =>"学生用户绑定 回复1\n\n".
                                        "家长用户绑定 回复2\n\n".
                                        "家教信息发布 回复3\n\n".
                                        "家教信息查询 回复4\n\n".
                                        "家长评价反馈 回复5\n\n".
                                        "学生评价反馈 回复6\n\n".
                                  		"明星教员查询 回复7\n",
                        "PicUrl" =>"", 
                        "Url" =>"");
                //$content = "功能： 回复指令\n学生用户绑定 回复1\n家长用户绑定 回复2\n家教信息发布 回复3\n家教信息查询 回复4\n家长评价反馈 回复5\n学生评价反馈 回复6\n";
            }else if (strstr($keyword, "1")){
                //$content = "学生账号绑定，例如：学生账号绑定+侯亮平+汉东大学+研二+政法系+13402202688";	
                $content = array();
                $content[] = array("Title"=>"学生账号绑定",  "Description"=>"例如：学生账号绑定+侯亮平+汉东大学+研二+政法系+13402****88", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "2")){
                //$content = "家长账号绑定，例如：家长账号绑定+季昌明+13867888888";
                $content = array();
                $content[] = array("Title"=>"家长账号绑定",  "Description"=>"例如：家长账号绑定+季昌明+13867****88", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "3")){
                //$content = "家长家教信息发布，例如：家长家教信息发布+然然+诚聘一位大三的学生，辅导小学数学+100/天+北京三里屯+13967399888";
                $content = array();
                $content[] = array("Title"=>"家长家教信息发布",  "Description"=>"例如：家长家教信息发布+然然+诚聘一位大三的学生，辅导小学数学+100/天+北京三里屯+13967****88", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "4")){
                //$content = "家教信息查询，例如：输入关键字，例如：家教信息查询+100、地址、数学等等";
                $content = array();
                $content[] = array("Title"=>"家教信息查询",  "Description"=>"例如：输入关键字，例如：家教信息查询+100、地址、数学等等", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "5")){
                //$content = "家长评价反馈，例如：家长评价反馈+学生姓名+联系方式+评价（好或不好）+评价内容";
                $content = array();
                $content[] = array("Title"=>"家长评价反馈",  "Description"=>"例如：家长评价反馈+联系方式+评价（好或不好）", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "6")){
                //$content = "学生评价反馈，例如：学生评价反馈+联系方式+评价（好或不好）";
                $content = array();
                $content[] = array("Title"=>"学生评价反馈",  "Description"=>"例如：学生评价反馈+联系方式+评价（好或不好）", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "7")){
                $content = array();
                $content[] = array("Title"=>"明星教员查询",  "Description"=>"例如：明星教员查询+展示条数", "PicUrl"=>"", "Url" =>"");
            }else if (strstr($keyword, "单图文") || strstr($keyword, "图文1")){
                $content = array();
                $content[] = array("Title"=>"单图文标题",  "Description"=>"单图文内容", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
            }else if (strstr($keyword, "多图文") || strstr($keyword, "图文2")){
                $content = array();
                $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
            }else if (strstr($keyword, "图文3") || strstr($keyword, "空气")){
                $content = array();
                $content[] = array("Title" =>"济南空气质量", 
                        "Description" =>"空气质量指数(AQI)：32\n".
                                        "空气质量等级：优\n".
                                        "细颗粒物(PM2.5)：12\n".
                                        "可吸入颗粒物(PM10)：31\n".
                                        "一氧化碳(CO)：0.9\n".
                                        "二氧化氮(NO2)：31\n".
                                        "二氧化硫(SO2)：5\n".
                                        "臭氧(O3)：20\n".
                                        "更新时间： 2014-06-30",
                        "PicUrl" =>"", 
                        "Url" =>"");
            }else if (strstr($keyword, "常用") || strstr($keyword, "常用链接")){
                $content[] = array("Title" =>"欢迎关注Allen工作室","Description" =>"", "PicUrl" =>"", "Url" =>"");
                 $content[] = array("Title" =>"违章查询", "Description" =>"", "PicUrl" =>"http://pic25.nipic.com/20121107/7185356_171642579104_2.jpg", "Url" =>"http://app.eclicks.cn/violation2/webapp/index?appid=10");
                $content[] = array("Title"=>"公交查询", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/91d3572c11dfa9ec144e43be6bd0f703918fc133.jpg", "Url" =>"http://map.baidu.com/mobile/webapp/third/transit/");
                $content[] = array("Title"=>"黄历查询", "Description"=>"", "PicUrl"=>"http://f.hiphotos.bdimg.com/wisegame/pic/item/3aee3d6d55fbb2fb8e689396464a20a44723dcf0.jpg", "Url" =>"http://baidu365.duapp.com/uc/Calendar.html");
                $content[] = array("Title"=>"常用电话", "Description"=>"", "PicUrl"=>"http://f.hiphotos.bdimg.com/wisegame/pic/item/15094b36acaf2edd4eed636a841001e939019311.jpg", "Url" =>"http://m.hao123.com/n/v/dianhua");
                $content[] = array("Title"=>"四六级查分", "Description"=>"", "PicUrl"=>"http://f.hiphotos.bdimg.com/wisegame/pic/item/c70f4bfbfbedab6476d56388f536afc378311ed6.jpg", "Url" =>"http://cet.fangbei.org/index.php");
                $content[] = array("Title"=>"实时路况", "Description"=>"", "PicUrl"=>"http://e.hiphotos.bdimg.com/wisegame/pic/item/e18ba61ea8d3fd1f754c8276384e251f95ca5f30.jpg", "Url" =>"http://map.baidu.com/mobile/webapp/third/traffic/foo=bar/traffic=on");

            }else if (strstr($keyword, "音乐")){
                $content = array();
                $content = array("Title"=>"最炫民族风", "Description"=>"歌手：凤凰传奇", "MusicUrl"=>"http://mascot-music.stor.sinaapp.com/zxmzf.mp3", "HQMusicUrl"=>"http://mascot-music.stor.sinaapp.com/zxmzf.mp3"); 
            }else{
                $content = date("Y-m-d H:i:s",time())."\n技术支持 Allen工作室";
                // $content = "";
            }
        }
       

        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }
        return $result;
    }

    //接收图片消息
    private function receiveImage($object)
    {
        
        include("faceplusplus.php");
        $imgurl = strval($object->PicUrl);
        $content = getFaceValue($imgurl);
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，经度为：".$object->Location_Y."；纬度为：".$object->Location_X."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }
        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }
	
    //回复文本消息
    private function transmitText($object, $content)
    {
        if (!isset($content) || empty($content)){
            return "";
        }

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);

        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return "";
        }
        $itemTpl = "        <item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
        </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[news]]></MsgType>
    <ArticleCount>%s</ArticleCount>
    <Articles>
$item_str    </Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        if(!is_array($musicArray)){
            return "";
        }
        $itemTpl = "<Music>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <MusicUrl><![CDATA[%s]]></MusicUrl>
        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
    </Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[music]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
        <MediaId><![CDATA[%s]]></MediaId>
    </Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[image]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
        <MediaId><![CDATA[%s]]></MediaId>
    </Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[voice]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
        <MediaId><![CDATA[%s]]></MediaId>
        <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
    </Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[video]]></MsgType>
    $item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('Y-m-d H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
?>