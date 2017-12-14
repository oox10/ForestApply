<?php
 /*
  *   Admin Page SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdPage{
	  
	
	
	/***-- Admin Page Permission SQL --***/
	
	//-- Admin Page : Access Check
	public static function CHECK_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	
	/***-- Admin Page SQL --***/  
	
	 
	//-- Admin Page :  get user_group list
	public static function  GET_SYSTEM_GROUPS(){
	  $SQL_String = "SELECT ug_code,ug_name,ug_pri FROM user_group WHERE 1 ORDER BY ug_pri DESC,ug_no ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Page :  get Page list 
	public static function GET_PAGE_LIST(){
	  $SQL_String = "SELECT * FROM system_pages WHERE page_owner='_ALL' OR page_owner=:master;";
	  return $SQL_String;
	}  
    
	
	//-- Admin Page :  get page target 
	public static function GET_PAGE_DATA(){
	  $SQL_String = "SELECT * FROM system_pages WHERE spno=:spno ;";
	  return $SQL_String;
	}  
	
	
	//-- Admin Post : get page can edit field
	public static function GET_DB_EDIT_DATA(){
	  $SQL_String = "SELECT page_type,page_owner,page_show,page_title,page_content FROM system_pages WHERE spno=:spno;";
	  return $SQL_String;
	} 
	
	
	//-- Admin Page : Modify Page Data
	public static function UPDATE_PAGE_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE system_pages SET ".join(',',$condition)." WHERE spno=:spno;";
	  return $SQL_String;
	}
	
	//-- Admin Page : create new Page
	public static function INSERT_NEW_PAGE(){
	  $SQL_String = "INSERT INTO system_pages VALUES(NULL,:page_type,:page_owner,:page_show,:page_title,:page_content,null,:user);";
	  return $SQL_String;
	}
	
	
	
	
	
	
	
	
  }

?>