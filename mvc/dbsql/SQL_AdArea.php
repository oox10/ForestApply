<?php
 /*
  *   Admin Area SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdArea{
	  
	
	/***-- Admin Area Permission SQL --***/
	
	//-- Admin Post : Access Check
	public static function CHECK_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	
	
	/***-- Admin Area SQL --***/  
	
	//-- Admin Area :  get area_main table descrip
	public static function ADMIN_AREA_GET_AREA_TABLE(){
	  $SQL_String = "DESCRIBE area_main;";
	  return $SQL_String;
	}
	
	//-- Admin Area :  get user_group list
	public static function ADMIN_POST_GET_POST_GROUPS(){
	  $SQL_String = "SELECT ug_code,ug_name,ug_pri FROM user_group WHERE 1 ORDER BY ug_pri DESC,ug_no ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Area :  get area list 
	public static function ADMIN_AREA_GET_AREA_LIST(){
	  $SQL_String = "SELECT * FROM area_main WHERE _keep=1 ORDER BY ano ASC;";
	  return $SQL_String;
	}  
    
	
	//-- Admin Area :  get area data
	public static function ADMIN_AREA_GET_AREA_VIEW_DATA(){
	  $SQL_String = "SELECT ano,area_code,area_type,area_name,area_descrip,area_link,area_gates,area_load,accept_max_day,accept_min_day,revise_day,cancel_day,filled_day,wait_list,auto_pass,time_open,time_close,refer_json,form_json,_open FROM area_main WHERE area_code=:area_code AND _keep=1 ;";
	  return $SQL_String;
	}  
		
	//-- Admin Area :  get area data
	public static function ADMIN_AREA_GET_AREA_STOP_DATES(){
	  $SQL_String = "SELECT asno,date_start,date_end,reason,effect,_active FROM area_stop WHERE am_id = :ano AND _keep=1 ORDER BY asno DESC;";
	  return $SQL_String;
	}  	
		
	
	//-- Admin Area : get area can edit field
	public static function ADMIN_AREA_GET_AREA_EDIT_DATA(){
	  $SQL_String = "SELECT area_type,area_name,area_descrip,area_link,area_gates,area_load,accept_max_day,accept_min_day,revise_day,cancel_day,filled_day,wait_list,auto_pass,time_open,time_close,refer_json,form_json FROM area_main WHERE area_code=:area_code AND _keep=1 ;";
	  return $SQL_String;
	} 
	
	
	//-- Admin Area : Modify Area Data
	public static function ADMIN_AREA_UPDATE_AREA_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE area_main SET ".join(',',$condition)." WHERE area_code=:area_code;";
	  return $SQL_String;
	}
	
	//-- Admin Area : create new area
	public static function ADMIN_AREA_INSERT_NEW_AREA_DATA(){
	  $SQL_String = "INSERT INTO area_main VALUES(NULL,:area_code,:area_type,:area_name,:area_descrip,:area_link,:area_gates,".
	                   "IFNULL(:area_load, DEFAULT(area_load)),".
					   "IFNULL(:accept_max_day, DEFAULT(accept_max_day)),".
					   "IFNULL(:accept_min_day, DEFAULT(accept_min_day)),".
					   "IFNULL(:revise_day, DEFAULT(revise_day)),".
					   "IFNULL(:cancel_day, DEFAULT(cancel_day)),".
					   "IFNULL(:filled_day, DEFAULT(filled_day)),".
					   "IFNULL(:wait_list, DEFAULT(wait_list)),".
					   "IFNULL(:auto_pass, DEFAULT(auto_pass)),".
					   "IFNULL(:time_open, DEFAULT(time_open)),".
					   "IFNULL(:time_close, DEFAULT(time_close)),".
					   "'[]','[]',:owner,1,1,:user,NULL);";
	  return $SQL_String;
	}
	
	//-- Admin Area : update new area
	public static function ADMIN_AREA_UPDATE_AREA_CODE(){
	  $SQL_String = "UPDATE area_main SET area_code = SUBSTRING(MD5(CONCAT(ano,'#',area_name)),1,8) WHERE ano=:ano;";
	  return $SQL_String;
	}
	
	
	
	
	
	/*== [ Module - STOP DATE ] ==*/
	
	//-- Admin Area : check area status
	public static function ADMIN_AREA_CHECK_AREA_KEEP(){
	  $SQL_String = "SELECT ano FROM area_main WHERE area_code=:area_code AND _keep=1";
	  return $SQL_String;
	}
	
	//-- Admin Area : get area stop date data
	public static function GET_AREA_TARGET_STOP_DATE(){
	  $SQL_String = "SELECT ano,area_name,date_start,date_end,reason,_active FROM area_main LEFT JOIN area_stop ON ano=am_id WHERE asno=:asno AND area_main._keep=1 AND area_stop._keep=1;";
	  return $SQL_String;
	} 
	
	//-- Admin Area : get area stop date data
	public static function ADMIN_AREA_UPDATE_AREA_STOP_DATE(){
	  $SQL_String = "INSERT INTO area_stop VALUES(:asno,:am_id,:date_start,:date_end,:reason,:effect,:active,1,:user,NULL) ON DUPLICATE KEY UPDATE date_start=:date_start , date_end=:date_end , reason=:reason , _active=:active , `@user`=:user;";
	  return $SQL_String;
	} 
	
	//-- Admin Area : check area status
	public static function ADMIN_AREA_DELETE_AREA_STOP_DATE(){
	  $SQL_String = "UPDATE area_stop SET _keep=0 AND @user=:user WHERE asno=:asno AND _keep=1";
	  return $SQL_String;
	}
	
	//-- Admin Area : get booking in target stop date
	public static function SEARCH_STOP_DATE_RANGE_BOOKING(){
	  $SQL_String = "SELECT * FROM area_booking WHERE am_id=:am_id AND (date_enter BETWEEN :stop_start AND :stop_end OR date_exit BETWEEN :stop_start AND :stop_end) AND _keep=1";
	  return $SQL_String;
	}
	
	
	
	
	/*== [ Module - AREA BLOCK ] ==*/
	//-- Admin Area : get area block
	public static function GET_AREA_BLOCKS(){
	  $SQL_String = "SELECT * FROM area_block WHERE am_id=:amid AND _keep=1 ORDER BY ab_id ASC";
	  return $SQL_String;
	}
	
	//-- Admin Area : add area block
	public static function NEW_AREA_BLOCK(){
	  $SQL_String = "INSERT INTO area_block VALUES(NULL,:am_id,'new','新增子區','','',:area_load,:accept_max_day,:accept_min_day,:wait_list,'[]',	:editor,NULL,1,1)";
	  return $SQL_String;
	}
	
	//-- Admin Area : del block id
	public static function DEL_AREA_BLOCK(){
	  $SQL_String = "DELETE FROM area_block WHERE ab_id=:ab_id";
	  return $SQL_String;
	}
	
	//-- Admin Area : renew block id
	public static function RENEW_AREA_BLOCK(){
	  $SQL_String = "UPDATE area_block SET ab_id=:abid WHERE abno=:abno";
	  return $SQL_String;
	}
	
	//-- Admin Area : Modify block Data
	public static function UPDATE_BLOCK_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE area_block SET ".join(',',$condition)." WHERE ab_id=:abid;";
	  return $SQL_String;
	}
	
	
	
	
	
  }

?>