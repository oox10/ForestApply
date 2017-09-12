<?php
 /*
  *   Admin Post SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdPost{
	  
	
	
	/***-- Admin Post Permission SQL --***/
	
	//-- Admin Post : Access Check
	public static function CHECK_ACCESS_PERMISSION(){
	  $SQL_String = "SELECT uid,gid FROM permission_matrix WHERE uid=:uid AND master=1;";
	  return $SQL_String;
	}
	
	
	/***-- Admin Post SQL --***/  
	
	//-- Admin Post :  get system_post table descrip
	public static function ADMIN_POST_GET_POST_TABLE(){
	  $SQL_String = "DESCRIBE system_post;";
	  return $SQL_String;
	}
	
	//-- Admin Post :  get user_group list
	public static function ADMIN_POST_GET_POST_GROUPS(){
	  $SQL_String = "SELECT ug_code,ug_name,ug_pri FROM user_group WHERE 1 ORDER BY ug_pri DESC,ug_no ASC;";
	  return $SQL_String;
	}
	
	//-- Admin Post :  get post user list 
	public static function ADMIN_POST_GET_POST_LIST(){
	  $SQL_String = "SELECT * FROM system_post WHERE post_keep=1 ORDER BY post_level DESC,post_time_start DESC;";
	  return $SQL_String;
	}  
    
	
	//-- Admin Post :  get post user list 
	public static function ADMIN_POST_GET_POST_VIEW_DATA(){
	  $SQL_String = "SELECT * FROM system_post WHERE pno=:pno AND post_keep=1 ;";
	  return $SQL_String;
	}  
	
	
	//-- Admin Post : get post can edit field
	public static function ADMIN_POST_GET_POST_EDIT_DATA(){
	  $SQL_String = "SELECT post_type,post_from,post_to,post_level,post_time_start,post_time_end,post_title,post_content,post_display FROM system_post WHERE pno=:pno AND post_keep=1 ;";
	  return $SQL_String;
	} 
	
	
	//-- Admin Post : Modify Post Data
	public static function ADMIN_POST_UPDATE_POST_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE system_post SET ".join(',',$condition)." WHERE pno=:pno;";
	  return $SQL_String;
	}
	
	//-- Admin Post : create new post
	public static function ADMIN_POST_INSERT_NEW_POST_DATA(){
	  $SQL_String = "INSERT INTO system_post VALUES(NULL,:post_type,:post_from,:post_to,:post_target,:post_level,:post_time_start,:post_time_end,:post_title,:post_content,:post_refer,1,'',:edit_user,NULL,:edit_group,0,1);";
	  return $SQL_String;
	}
	
	
	
	
	
	
	
	
  }

?>