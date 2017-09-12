<?php

  /*
  *   Admin Tracking SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdTrack{



    /*** [---- 通用回報 ----] ***/
	public static function AP_USER_FEEDBACK_INITIAL(){
	  $SQL_String = "INSERT INTO system_feedback VALUES(NULL,:fb_from,:fb_group,:fb_type,:fb_url,:fb_content,:fb_preview,:user_account,:user_browse,:user_ip,:error_logs,'".date('Y-m-d H:i:s')."','{}',NULL);";
	  return $SQL_String;
	}
	
	public static function REGISTER_REPORT_DATA(){
	  $SQL_String = "INSERT INTO system_feedback VALUES(NULL,'".date('Y-m-d H:i:s')."',:RPTYPE,:STNO,:UID,'@System',:FIELD,:REPORT,'_REPORT','','','');";
	  return $SQL_String;
	}
	
	
	/*** [---- 系統回報管理 ----] ***/
	
	//-- Admin Report : Get Report List - User Self Report
	public static function ADMIN_REPORT_SELECT_USER_REPORTS(){
	  $SQL_String = "SELECT fno,fb_group,fb_type,fb_content,user_account,fb_treatment,fb_time,fb_status FROM system_feedback WHERE user_account=:user_id ORDER BY fb_status DESC , fno DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Report : Get Report List - Group Admin Get Group's Report
	public static function ADMIN_REPORT_SELECT_GROUP_REPORTS(){
	  $SQL_String = "SELECT fno,fb_group,fb_type,fb_content,user_account,fb_treatment,fb_time,fb_status FROM system_feedback WHERE fb_group=:group_code ORDER BY fb_status DESC , fno DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Report : Get Report List - Admin Get All Report's
	public static function ADMIN_REPORT_SELECT_SYSTEM_REPORTS(){
	  $SQL_String = "SELECT fno,fb_group,fb_type,fb_content,user_account,fb_treatment,fb_time,fb_status FROM system_feedback WHERE 1 ORDER BY fb_status DESC , fno DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Report : Get Report Data
	public static function ADMIN_REPORT_GET_REPORT_DATA(){
	  $SQL_String = "SELECT * FROM system_feedback WHERE fno=:fno;";
	  return $SQL_String;
	}
	
	//-- Admin Report : Set Report Message Data
	public static function ADMIN_REPORT_SET_REPORT_NOTE(){
	  $SQL_String = "UPDATE system_feedback SET fb_note=:fb_note WHERE fno=:fno;";
	  return $SQL_String;
	}
	
	//-- Admin Staff : Modify Staff Data
	public static function ADMIN_REPORT_UPDATE_REPORT_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE system_feedback SET ".join(',',$condition)." WHERE fno=:fno;";
	  return $SQL_String;
	}
	
	
	public static function ADMIN_REPORT_USER_FEEDBACK_INITIAL(){
	  $SQL_String = "INSERT INTO system_feedback VALUES(NULL,:fb_from,:fb_group,:fb_type,:fb_url,:fb_content,:fb_preview,:user_account,:user_browse,:user_ip,:fb_treatment,'".date('Y-m-d H:i:s')."','{}','_INITIAL',NULL);";
	  return $SQL_String;
	}
	
	
	
	
  }
  
  
?>	