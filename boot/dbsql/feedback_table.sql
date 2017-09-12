-- [ 回報管理 ]
--
-- 表的結構 `system_feedback`
--

CREATE TABLE IF NOT EXISTS `system_feedback` (
  `fno` int(5) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `fb_from` varchar(50) NOT NULL DEFAULT '1',
  `fb_group` varchar(10) NOT NULL,
  `fb_type` varchar(50) NOT NULL,
  `fb_url` varchar(255) NOT NULL,
  `fb_content` text NOT NULL,
  `fb_preview` longblob NOT NULL,
  `user_account` varchar(50) NOT NULL,
  `user_browse` varchar(200) NOT NULL,
  `user_ip` varchar(50) NOT NULL,
  `fb_treatment` text NOT NULL,
  `fb_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fb_note` longtext NOT NULL,
  `fb_status` varchar(20) NOT NULL DEFAULT '_INITIAL',
  `@time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`fno`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
