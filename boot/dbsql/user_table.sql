-- [ 帳號與權限表格 ]

--
-- 表的結構 `permission_role`
--

CREATE TABLE IF NOT EXISTS `permission_role` (
  `rno` varchar(3) NOT NULL,
  `name` varchar(10) NOT NULL,
  `descrip` varchar(50) NOT NULL,
  `pri_limit` int(2) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_keep` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`rno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 轉存資料表中的資料 `permission_role`
--

INSERT INTO `permission_role` (`rno`, `name`, `descrip`, `pri_limit`, `create_time`, `_keep`) VALUES
('R00', '系統管理者', '執行所有系統功能', 9, '2015-11-23 01:38:19', 1),
('R01', '群組管理者', '執行群組系統功能', 7, '2016-05-10 07:22:11', 1),
('R02', '資料管理者', '執行檔案管理功能', 5, '2016-05-10 07:25:23', 1),
('R03', '校驗人員', '檢視與修改檔案工作', 3, '2016-05-10 07:25:16', 1),
('R04', '建檔人員', '執行分配建檔工作', 1, '2016-05-10 07:24:44', 1),
('R05', '一般成員', '檢視公告與檔案內容', 0, '2016-05-10 07:26:36', 1);



--
-- 表的結構 `permission_rule`
--

CREATE TABLE IF NOT EXISTS `permission_rule` (
  `prno` int(5) NOT NULL AUTO_INCREMENT,
  `mode` varchar(5) NOT NULL,
  `limitto` varchar(10) NOT NULL,
  `target` varchar(50) NOT NULL,
  `table` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `operator` varchar(10) NOT NULL,
  `contents` tinytext NOT NULL,
  `creater` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_keep` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`prno`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

--
-- 轉存資料表中的資料 `permission_rule`
--

INSERT INTO `permission_rule` (`prno`, `mode`, `limitto`, `target`, `table`, `field`, `operator`, `contents`, `creater`, `create_time`, `_keep`) VALUES
(1, 'acl', 'group', 'adm', 'data_book_catalog', 'SUBSTR(bookid,1,3)', 'IN', '001,002,003,006,008,009,011,012,013,014,015,016,019,021,022,024,024,028,028', 'admin', '2016-05-16 08:15:21', 1),
(2, 'acl', 'group', 'rcdh', 'permission_matrix', 'permission_matrix.gid', 'IN', 'rcdh', 'admin', '2016-05-11 09:50:44', 1),
(3, 'acl', 'group', 'rcdh', 'data_project', 'data_project.gid', 'IN', 'rcdh,tpc', 'admin', '2016-05-11 09:28:46', 1),
(4, 'acl', 'group', 'rcdh', 'format_project', 'gid', 'IN', 'rcdh,tpc', 'admin', '2016-05-11 09:28:49', 1),
(5, 'acl', 'group', 'tpc', 'permission_matrix', 'gid', 'IN', 'tpc', 'admin', '2016-05-11 09:28:52', 1),
(6, 'acl', 'group', 'tpc', 'data_project', 'data_project.gid', 'IN', 'tpc', 'admin', '2016-05-11 09:28:55', 1),
(7, 'acl', 'group', 'tpc', 'format_project', 'gid', 'IN', 'rcdh,tpc', 'admin', '2016-05-11 09:28:57', 1),
(8, 'acl', 'group', 'tpc', 'refer_member', 'gid', 'IN', 'tpa', 'admin', '2016-05-11 09:29:00', 1),
(9, 'rbac', 'role', 'R01', 'Staff_Controller', 'method', 'IN', 'index,read,save,dele,startmail,gmember,gpadd,gpdef', 'admin', '2016-05-11 10:15:03', 1),
(10, 'rbac', 'role', 'R02', 'Staff_Controller', 'method', 'IN', 'index,read', 'admin', '2016-05-11 10:15:06', 1),
(11, 'rbac', 'role', 'R03', 'Staff_Controller', 'method', 'IN', '', 'admin', '2016-05-11 09:46:34', 1),
(12, 'rbac', 'role', 'R04', 'Staff_Controller', 'method', 'IN', '', 'admin', '2016-05-11 09:46:34', 1),
(13, 'rbac', 'role', 'R05', 'Staff_Controller', 'method', 'IN', '', 'admin', '2016-05-11 09:46:34', 1);

--
-- 表的結構 `permission_matrix`
--

CREATE TABLE IF NOT EXISTS `permission_matrix` (
  `uid` int(5) NOT NULL,
  `gid` varchar(10) NOT NULL,
  `rset` blob NOT NULL,
  `master` tinyint(1) NOT NULL DEFAULT '1',
  `creater` varchar(50) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uid`,`gid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 轉存資料表中的資料 `permission_matrix`
--

INSERT INTO `permission_matrix` (`uid`, `gid`, `rset`, `master`, `creater`, `create_time`) VALUES
(1, 'adm', 0x0406001200000000000300100006002000090030000c0040000f0050005230305230315230325230335230345230350202020202, 1, 'admin', '2016-05-13 09:42:40');



--
-- 表的結構 `permission_action`
--

CREATE TABLE IF NOT EXISTS `permission_action` (
  `ano` int(4) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `actype` varchar(10) NOT NULL,
  `controller` varchar(15) NOT NULL,
  `model` varchar(20) NOT NULL,
  `descrip` varchar(100) NOT NULL,
  `link_role` varchar(5) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `_keep` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ano`),
  KEY `link_role` (`link_role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;



--
-- 表的結構 `user_regist`
--

CREATE TABLE IF NOT EXISTS `user_regist` (
  `rno` int(5) NOT NULL AUTO_INCREMENT,
  `uid` int(5) NOT NULL,
  `reg_code` varchar(15) NOT NULL,
  `reg_state` varchar(10) NOT NULL,
  `effect_time` datetime NOT NULL,
  `active_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rno`),
  KEY `uid` (`uid`),
  KEY `reg_code` (`reg_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- 表的結構 `user_login`
--

CREATE TABLE IF NOT EXISTS `user_login` (
  `uno` int(5) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `user_pw` varchar(50) NOT NULL,
  `date_register` datetime NOT NULL,
  `date_open` datetime NOT NULL,
  `date_access` datetime NOT NULL,
  `ip_range` varchar(100) NOT NULL DEFAULT '0.0.0.0',
  `user_status` tinyint(2) NOT NULL,
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`uno`),
  UNIQUE KEY `User_ID` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=192 ;

--
-- 轉存資料表中的資料 `user_login`
--

INSERT INTO `user_login` (`uno`, `user_id`, `user_pw`, `date_register`, `date_open`, `date_access`, `ip_range`, `user_status`, `update_time`) VALUES
(1, 'admin', '2679aead131373c64780d5e2663c6ae9', '2013-07-01 00:00:00', '2013-07-01 00:00:00', '2020-12-31 00:00:00', '0.0.0.0', 5, '2016-05-10 07:09:14');


--
-- 表的結構 `user_info`
--

CREATE TABLE IF NOT EXISTS `user_info` (
  `uid` int(5) NOT NULL,
  `user_name` varchar(20) DEFAULT NULL COMMENT '姓名',
  `user_idno` varchar(30) NOT NULL COMMENT '證號',
  `user_staff` varchar(20) DEFAULT NULL COMMENT '組室科別',
  `user_organ` varchar(20) NOT NULL COMMENT '議會',
  `user_tel` varchar(50) DEFAULT NULL,
  `user_mail` varchar(50) DEFAULT NULL,
  `user_address` varchar(200) DEFAULT NULL,
  `user_note` text,
  `user_info` varchar(10) NOT NULL,
  `user_pri` int(2) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 轉存資料表中的資料 `user_info`
--

INSERT INTO `user_info` (`uid`, `user_name`, `user_idno`, `user_staff`, `user_organ`, `user_tel`, `user_mail`, `user_address`, `user_note`, `user_info`, `user_pri`) VALUES
(1, '管理者', '', '系統管理', '臺大數位人文研究中心', '02-33669847', 'hsiaoiling@ntu.edu.tw', '登登', '系統開發', 'ㄎㄎ', 3);

--
-- 表的結構 `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
  `ug_no` int(3) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `ug_code` varchar(10) NOT NULL,
  `ug_name` varchar(20) NOT NULL,
  `ug_info` varchar(100) NOT NULL,
  `ug_pri` tinyint(1) NOT NULL DEFAULT '0',
  `creater` varchar(50) NOT NULL,
  `create_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ug_no`),
  UNIQUE KEY `ug_code` (`ug_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- 轉存資料表中的資料 `user_group`
--

INSERT INTO `user_group` (`ug_no`, `ug_code`, `ug_name`, `ug_info`, `ug_pri`, `creater`, `create_dt`) VALUES
(001, 'adm', '管理群組', '系統管理者', 9, 'admin', '2015-11-22 19:29:35');

--
-- 表的結構 `user_ftpuser`
--

CREATE TABLE IF NOT EXISTS `user_ftpuser` (
  `id` int(5) unsigned NOT NULL,
  `userid` varchar(50) NOT NULL DEFAULT '',
  `passwd` varchar(100) NOT NULL DEFAULT '',
  `uid` smallint(6) NOT NULL DEFAULT '2001',
  `gid` smallint(6) NOT NULL DEFAULT '2001',
  `homedir` varchar(255) NOT NULL DEFAULT '',
  `shell` varchar(16) NOT NULL DEFAULT '/sbin/nologin',
  `count` int(11) NOT NULL DEFAULT '0',
  `accessed` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ProFTP user table';

--
-- 表的結構 `user_ftpgroup`
--

CREATE TABLE IF NOT EXISTS `user_ftpgroup` (
  `groupname` varchar(16) NOT NULL DEFAULT '',
  `gid` smallint(6) NOT NULL DEFAULT '2001',
  `members` varchar(16) NOT NULL DEFAULT '',
  KEY `groupname` (`groupname`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ProFTP group table';

--
-- 轉存資料表中的資料 `user_ftpgroup`
--

INSERT INTO `user_ftpgroup` (`groupname`, `gid`, `members`) VALUES
('ftpgroup', 2001, 'ftpuser');


--
-- 表的結構 `user_access`
--

CREATE TABLE IF NOT EXISTS `user_access` (
  `acc_no` int(10) NOT NULL AUTO_INCREMENT,
  `acc_key` varchar(10) NOT NULL,
  `acc_uno` int(5) NOT NULL,
  `acc_into` varchar(20) NOT NULL,
  `acc_ip` varchar(100) NOT NULL,
  `acc_from` text NOT NULL,
  `acc_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `acc_active` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`acc_no`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
