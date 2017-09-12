-- --------------------------------------------------------
-- 主機:                           127.0.0.1
-- 服務器版本:                        10.1.18-MariaDB - mariadb.org binary distribution
-- 服務器操作系統:                      Win64
-- HeidiSQL 版本:                  9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
-- 正在導出表  forestbooking_db.permission_action 的資料：20 rows
/*!40000 ALTER TABLE `permission_action` DISABLE KEYS */;
INSERT INTO `permission_action` (`ano`, `limitto`, `target`, `level`, `controller`, `model`, `permission`, `descrip`, `@time`, `_keep`) VALUES
	(0001, 'role', 'R00', 1, '*', '*', 1, '所有權限', '2017-06-05 11:17:46', 1),
	(0002, 'role', 'R01', 1, 'staff', '*', 1, '帳號管理-管理', '2017-06-05 11:17:46', 1),
	(0003, 'role', 'R09', 1, 'staff', 'index;read;save;startmail', 1, '帳號管理-一般', '2017-06-05 11:23:14', 1),
	(0004, 'role', 'R01', 1, 'tracking', '*', 1, '回報管理-管理', '2017-06-05 11:17:54', 1),
	(0005, 'role', 'R09', 1, 'tracking', 'index;read;messg;submit', 1, '回報管理-一般', '2017-06-05 11:23:18', 1),
	(0006, 'role', 'R01', 1, 'area', '*', 1, '區域管理-管理', '2017-06-05 11:20:37', 1),
	(0007, 'role', 'R02', 1, 'area', 'index;read;save;show;mask;dele;stop_save;stop_dele;addimg;delrefer;addblock;delblock;formconfig', 1, '區域管理-維護', '2017-06-05 11:20:57', 1),
	(0008, 'role', 'R01', 1, 'post', '*', 1, '公告管理-管理', '2017-06-05 11:24:53', 1),
	(0009, 'role', 'R02', 1, 'post', 'index;read;save;show;mask;dele', 1, '公告管理-維護', '2017-06-05 11:24:59', 1),
	(0010, 'role', 'R01', 1, 'mailer', '*', 1, '信件管理-管理', '2017-06-05 11:27:35', 1),
	(0011, 'role', 'R02', 1, 'mailer', 'index;read;save;sent;dele;regist', 1, '信件管理-維護', '2017-06-05 11:27:32', 1),
	(0012, 'role', 'R01', 1, 'booking', '*', 1, '申請管理-管理', '2017-06-06 11:37:03', 1),
	(0013, 'role', 'R02', 1, 'booking', 'index;search;read;save;review;setstage;attach;ticket', 1, '申請管理-維護', '2017-06-09 16:12:33', 1),
	(0014, 'role', 'R03', 1, 'booking', 'index;search;read', 1, '申請管理-調閱', '2017-06-06 11:39:39', 1),
	(0015, 'role', 'R04', 1, 'booking', 'index', 0, '申請管理-檢視', '2017-06-06 11:37:10', 1),
	(0016, 'role', 'R05', 1, 'booking', 'index', 0, '申請管理-外審', '2017-06-06 11:33:28', 1),
	(0017, 'role', 'R01', 1, 'lotto', '*', 1, '抽籤管理-管理', '2017-06-05 11:39:48', 1),
	(0018, 'role', 'R02', 1, 'lotto', 'index;read;built;active', 1, '抽籤管理-維護', '2017-06-05 11:39:54', 1),
	(0019, 'role', 'R01', 1, 'record', '*', 1, '統計管理-管理', '2017-06-05 11:40:03', 1),
	(0020, 'role', 'R09', 1, 'main', '*', 1, '首頁', '2017-06-05 11:40:03', 1);
/*!40000 ALTER TABLE `permission_action` ENABLE KEYS */;

