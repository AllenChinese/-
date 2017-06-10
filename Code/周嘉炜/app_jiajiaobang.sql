-- phpMyAdmin SQL Dump
-- version 3.3.8.1
-- http://www.phpmyadmin.net
--
-- 主机: w.rdc.sae.sina.com.cn:3307
-- 生成日期: 2017 年 06 月 10 日 17:41
-- 服务器版本: 5.6.23
-- PHP 版本: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `app_jiajiaobang`
--

-- --------------------------------------------------------

--
-- 表的结构 `wx_feedback`
--

CREATE TABLE IF NOT EXISTS `wx_feedback` (
  `fid` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_mes` varchar(128) DEFAULT NULL,
  `bname` varchar(16) DEFAULT NULL,
  `bman` varchar(16) DEFAULT NULL,
  PRIMARY KEY (`fid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 转存表中的数据 `wx_feedback`
--


-- --------------------------------------------------------

--
-- 表的结构 `wx_message`
--

CREATE TABLE IF NOT EXISTS `wx_message` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `mes_name` varchar(32) DEFAULT NULL,
  `mes_detail` varchar(128) DEFAULT NULL,
  `mes_money` varchar(32) DEFAULT NULL,
  `mes_address` varchar(32) DEFAULT NULL,
  `mes_phone` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- 转存表中的数据 `wx_message`
--

INSERT INTO `wx_message` (`mid`, `mes_name`, `mes_detail`, `mes_money`, `mes_address`, `mes_phone`) VALUES
(1, '周某某', '招数学女家教一名，最好大四', '100/天', '杭州市文三路支付宝大厦', '13967395599'),
(2, '胡歌', '招数学家教，周末两天', '120/天', '杭州市滨江区江南名都', '13858367979'),
(3, '然然', '诚聘一位大三的学生，辅导小学数学', '100/天', '北京三里屯', '13967399888'),
(6, '柯女士', '家教 5次/周，学生是男孩小学4年级的，主要补习英语，其他科目也可以捎带。', '70元/小时', '上海松江区', '17710324789'),
(7, '尚先生', '小学三年级男孩补习语文，考试C，一周2次，周一到周五上一次，晚上放学后方便，周六周天上一次，都方便，暑假需要上课 ', '100元/小时', '上海嘉定区', '17710324628'),
(8, '张女士', '小学六年级 男孩 英语和数学都在及格线左右 周一至周五晚上5点半之后可以上课 每周三或四次课程 ', '50元/小时', '济南天桥区', '15858581580'),
(9, '苏女士', '今年上小学，男孩，对英语感兴趣，想培养下孩子语感和英语的学习氛围', '65元/小时', '济南历下区', '18910583435'),
(10, '张女士', '小学四年级女孩补习语文数学考试60-70分左右，基础差，辅导作业，周一到周五4:30可以开始，一周5次一次两个小时 ', '70元/小时', '杭州萧山区', '18910322020'),
(11, '李女士', '初一女孩补习全科，想办休学在家，小学基础不错，在学习融入不进，不想去，想换学校，之后每天方便，需要讲课 ', '面议', '杭州滨江区 ', '15810324808'),
(12, '毛毛', '想找一个兼职的英语老师，一周最好能够教3天', '50/小时', '济南天桥区', '18835796588'),
(13, '王先生', '儿子上小学，想找一个大四的学生来教语文、数学和英语。周末进行辅导', '100/天', '济南市中区', '13769055069');

-- --------------------------------------------------------

--
-- 表的结构 `wx_student`
--

CREATE TABLE IF NOT EXISTS `wx_student` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `stu_name` varchar(32) DEFAULT NULL,
  `stu_school` varchar(32) DEFAULT NULL,
  `stu_grade` varchar(32) DEFAULT NULL,
  `stu_major` varchar(32) DEFAULT NULL,
  `stu_phone` varchar(32) DEFAULT NULL,
  `s_star` int(128) NOT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;

--
-- 转存表中的数据 `wx_student`
--

INSERT INTO `wx_student` (`sid`, `stu_name`, `stu_school`, `stu_grade`, `stu_major`, `stu_phone`, `s_star`) VALUES
(1, '周嘉炜', '济南大学', '大四', '通信工程', '13967395930', 15),
(2, '李开复', '哥伦比亚大学', '博士', '计算机科学', '13990901234', 12),
(3, '施振强', '北京大学', '大四', '法律系', '15858900573', 8),
(4, '刘帅', '郑州大学', '研一', '环境工程', '15867309090', 2),
(5, '刘斌', '山东大学', '大四', '机械工程', '13402202688', 13),
(6, '米明瑞', '中国海洋大学', '大三', '心理学教育', '13490906745', 1),
(7, '于奎强', '中国科技大学', '研二', '信息科学', '13978786745', 8),
(8, '宋金鑫', '杭州电子科技大学', '研一', '嵌入式', '13889899090', 3),
(9, '栾帅', '中国石油大学', '大四', '计算机科学', '15690907834', 2),
(10, '厉龙建', '南京理工大学', '大二', '英语教育', '13478569090', 10),
(11, '候亮平', '汉东大学', '研二', '政法系', '13409095730', 0),
(12, '周玉昊', '暨南大学', '大一', '通信技术', '13400129898', 0),
(13, '马化腾', '深圳大学', '大四', '计算机专业', '13805889001', 0),
(14, '周嘉炜', '济南大学', '大三', '通信', '13402202695', 0);

-- --------------------------------------------------------

--
-- 表的结构 `wx_student_feedback`
--

CREATE TABLE IF NOT EXISTS `wx_student_feedback` (
  `sfid` int(11) NOT NULL AUTO_INCREMENT,
  `s_name` varchar(32) NOT NULL,
  `s_phone` varchar(32) NOT NULL,
  `s_feedback` varchar(16) NOT NULL,
  `s_detail` varchar(128) NOT NULL,
  PRIMARY KEY (`sfid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 转存表中的数据 `wx_student_feedback`
--


-- --------------------------------------------------------

--
-- 表的结构 `wx_user`
--

CREATE TABLE IF NOT EXISTS `wx_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(32) DEFAULT NULL,
  `user_phone` varchar(128) DEFAULT NULL,
  `u_star` int(128) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- 转存表中的数据 `wx_user`
--

INSERT INTO `wx_user` (`uid`, `user_name`, `user_phone`, `u_star`) VALUES
(1, '周杰伦', '13490901212', 2),
(2, '博尔特', '13478781212', -2),
(3, '刘诗诗', '13956561212', 0),
(4, '郭碧婷', '13956567878', 5),
(5, '刘亦菲', '18923236767', 6),
(7, '唐嫣', '13480801234', 1),
(8, '刘涛', '13956781234', 5),
(9, '阮一峰', '18805731234', 0),
(10, '马云', '18715108888', 0);

-- --------------------------------------------------------

--
-- 表的结构 `wx_user_feedback`
--

CREATE TABLE IF NOT EXISTS `wx_user_feedback` (
  `ufid` int(11) NOT NULL AUTO_INCREMENT,
  `u_name` varchar(32) NOT NULL,
  `u_phone` varchar(32) NOT NULL,
  `u_feedback` varchar(16) NOT NULL,
  `u_detail` varchar(128) NOT NULL,
  PRIMARY KEY (`ufid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- 转存表中的数据 `wx_user_feedback`
--

