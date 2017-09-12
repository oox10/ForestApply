<?php
  
  /*
  *   [RCDH10 Admin Module] - Record Sql Library 
  *   Admin Record SQL SET
  *
  *   2017-01-01 ed.  
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdRecord{
	
	/***-- Admin Record SQL --***/
	
	//-- Admin Record : Get Area List
	public static function SELECT_AREA_LIST(){
	  $SQL_String = "SELECT ano,area_code,area_type,area_name,area_load,_open FROM area_main WHERE _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Record : Get Area List
	public static function GET_AREA_BOOK_RECORD(){
	  $SQL_String = "SELECT abno,am_id,apply_date,date_enter,member_count,_final,_stage FROM area_booking WHERE _keep=1 AND am_id=:ano AND date_enter BETWEEN :date_start AND :date_end;";
	  return $SQL_String;
	}
	
	
  }	