<?php
  
  /*
  *   [RCDH10 Admin Module] - Mailer Sql Library 
  *   System Mailer SQL SET
  *
  *   2017-03-08 ed.  
  */
  
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdMailer{
	  
	
	
	/***-- Admin Mailer Permission SQL --***/
	
	//-- Admin Mailer : Access Check
	public static function CHECK_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	
	/***-- Admin Mailer SQL --***/  
	
	
	//-- Admin Collect : 計算資料顯示總筆數
	public static function SELECT_COUNT_RECORD( $Condition = array(1) ){
	  $SQL_String = "SELECT count(*) ".
	                " FROM  system_mailer ".
					" WHERE _keep=1 ".join(' AND ',$Condition);
	  return $SQL_String;
	}
	
	//-- Admin Collect : 取得資料顯示列表
	public static function SELECT_SEARCH_RECORD( $Condition = array(1), $OrderBySet=' ORDER BY import_id DESC,citno ASC' ){
	  $SQL_String = "SELECT smno,mail_type,mail_to,mail_title,_status_code,_mail_date,_active_time ".
	                " FROM system_mailer WHERE _keep=1 ".join(' AND ',$Condition).
					" ".$OrderBySet." LIMIT :page_start,:page_length";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Mailer :  get mail job list 
	public static function GET_MAILER_JOBS(){
	  $SQL_String = "SELECT  FROM system_mailer WHERE _keep=1 ORDER BY _status_code ASC,_mail_date DESC,smno ASC;";
	  return $SQL_String;
	}  
	
	//-- Admin Mailer :  get mail job list filter by status
	public static function GET_MAILER_STATUS_JOBS(){
	  $SQL_String = "SELECT smno,mail_type,mail_to,mail_title,_status_code,_mail_date,_active_time FROM system_mailer WHERE _status_code=:status AND _keep=1 ORDER BY _status_code ASC,_mail_date DESC,smno ASC;";
	  return $SQL_String;
	}  
	
	
    
	//-- Admin Mailer :  get post user list 
	public static function GET_MAIL_DATA(){
	  $SQL_String = "SELECT * FROM system_mailer WHERE smno=:smno AND _keep=1 ;";
	  return $SQL_String;
	}  
	
	//-- Admin Mailer : Modify Mailer Data
	public static function UPDATE_MAIL_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE system_mailer SET ".join(',',$condition)." WHERE smno=:smno;";
	  return $SQL_String;
	}
	
	
	//-- Admin Apply : Regist Apply mail alert job
	public static function REGIST_MAIL_JOB(){
	  $SQL_String = "INSERT INTO system_mailer VALUES(NULL,:mail_type,:mail_from,:mail_to,:mail_title,:mail_content,0,:creator,'".date('c')."',:editor,'0000-00-00 00:00:00',:mail_date,'0000-00-00 00:00:00','',:active_logs,1);";
	  return $SQL_String;
	}
	
	
	
	
	
  }

?>