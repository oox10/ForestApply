<?php
  
  /*
  *   [RCDH10 Admin Module] - Book Sql Library 
  *   Admin Book SQL SET
  *
  *   2017-06-10 ed.  
  *   note: 透過篩選使用者管理區域來限制區域註冊資料 filter by permission_rule area_main part
  */  
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdBook{
	
   
	/***-- Admin Book SQL --***/
	
	
	//-- Admin Book : Get User Access Areas 
	public static function SELECT_USER_AREA(){
	  $SQL_String = "SELECT ano,area_code,area_type,area_name FROM area_main WHERE _keep=1 ORDER BY area_type ASC, ano ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Book : Get Access Areas booking
	public static function SELECT_AREA_BOOKING($AdminAreas=array(),$Condition='1',$OrderBy='_time_update DESC'){
	  $SQL_String = "SELECT area_booking.*,area_main.area_type FROM area_booking LEFT JOIN area_main ON ano=am_id WHERE am_id IN(".join(',',$AdminAreas).") AND ".$Condition." AND area_booking._keep=1 ORDER BY ".$OrderBy." ;";
	  return $SQL_String;
	}
	
	//-- Admin Book : get book data for admin 
	public static function GET_BOOKING_RECORD(){
	  $SQL_String = "SELECT * FROM area_booking WHERE (apply_code=:abno) AND _keep=1 ;";
	  return $SQL_String;
	}
	
	//-- Admin Book : get book data for admin 
	public static function GET_BOOKING_EDITED(){
	  $SQL_String = "SELECT check_note,am_id AS AREAID FROM area_booking WHERE (apply_code=:abno) AND _keep=1 ;";
	  return $SQL_String;
	}
	
	
	//-- Admin Book : get area data for admin
	public static function GET_AREA_META(){
	  $SQL_String = "SELECT * FROM area_main WHERE ano=:ano AND _keep=1 ;";
	  return $SQL_String;
	} 
	
	//-- Admin Book : 
	public static function GET_APPLY_HISTORY(){
	  $SQL_String = "SELECT area_name,apply_date,apply_reason,member_count,_final FROM area_booking LEFT JOIN area_main ON am_id=ano WHERE apply_code != :apply_code AND applicant_name=:applicant_name AND applicant_mail=:applicant_mail AND area_booking._keep=1 ORDER BY apply_date DESC;";
	  return $SQL_String;
	} 
	
	
	//-- Admin Book : Modify Book Data
	public static function UPDATE_BOOK_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE area_booking SET ".join(',',$condition)." WHERE apply_code=:apply_code;";
	  return $SQL_String;
	}
	
	
	
	
	
	
  }	