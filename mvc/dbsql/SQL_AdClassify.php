<?php

  /*
  *   Admin Classify SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdClassify{
	
    /***-- Admin Classify SQL --***/
	
	//-- Admin Classify : Get User Tags 
	public static function ADMIN_CLASSIFY_GET_USER_TAGS(){
	  $SQL_String = "SELECT * FROM user_tags WHERE owner=:user ORDER BY edit_time DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Get User Folders 
	public static function ADMIN_CLASSIFY_GET_USER_FOLDERS(){
	  $SQL_String = "SELECT * FROM user_folder WHERE owner=:user AND ftype='folder' AND _keep=1 ORDER BY createtime DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Get System Classify 
	public static function ADMIN_CLASSIFY_GET_SYSTEM_LEVEL(){
	  $SQL_String = "SELECT * FROM search_level WHERE type='classify' AND organ ='forest' ORDER BY lvcode ASC;";
	  return $SQL_String;
	}
	
	
	//-- Admin Classify : Update User Tags 
	public static function ADMIN_CLASSIFY_UPDATE_USER_TAGS(){
	  $SQL_String = "UPDATE user_tags SET tag_term=:new_term WHERE owner=:user AND utno=:term_id;";
	  return $SQL_String;
	}
	//-- Admin Classify : DELETE User Tags 
	public static function ADMIN_CLASSIFY_DELETE_USER_TAGS(){
	  $SQL_String = "DELETE FROM user_tags  WHERE owner=:user AND utno=:term_id;";
	  return $SQL_String;
	}
	
	
	//-- Admin Classify : Update User Folder 
	public static function ADMIN_CLASSIFY_UPDATE_USER_FOLDER(){
	  $SQL_String = "UPDATE user_folder SET name=:new_term WHERE owner=:user AND ufno=:term_id;";
	  return $SQL_String;
	}
	
	//-- Admin Classify : DELETE User Tags 
	public static function ADMIN_CLASSIFY_DELETE_USER_FOLDER(){
	  $SQL_String = "DELETE FROM user_folder WHERE owner=:user AND ufno=:term_id;";
	  return $SQL_String;
	}
	
	
	
	
	//-- Admin Classify : Select System Level 
	public static function ADMIN_CLASSIFY_SELECT_SYSTEM_LEVEL($field='lvno'){
	  $SQL_String = "SELECT * FROM search_level WHERE ".$field."=:".$field.";";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Update System Level 
	public static function ADMIN_CLASSIFY_UPDATE_SYSTEM_CLASS(){
	  $SQL_String = "UPDATE search_level SET name=:new_term WHERE lvcode=:term_id;";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Insert System Level 
	public static function ADMIN_CLASSIFY_INSERT_SYSTEM_LEVEL(){
	  $SQL_String = "INSERT INTO search_level VALUES(NULL,'classify','iis',:uplv,:lvid,:lvcode,:site,:name,:info,1,0);";
	  return $SQL_String;
	}
	
	
	//-- Admin Classify : Insert New Level To User Folder Data 
	public static function ADMIN_CLASSIFY_UPDATE_FOLDER_DATA_LEVEL(){
	  $SQL_String = "UPDATE folder_map LEFT JOIN metadata ON sid=system_id SET classlevel=CONCAT(classlevel,:newlevel) WHERE fid=:fid AND _keep=1";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Update New Level Count Add From folder 
	public static function ADMIN_CLASSIFY_UPDATE_SYSTEM_LEVEL_COUNT(){
	  $SQL_String = "UPDATE search_level SET count=:count WHERE lvno=:lvno";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Update Metadata Term Value 
	public static function ADMIN_CLASSIFY_META_UPDATE_TERMS($Field='1'){
	  $SQL_String = "UPDATE metadata SET ".$Field."=REPLACE(REPLACE(".$Field.",:search_term,:replace_term),';;',';') WHERE ".$Field." LIKE :condition AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Classify : Delete Class Level
	public static function ADMIN_CLASSIFY_DELETE_SYSTEM_LEVEL_RECORD(){
	  $SQL_String = "UPDATE search_level SET type='delete' WHERE lvno=:lvno;";
	  return $SQL_String;
	}
	
	
	
  }
  
  
?>