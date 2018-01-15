<?php
 /*
  *   Client Page SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_Client{
	  
	
	
	/***-- Admin Post Permission SQL --***/
	
	//-- Admin Post : Access Check
	public static function CHECK_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	
	/***-- Client Data SQL --***/  
	
	//-- Client Post :  get post list
	public static function INDEX_GET_POST_LIST(){
	  $SQL_String = "SELECT pno,post_type,post_from,post_level,post_time_start,post_title,post_content FROM system_post WHERE post_to='申請系統' AND post_display=1 AND post_keep=1 AND ( (NOW() BETWEEN post_time_start AND post_time_end ) OR post_level=4 ) ORDER BY post_level DESC,post_time_start DESC,pno DESC;";
	  return $SQL_String;
	}  
	
	
	//-- Client Page :  get page contents 
	public static function INDEX_GET_PAGE_CONTENT(){
	  $SQL_String = "SELECT page_title,page_content FROM system_pages WHERE 1;";
	  return $SQL_String;
	}

	
	//-- Client Landing :  get area contact information
	public static function INDEX_CONTECT_ORGAN(){
	  $SQL_String = "SELECT ug_code,ug_name FROM user_group WHERE ug_pri=3 ORDER BY ug_no ASC;";
	  return $SQL_String;
	}
	
	
	//-- Client Landing :  get area contact information
	public static function GROUP_CONTECT_ORGAN(){
	  $SQL_String = "SELECT ug_code,ug_name FROM user_group WHERE ug_pri>=3 AND ug_code=:ug_code;";
	  return $SQL_String;
	}
	
	//-- Client Landing :  get area contact information
	public static function GET_ORGAN_CONTECT_INFO(){
	  $SQL_String = "SELECT user_mail,user_tel,user_organ FROM (SELECT uid,gid, COLUMN_GET(rset,'R01' as char) AS R01 FROM permission_matrix WHERE master=1) AS PM LEFT JOIN user_info ON PM.uid=user_info.uid WHERE gid=:gid AND (R01=1);";
	  return $SQL_String;
	}
	
	//-- Client check :  search application by code and mail  
	public static function SEARCH_USER_APPLICATION(){
	  $SQL_String = "SELECT abno,apply_code FROM area_booking WHERE apply_code = :apply_code AND applicant_mail = :applicant_mail AND _stage > 0 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Client Area :  get open area list 
	public static function INDEX_GET_AREA_LIST(){
	  $SQL_String = "SELECT area_code,area_type,area_name,owner FROM area_main WHERE _open=1 AND _keep=1 ORDER BY ano ASC;";
	  return $SQL_String;
	} 
	
	//-- Client Area :  get open area list 
	public static function INDEX_GET_GROUP_AREA(){
	  $SQL_String = "SELECT area_code,area_type,area_name,owner FROM area_main WHERE owner=:owner AND _open=1 AND _keep=1 ORDER BY ano ASC;";
	  return $SQL_String;
	} 
	
	
	
	//-- Client Area :  get target area data 
	public static function GET_TARGET_AREA_DATA(){
	  $SQL_String = "SELECT * FROM area_main WHERE area_code=:area_code AND _open=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	public static function GET_TARGET_AREA_BLOCK(){
	  $SQL_String = "SELECT * FROM area_block WHERE am_id=:amid AND _keep=1 ORDER BY ab_id ASC";
	  return $SQL_String;
	}
	
	
	//-- Client Area :  get target area data stop date range
	public static function GET_TARGET_AREA_STOP(){
	  $SQL_String = "SELECT date_start,date_end,effect,reason FROM area_stop WHERE date_end >= :date_now AND am_id=:amid AND _active=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  get target area apply person
	public static function GET_TARGET_AREA_APPLIED(){
	  $SQL_String = "SELECT date_enter,date_exit,member_count FROM area_booking WHERE am_id=:amid AND ( :apply_d_start <= date_exit  OR  :apply_d_end >= date_enter ) AND _status NOT IN('取消申請','申請取消');"; //'審查未過','申請註銷','備取失敗',
	  return $SQL_String;
	}
	
	//-- Client Area :  get target area woner group and tel
	public static function GET_AREA_OWNER_GROUP_AND_CONCATER(){
	  $SQL_String = "SELECT ug_name,user_tel,user_mail,column_json(rset) AS role_conf FROM user_group LEFT JOIN permission_matrix ON gid=ug_code LEFT JOIN user_info ON user_info.uid = permission_matrix.uid WHERE gid=:owner AND master=1;";
	  return $SQL_String;
	}
	
	
	
	
	
	//-- Client Area :  search user apply history last 7
	public static function SEARCH_APPLY_RECORD(){
	  $SQL_String = "SELECT area_booking.*,area_name FROM area_booking LEFT JOIN area_main ON ano=am_id WHERE applicant_name=:applicant_name AND applicant_mail=:applicant_mail AND applicant_id=:applicant_id AND _status!='申請進入' ORDER BY apply_date DESC;";
	  return $SQL_String;
	}
	
    
	//-- Client Area :  initial apply account
	public static function INITIAL_APPLY_ACCOUNT(){
	  $SQL_String = "INSERT INTO area_booking VALUES(NULL,:am_id,:apply_code,:apply_date,:applicant_name,:applicant_mail,:applicant_id,:applicant_info,".
	                                                 "'','0000-00-00','0000-00-00','[]',:member_list,1,'',".
	                                                 "0,'0000-00-00',0,0,0,'{\"client\":[[],[],[],[],[],[]],\"review\":[[],[],[],[],[],[]],\"admin\":[[],[],[],[],[],[]]}','申請進入','','".date('Y-m-d H:i:s')."',NULL,'','',1) ".
													 "ON DUPLICATE KEY UPDATE applicant_name=:applicant_name,applicant_mail=:applicant_mail,applicant_id=:applicant_id,applicant_info=:applicant_info".";";
	  return $SQL_String;
	}
    
	//-- Client Area :  get application data // 取得要輸出的資料，後台用
	public static function GET_APPLICATION_DATA(){
	  $SQL_String = "SELECT * FROM area_booking WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  get applicant data // 取得要輸出的資料，前台用
	public static function GET_APPLICATION_META(){
	  $SQL_String = "SELECT area_booking.*,area_code,area_type,area_name,cancel_day FROM area_booking LEFT JOIN area_main ON ano=am_id WHERE apply_code=:apply_code AND area_booking._keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  update apply data
	public static function CHECK_APPLY_DATE_IS_ALONE(){
	  $SQL_String = "SELECT count(*) FROM area_booking WHERE am_id=:am_id AND applicant_name=:applicant_name AND applicant_mail=:applicant_mail AND applicant_id=:applicant_id AND date_enter=:date_enter AND date_exit=:date_exit AND _stage<5 AND apply_code!=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  update apply data
	public static function UPDATE_APPLY_FORM(){
	  $SQL_String = "UPDATE area_booking SET am_id=:areaid , apply_reason=:reason ,date_enter=:date_enter,date_exit=:date_exit  ,apply_form=:application,_ballot=:ballot,_ballot_date=:ballot_date WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Client Area :  update apply members
	public static function GET_AREA_APPLIED_MEMBER(){
	  $SQL_String = "SELECT apply_code,apply_date,applicant_name,member FROM area_booking WHERE am_id=:am_id AND apply_code!=:apply_code AND date_enter <=:check_date AND date_exit >=:check_date AND _stage<5 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
	//-- Client Area :  update apply members
	public static function UPDATE_APPLY_MEMBER(){
	  $SQL_String = "UPDATE area_booking SET member=:member , member_count=:countmbr  WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  update apply status
	public static function UPDATE_APPLY_STATUS(){
	  $SQL_String = "UPDATE area_booking SET _status=:status,_progres=:progres WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Client Area :  update apply stage
	public static function UPDATE_APPLY_STAGE(){
	  $SQL_String = "UPDATE area_booking SET _stage=:stage WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Client : cancel user booking
	public static function CANCEL_AREA_BOOKING(){
	  $SQL_String = "UPDATE area_booking SET _status=:status,_progres=:progres,_stage=:stage,_final=:final WHERE apply_code=:apply_code AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Client Post:  get post user select 
	public static function GET_CLIENT_POST_TARGET(){
	  $SQL_String = "SELECT pno,post_type,post_from,post_level,post_time_start,post_title,post_content,post_hits FROM system_post WHERE post_to IN('申請系統','所有系統') AND post_display=1 AND post_keep=1 AND ( (NOW() BETWEEN post_time_start AND post_time_end ) OR post_level=4 ) AND pno=:pno;";
	  return $SQL_String;
	}
	
	//-- Client Post:  update post hits 
	public static function CLIENT_POST_HITS(){
	  $SQL_String = "UPDATE system_post SET post_hits=(post_hits+1) WHERE pno=:pno;";
	  return $SQL_String;
	}
	
	
	//-- Client Applied : search area lotto data
	public static function GET_LOTTO_TARGET_DATA(){
	  $SQL_String = "SELECT aid,date_tolot,time_lotto,lotto_pool,lotto_num,logs_process,_loted FROM booking_lotto WHERE aid=:aid AND date_enter=:date_enter AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
  }

?>