<?php

  /*
  *   [RCDH10 Admin Module] - Account Sql Library 
  *   System Account & Login SQL SET
  *
  *   2016-08-01 ed.  
  */
  
  class SQL_Account{
    
	/* [ System Execute function Set ] */ 	
	
	
    /***---- 帳號登入 ----***/
    
	//-- 系統登入檢查
	public static function SELECT_ACCOUNT_LOGIN_DATA(){
	  $SQL_String = "SELECT uno,user_mail,user_pw,date_open,date_access,user_status,ip_range FROM user_login LEFT JOIN user_info ON uid=uno WHERE user_id=:user_id;";
	  return $SQL_String;
	}
	
	//-- 註冊登入序號
	public static function INSERT_ACCOUNT_LOGIN_CODE(){
	  $SQL_String = "INSERT INTO user_access VALUES(NULL,:acc_key,:acc_uno,:acc_into,:acc_ip,:acc_from,NULL,'');";
	  return $SQL_String;
	}

	//-- 檢查使用者email
	public static function CHECK_ACCOUNT_REGIST_EMAIL(){
	  $SQL_String = "SELECT uno,user_id,user_mail,user_idno,user_status FROM user_info LEFT JOIN user_login ON uid=uno WHERE user_id=:user_id AND user_mail=:user_mail;";
	  return $SQL_String;
	}
	
	
	//-- 檢查使用者群組是否可進入後台
	public static function CHECK_ACCOUNT_GROUP_TOBACK(){
	  $SQL_String = "SELECT count(*) FROM permission_matrix LEFT JOIN user_group ON ug_code=gid WHERE uid=:uid AND back=1;";
	  return $SQL_String;
	}
	
	
	//-- 變更使用者狀態為可重設
	public static function SET_ACCOUNT_STATUS_REPASSWORD(){
	  $SQL_String = "UPDATE user_login SET user_status=4 WHERE uno=:uno;";
	  return $SQL_String;
	}
	
	
	//-- 系統登入KEY 檢查
	public static function CHECK_ACCOUNT_LOGIN_KEY(){
	  $SQL_String = "SELECT acc_uno,acc_into,acc_ip,acc_from,acc_time,uno,user_id FROM user_access LEFT JOIN user_login ON acc_uno=uno WHERE acc_key=:acc_key AND acc_active='';";
	  return $SQL_String;
	}
	
	//-- Admin Login : Cancle Login Key
	public static function CANCEL_ACCOUNT_LOGIN_KEY(){
	  $SQL_String = "UPDATE user_access SET acc_active=:acc_active WHERE acc_key=:acc_key;";
	  return $SQL_String;
	}
	
	
	/***---- User Account Object Initial ----***/
	
	
	//-- 查詢使用者資料
	public static function GET_ACCOUNT_INFO_DATA(){
	  $SQL_String = "SELECT user_name,user_staff,user_organ,user_tel,user_mail,user_pri FROM user_info WHERE uid=:uid;";
	  return $SQL_String;
	}
	
	//-- 取得使用者群組&角色
	public static function GET_ACCOUNT_GROUPS(){
	  $SQL_String = "SELECT gid,master,ug_no,ug_name,ug_info,ug_pri,COLUMN_JSON(rset) AS roles,back,filter FROM permission_matrix LEFT JOIN user_group ON ug_code=gid WHERE uid=:uid ORDER BY ug_pri DESC,ug_no ASC;";
	  return $SQL_String;
	}
	
	//-- 取得使用者群組資源篩選條件
	public static function GET_GROUPS_ACCESS_RULES(){
	  $SQL_String = "SELECT * FROM permission_rule WHERE mode='acl' AND ((limitto='group' AND (target=:gid OR target='*')) OR ( limitto='user' AND target=:user)) AND _open=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- 取得腳色介面設定
	public static function GET_GROUPS_ROLE_INTERFACE_CONFIG(){
	  $SQL_String = "SELECT * FROM permission_rule WHERE mode=:mode AND limitto=:limit AND target=:target AND operator='UI' AND _keep=1 ORDER BY permission_rule.table ASC;";
	  return $SQL_String;
	}
	
	
	
	//-- 取得使用者群組角色動作
	public static function GET_GROUPS_ROLE_ACTION(){
	  $SQL_String = "SELECT * FROM permission_action WHERE (limitto='role' AND target=:role AND level <=:level ) AND _keep=1 ORDER BY level ASC;";
	  return $SQL_String;
	}
	
	//-- 取得使用者個人腳色動作
	public static function GET_GROUPS_USER_ACTION(){
	  $SQL_String = "SELECT * FROM permission_action WHERE (limitto='user' AND target=:user) AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
	
	
	
	/***---- 帳號註冊 ----***/
	
	// 取得群組列表
	public static function SELECT_REGIST_GROUP_LIST(){
	  $SQL_String = "SELECT ug_code,ug_name,ug_info FROM user_group WHERE ug_pri < 8 AND ug_pri > 0 AND back=1 ORDER BY ug_pri DESC , ug_info ASC , ug_no ASC;";
	  return $SQL_String;
	}
	
	// 檢查 email 是否已被註冊
	public static function CHECK_REGISTER_MAIL_USED(){
	  $SQL_String = "SELECT count(*) FROM user_login LEFT JOIN user_info ON uno = uid WHERE user_mail=:user_mail GROUP BY user_mail;";
	  return $SQL_String;
	}
	
	// 建立新成員
	public static function INSERT_NEW_USER_ACCOUNT(){
	  $SQL_String = "INSERT INTO user_login VALUES(NULL,:uname,:upass,:date_register,:date_open,:date_access,'0.0.0.0',:ustate,NULL);";
	  return $SQL_String;
	}
	
	// 填寫新成員資料
	public static function INSERT_NEW_USER_INFOMATION(){
	  $SQL_String = "INSERT INTO user_info VALUES(:uid,:user_name,:user_idno,:user_staff,:user_organ,:user_tel,:user_mail,:user_address,'','',:user_pri);";
	  return $SQL_String;
	}
	
	// 註冊系統FTP帳號
    public static function INSERT_NEW_FTP_ACCOUNT(){
	  $SQL_String = "INSERT INTO user_ftpuser VALUES (:uno, :user_account, '', 2001, 2001, :homedir, '/sbin/nologin', 0, '', '');";
	  return $SQL_String;
	}
	
	// 註冊成員加入申請群組
	public static function INSERT_GROUP_MEMBER(){
	  $SQL_String = "INSERT INTO permission_matrix VALUES(:uno,:gno,COLUMN_CREATE('R00', 0, 'R01', 0, 'R02', 0, 'R03',0, 'R04',0, 'R05',0, 'R09',1),:master,'',:creater,NULL);";
	  return $SQL_String;
	}
	
	// 註冊帳號啟動碼
	public static function INSERT_ACCOUNT_REGIST_CODE(){
	  $SQL_String = "INSERT INTO user_regist VALUES(NULL,:uno,:reg_code,:reg_state,:effect_time,'0000-00-00 00:00:00');";
	  return $SQL_String;
	}
    
	
	// 查詢 user_regist 確認 reg_code 是否合法
	public static function SELECT_REGISTCODE_BY_CODE(){
	  $SQL_String = "SELECT * FROM user_regist LEFT JOIN user_login ON uno = uid WHERE reg_code=:reg_code AND effect_time > :now;";
	  return $SQL_String;
	}
	
	// 使用者帳號啟動並更新密碼
	public static function ACCOUNT_START_AND_SET_PASSWORD(){
	  $SQL_String = "UPDATE user_login LEFT JOIN user_info ON uno=user_info.uid LEFT JOIN user_regist ON uno = user_regist.uid LEFT JOIN user_ftpuser ON id=uno SET user_pw=:user_pass,user_status=:user_status,reg_state=:reg_state,user_pri=3,passwd=PASSWORD(:password) WHERE uno=:uno AND reg_code=:reg_code;";
	  return $SQL_String;
	}
	
	// 查詢 user_regist 確認 reg_doc_code 是否合法
	public static function CHECK_REGISTDOCCODE_BY_CODE(){
	  $SQL_String = "SELECT * FROM user_regist LEFT JOIN user_info ON user_regist.uid = user_info.uid WHERE reg_state='_REGDOC' AND reg_code=:reg_code AND effect_time > :now;";
	  return $SQL_String;
	}
	
	
	
	
  }
  
?>