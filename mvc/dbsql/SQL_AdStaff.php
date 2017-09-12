<?php
  /*
  *   Admin Staff SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdStaff{
	
   
	/***-- Admin Staff SQL --***/
	
	//-- Admin Staff : Get Roles List
	public static function ADMIN_STAFF_GET_ROLES_LIST(){
	  $SQL_String = "SELECT * FROM permission_role WHERE _keep=1 AND pri_limit < 8;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Group
	public static function ADMIN_STAFF_SELECT_USER_GROUP_MAP(){
	  $SQL_String = "SELECT uid AS puid,gid,ug_name FROM permission_matrix LEFT JOIN user_group ON gid=ug_code WHERE master=1;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Promission Field
	public static function ADMIN_STAFF_GET_PERMISSION_FIELD(){
	  $SQL_String = "SHOW FULL COLUMNS FROM permission_table WHERE Comment !='';";
	  return $SQL_String;
	}
	
	public static function ADMIN_STAFF_SELECT_GROUP_PERMISSION_MAP(){
	  $SQL_String = "SELECT * FROM permission_role LEFT JOIN permission_table ON rno= effect_target WHERE pri_limit < 8 AND gid=:group_code ORDER BY rno ASC;";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Staff : Get Staff List
	public static function ADMIN_STAFF_SELECT_ALL_STAFF(){
	  $SQL_String = "SELECT uno,user_id,user_status,date_open,date_access,user_name,user_mail,user_staff,user_organ,user_tel FROM  permission_matrix LEFT JOIN user_login ON uid=uno LEFT JOIN user_info ON user_info.uid=user_login.uno WHERE master=1 AND uno IS NOT NULL ORDER BY uno DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Account
	public static function ADMIN_STAFF_SELECT_TARGET_STAFF(){
	  $SQL_String = "SELECT uno,user_id,user_status,date_open,date_access,user_name,user_mail,user_staff,user_organ,user_tel FROM  permission_matrix LEFT JOIN user_login ON uid=uno LEFT JOIN user_info ON user_info.uid=user_login.uno WHERE master=1 AND uno =:uno;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Output
	public static function ADMIN_STAFF_SELECT_OUTPUT_STAFF(){
	  $SQL_String = "SELECT user_id, user_name, user_organ, user_tel, user_mail, user_education, user_age, user_note, date_register,date_access,user_status FROM  user_login LEFT JOIN user_info ON uid=uno WHERE 1;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Access Check
	public static function ADMIN_STAFF_CHECK_STAFF_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Admin Data
	public static function ADMIN_STAFF_GET_STAFF_ADMIN_DATA(){
	  $SQL_String = "SELECT uno,user_id,date_open,date_access,ip_range,user_status,user_idno,user_name,user_staff,user_organ,user_tel,user_mail FROM user_login LEFT JOIN user_info ON uid=uno WHERE uno=:uno AND user_status >= 0;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Group Role  // 取得帳號主要群組的角色
	public static function ADMIN_STAFF_GET_STAFF_GROUP_ROLES(){
	  $SQL_String = "SELECT COLUMN_JSON(rset) AS role_json FROM permission_matrix WHERE uid=:uid AND gid=:gid AND master=1;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Staff Groups      // 取得帳號主要群組與相關群組
	public static function ADMIN_STAFF_GET_STAFF_GROUP_OTHER(){
	  $SQL_String = "SELECT gid,ug_code,ug_name,master FROM permission_matrix LEFT JOIN user_group ON gid=ug_code WHERE uid=:uid ORDER BY master DESC,gid ASC;";
	  return $SQL_String;
	}
	
	
	
	
	//-- Admin Staff : Modify Staff Data
	public static function ADMIN_STAFF_UPDATE_STAFF_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE user_login LEFT JOIN user_info ON uid=uno SET ".join(',',$condition)." WHERE uno=:uno;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : New Staff Login Data
	public static function ADMIN_STAFF_INSERT_USER_LOGIN(){
	  $SQL_String = "INSERT INTO user_login VALUES( NULL , :user_id , '' , '".date('Y-m-d H:i:s')."' , :date_open , :date_access ,:ip_range, 4 ,NULL );";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Insert Staff Information Data
	public static function ADMIN_STAFF_INSERT_USER_INFO(){
	  $SQL_String = "INSERT INTO user_info VALUES( :uid , :user_name , :user_idno , :user_staff , :user_organ , :user_tel , :user_mail, :user_address ,:user_note , :user_info  , :user_pri );";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Insert Group Member
	public static function INSERT_GROUP_MEMBER($RoleField="COLUMN_CREATE('R00', 0, 'R01', 0, 'R02', 0, 'R03',0, 'R04',0, 'R05', 0, 'R09',1)"){
	  $SQL_String = "INSERT INTO permission_matrix VALUES(:uno,:gno,".$RoleField.",:master,'',:creater,NULL);";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Staff : Update User Groups Roles
	public static function ADMIN_STAFF_UPDATE_STAFF_ROLES($roles=array()){
	  $condition = array();
	  foreach($roles as $r){
	    $condition[] = "'".$r."',:".$r;
	  }	
	  $SQL_String = "INSERT INTO permission_matrix VALUES( :uid , :gid , COLUMN_CREATE('R00',0 ,".join(',',$condition).") , :master,'' ,:user , NULL ) ON DUPLICATE KEY UPDATE rset=COLUMN_ADD(rset, ".join(',',$condition).") , creater=:user;";
	  //COLUMN_CREATE
	  //COLUMN_ADD(dyncol_blob, "column_name", "value") WHERE id=1;
	  return $SQL_String;
	}
	
	
	//-- Admin Staff : Delete Staff Login Data
	public static function ADMIN_STAFF_DELETE_STAFF_LOGIN(){
	  $SQL_String = "DELETE FROM user_login WHERE uno = :uno;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : 註冊帳號啟動碼
	public static function ADMIN_STAFF_LOGIN_REGISTER_REPASSWORD_CODE(){
	  $SQL_String = "INSERT INTO user_regist VALUES(NULL,:uno,:reg_code,:reg_state,:effect_time,'0000-00-00 00:00:00');";
	  return $SQL_String;
	}	

	//-- Admin Staff RePass Initial : 查詢 user_regist 確認 reg_code 是否合法
	public static function STAFF_LOGIN_REGIST_CODE_CHECK(){
	  $SQL_String = "SELECT uid,user_id,date_register,user_status,reg_state,effect_time FROM user_regist LEFT JOIN user_login ON uid = uno WHERE reg_code=:reg_code AND effect_time > :now;";
	  return $SQL_String;
	}
	
	//-- Client Login : 執行密碼設定以及帳號開通
	public static function STAFF_LOGIN_ACCOUNT_START(){
	  $SQL_String = "UPDATE user_regist LEFT JOIN user_login ON uid=uno SET user_pw=:passwd,user_status=:status,reg_state=:reg_state,active_time=:actie_time WHERE reg_code=:reg_code AND uid=:uid AND effect_time > :now;";
	  return $SQL_String;
	}
    
	
	
	/***-- Admin Group Member Set Function --***/	
	
	//-- Admin Staff : Get Group List
	public static function SELECT_GROUP_LIST(){
	  $SQL_String = "SELECT ug_no,ug_code,ug_name,ug_info FROM user_group WHERE 1 ORDER BY ug_pri DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Get Group Members
	public static function SELECT_GROUP_MEMBER(){
	  $SQL_String = "SELECT gid,uno,user_id,user_name,user_organ,COLUMN_JSON(rset) AS role_json ,master,filter FROM permission_matrix LEFT JOIN user_login ON permission_matrix.uid=uno LEFT JOIN user_info ON uno=user_info.uid WHERE (uno IS NOT NULL AND uno > 0);";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Check Member Account
	public static function CHECK_MEMBER_ACCOUNT(){
	  $SQL_String = "SELECT uno,user_id,user_name,user_organ FROM user_login LEFT JOIN user_info ON uno = uid WHERE (uno=:uno OR user_id=:user_id OR user_mail=:user_mail OR user_name=:user_name) AND (uno IS NOT NULL AND uno > 0);";
	  return $SQL_String;
	}
	
    //-- Admin Staff : Delete Member From permission maxtrix
	public static function DELETE_MEMBER_FROM_GROUP(){
	  $SQL_String = "DELETE FROM permission_matrix WHERE uid=:uid AND gid=:gid AND master!=1;";
	  return $SQL_String;
	}

	
	//-- Admin Staff : Renew Group Meta
	public static function RENEW_GROUP_META(){
	  $SQL_String = "INSERT INTO user_group VALUES(NULL,:ug_code,:ug_name,:ug_info,3,1,:creater,null) ON DUPLICATE KEY UPDATE ug_name=:ug_name,ug_info=:ug_info,creater=:creater;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Delete Group Meta
	public static function DELETE_GROUP_META(){
	  $SQL_String = "DELETE FROM user_group WHERE ug_code=:ug_code;";
	  return $SQL_String;
	}
	
	
	
  }
  
  
?>