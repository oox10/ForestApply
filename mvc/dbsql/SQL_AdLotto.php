<?php
  
  /*
  *   [RCDH10 Admin Module] - Lotto Sql Library 
  *   Admin Lotto SQL SET
  *
  *   2017-01-01 ed.  
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdLotto{
	
   
	/***-- Admin Lotto SQL --***/
	
	//-- Admin Lotto :  scan booking_lotto table
	public static function GET_LOTTO_AREA_LIST(){
	  $SQL_String = "SELECT booking_lotto.*,area_main.area_type,area_main.area_code,area_main.area_name,area_main.area_load,area_main.wait_list FROM booking_lotto LEFT JOIN area_main ON aid=ano WHERE booking_lotto._keep=1 ORDER BY date_tolot DESC , aid ASC";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  read booking_lotto record
	public static function GET_LOTTO_TARGET_DATA(){
	  $SQL_String = "SELECT booking_lotto.* FROM booking_lotto LEFT JOIN area_main ON aid=ano WHERE blno=:blno AND booking_lotto._keep=1 ;";
	  return $SQL_String;
	}
	
	
	//-- Admin Lotto :  scan booking_lotto table
	public static function BUILT_LOTTO_BOX(){
	  $SQL_String = "INSERT INTO booking_lotto VALUES(NULL,:amid,:lotto_date,:enter_date,'0000-00-00 00:00:00','[]',0,'[]',NULL,0,0,1);";
	  return $SQL_String;
	}
	
	public static function UPDATE_LOTTO_BOX(){
	  $SQL_String = "UPDATE booking_lotto SET lotto_pool=:lotto_pool WHERE aid=:aid AND date_tolot=:date_tolot AND _keep=1 AND _loted=0;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  scan booking_lotto table
	public static function GET_LOTTO_QUEUE(){
	  $SQL_String = "SELECT * FROM booking_lotto WHERE date_tolot=:date_tolot AND _keep=1 ORDER BY aid ASC,date_tolot ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  scan area_booking table that to lotto
	public static function GET_LOTTO_BOOKING(){
	  $SQL_String = "SELECT * FROM area_booking WHERE _ballot=1 AND _ballot_date=:lotto_date AND _ballot_result=0 AND _stage<=2 AND _keep=1 AND _status='收件待審' ORDER BY am_id ASC,apply_date ASC,abno ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  get today lotto box
	public static function GET_LOTTO_TODAY(){
	  $SQL_String = "SELECT * FROM booking_lotto WHERE date_tolot=:lottodate AND _loted=0 AND _keep=1  ORDER BY aid ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  get target lotto box
	public static function GET_LOTTO_TARGET(){
	  $SQL_String = "SELECT * FROM booking_lotto WHERE blno=:lottono AND _loted=0 AND _keep=1  ORDER BY aid ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  get book data
	public static function GET_BOOKING_DATA(){
	  $SQL_String = "SELECT * FROM area_booking WHERE am_id=:amid AND abno=:abno AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  get area data
	public static function GET_LOTTO_AREA_DATA(){
	  $SQL_String = "SELECT * FROM area_main WHERE ano=:ano AND _open=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  get area data
	public static function GET_LOTTO_AREA_BLOCK_DATA(){
	  $SQL_String = "SELECT * FROM area_block WHERE am_id=:amid AND _open=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
	//-- Admin Lotto :  update lotto logs
	public static function UPDATE_LOTTO_BOX_LOGS(){
	  $SQL_String = "UPDATE booking_lotto SET logs_process=:logs_process WHERE blno=:blno;";
	  return $SQL_String;
	}
	
	//-- Admin Lotto :  update lotto data
	public static function UPDATE_LOTTO_BOX_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE booking_lotto SET ".join(',',$condition)." WHERE blno=:blno;";
	  return $SQL_String;
	}
	
	
	//-- Admin Lotto :  update lotto data
	public static function UPDATE_BOOKING_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE area_booking SET ".join(',',$condition)." WHERE abno=:abno;";
	  return $SQL_String;
	}
	
	
	
  }	