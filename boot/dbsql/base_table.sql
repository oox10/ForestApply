--[ 系統基礎表格 ]
--
-- 表的結構 `logs_system`
--

CREATE TABLE IF NOT EXISTS `logs_system` (
  `slgno` int(10) unsigned zerofill NOT NULL AUTO_INCREMENT,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `acc_ip` varchar(100) NOT NULL,
  `acc_act` varchar(50) NOT NULL,
  `acc_url` text NOT NULL,
  `session` text NOT NULL,
  `request` longtext NOT NULL,
  `acc_from` text NOT NULL,
  `result` longtext NOT NULL,
  `agent` varchar(200) NOT NULL,
  PRIMARY KEY (`slgno`),
  KEY `acc_ip` (`acc_ip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;