-- 正在導出表  forestbooking_db.permission_role 的資料：7 rows
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` (`rno`, `name`, `descrip`, `rlevel`, `pri_limit`, `create_time`, `_keep`) VALUES
	('R00', '系統管理者', '執行所有系統功能', '', 9, '2015-11-23 01:38:19', 1),
	('R01', '區域管理者', '執行轄區所有功能', '', 1, '2017-06-05 18:51:29', 1),
	('R02', '申請管理者', '設定區域參數、申請進入審核', '', 1, '2017-06-05 18:51:32', 1),
	('R03', '警政單位', '檢視資料', '', 1, '2017-06-05 18:51:34', 1),
	('R04', '查驗人員', '查驗人員', '', 1, '2016-08-11 17:47:06', 1),
	('R05', '外審人員', '外部審查人', '', 1, '2017-06-05 18:51:24', 1),
	('R09', '系統成員', '可進入管理系統', '', 1, '2017-06-05 19:03:17', 1);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;

-- 正在導出表  forestbooking_db.permission_rule 的資料：22 rows
/*!40000 ALTER TABLE `permission_rule` DISABLE KEYS */;
INSERT INTO `permission_rule` (`prno`, `mode`, `limitto`, `target`, `table`, `field`, `operator`, `contents`, `creater`, `create_time`, `_open`, `_keep`) VALUES
	(1, 'acl', 'group', 'adm', 'data_book_catalog', 'SUBSTR(bookid,1,3)', 'IN', '001,002,003,006,008,009,011,012,013,014,015,016,019,021,022,024,024,028,028', 'admin', '2016-05-16 08:15:21', 0, 1),
	(2, 'acl', 'group', 'rcdh', 'permission_matrix', 'permission_matrix.gid', 'IN', 'rcdh', 'admin', '2016-05-11 09:50:44', 0, 1),
	(3, 'acl', 'group', 'rcdh', 'data_project', 'data_project.gid', 'IN', 'rcdh,tpc', 'admin', '2016-05-11 09:28:46', 0, 1),
	(4, 'acl', 'group', 'rcdh', 'format_project', 'gid', 'IN', 'rcdh,tpc', 'admin', '2016-05-11 09:28:49', 0, 1),
	(5, 'acl', 'group', 'tpc', 'permission_matrix', 'gid', 'IN', 'tpc', 'admin', '2016-05-11 09:28:52', 0, 1),
	(6, 'acl', 'group', 'adm', 'user_group', 'ug_pri', '>', '1', 'admin', '2016-08-09 17:27:10', 0, 1),
	(7, 'acl', 'group', '*', 'user_group', 'ug_code', '=', '#SELF', 'admin', '2016-08-11 16:28:01', 0, 1),
	(8, 'acl', 'group', 'adm', 'system_post', 'post_keep', '=', '1', 'admin', '2016-08-09 17:36:32', 0, 1),
	(9, 'acl', 'group', '*', 'system_post', 'edit_group', '=', '#SELF', 'admin', '2016-08-11 16:28:03', 0, 1),
	(10, 'acl', 'group', 'adm', 'area_main', 'area_main._keep', '=', '1', 'admin', '2017-06-09 00:02:56', 1, 1),
	(11, 'acl', 'group', 'forest', 'area_main', 'area_main._keep', '=', '1', 'admin', '2017-06-09 00:02:54', 1, 1),
	(12, 'acl', 'group', '*', 'area_main', 'owner', '=', '#SELF', 'admin', '2017-06-09 00:02:52', 1, 1),
	(13, 'rbac', 'role', 'R0x', 'Staff_Controller', 'method', 'IN', 'index,read,save,dele,startmail,gmember,gpadd,gpdef', 'admin', '2017-06-09 00:03:23', 0, 1),
	(14, 'rbac', 'role', 'R00', 'admin_book.html5tpl.php', 'review', 'UI', '1', 'admin', '2017-06-07 21:44:50', 1, 1),
	(15, 'rbac', 'role', 'R01', 'admin_book.html5tpl.php', 'review', 'UI', '1', 'admin', '2017-06-07 21:44:51', 1, 1),
	(16, 'rbac', 'role', 'R02', 'admin_book.html5tpl.php', 'review', 'UI', '1', 'admin', '2017-06-07 21:44:52', 1, 1),
	(17, 'rbac', 'role', 'R00', 'admin_staff.html5tpl.php', 'roleset', 'UI', '1', 'admin', '2017-06-07 21:43:18', 1, 1),
	(18, 'rbac', 'role', 'R00', 'admin_staff.html5tpl.php', 'act_set_gmember', 'UI', '1', 'admin', '2017-06-07 21:43:45', 1, 1),
	(19, 'rbac', 'role', 'R00', 'admin_staff.html5tpl.php', 'statusset', 'UI', '1', 'admin', '2017-06-07 21:43:46', 1, 1),
	(20, 'rbac', 'role', 'R01', 'admin_staff.html5tpl.php', 'roleset', 'UI', '1', 'admin', '2017-06-07 21:44:10', 1, 1),
	(21, 'rbac', 'role', 'R01', 'admin_staff.html5tpl.php', 'act_set_gmember', 'UI', '1', 'admin', '2017-06-07 21:44:11', 1, 1),
	(22, 'rbac', 'role', 'R01', 'admin_staff.html5tpl.php', 'statusset', 'UI', '1', 'admin', '2017-06-07 21:44:13', 1, 1),
	(30, 'rbac', 'role', 'R01', 'admin_lotto.html5tpl.php', 'act_lotto_active', 'UI', '1', 'admin', '2017-06-07 21:44:13', 1, 1),
	(29, 'rbac', 'role', 'R00', 'admin_lotto.html5tpl.php', 'act_lotto_active', 'UI', '1', 'admin', '2017-06-07 21:44:13', 1, 1);
/*!40000 ALTER TABLE `permission_rule` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
