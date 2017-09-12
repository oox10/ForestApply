<?php

  /***
  *   TLCDA Admin System Main SQL SET
  *  
  *
  */
  
  
  class SQL_Admin{
	
	/* [ System Execute function Set ] */ 	
	
	
	
	
	/***-- System Work Sqls --***/
	//-- 系統紀錄 
	public static function SYSTEM_LOGS_USED_ACTION(){
	  $SQL_String = "INSERT INTO logs_system VALUES(NULL,NULL,:acc_ip,:acc_act,:acc_url,:session,:request,:acc_from,:result,:agent);";
	  return $SQL_String;
	}
	
	
	// 查詢 system_info 表取得相關 info
	public static function SEARCH_INDEX_GET_POST($Council='000'){
	  $SQL_String = "SELECT * FROM system_post WHERE post_type=:post_type AND (post_to='#all' OR post_to='".$Council."') AND post_keep=1 AND ((post_time_start<NOW() AND post_time_end >= NOW() AND post_level >0 AND post_level<4 ) OR post_level=4  ) AND post_display=1 ORDER BY post_level DESC,post_time_start DESC";
	  return $SQL_String;
	}
	
	
    /***-- System Main Page Sqls --***/
	
	// 查詢系統總資料空間
	public static function SELECT_ALL_DATA_STORE(){
	  $SQL_String = "SELECT EXTRACT(YEAR_MONTH FROM upload_date) AS stage,sum(image_size) as total_size,count(*) AS count ,classcode FROM metadata WHERE 1 GROUP BY stage ORDER BY stage ASC;";
	  return $SQL_String;
	}
	
	
	
	
	
	
  }
  
  
?>