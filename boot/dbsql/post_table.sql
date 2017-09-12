-- [ 公告管理表 ]

--
-- 表的結構 `system_post`
--

CREATE TABLE IF NOT EXISTS `system_post` (
  `pno` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `post_type` enum('系統公告','區域公告','緊急通告') NOT NULL,
  `post_from` varchar(50) NOT NULL,
  `post_to` enum('申請系統','管理系統') NOT NULL,
  `post_target` varchar(50) NOT NULL,
  `post_level` tinyint(1) NOT NULL,
  `post_time_start` datetime NOT NULL,
  `post_time_end` datetime NOT NULL,
  `post_title` varchar(200) NOT NULL,
  `post_content` text NOT NULL,
  `post_refer` varchar(100) NOT NULL,
  `post_display` tinyint(1) NOT NULL DEFAULT '1',
  `post_temp` varchar(50) NOT NULL,
  `edit_user` varchar(50) NOT NULL,
  `edit_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `edit_group` varchar(10) NOT NULL,
  `post_hits` int(5) NOT NULL DEFAULT '0',
  `post_keep` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`pno`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
