<?php

  /*
  *   Admin Archive SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdArchive{
	
    
	
	/***-- Admin Archive SQL --***/
	
	
	//--  Admin Archive : 查詢 filelevel 表取得相關 Level Data
	public static function SELECT_FILE_LEVEL( $OrganCode='000', $LevelType = 'classify' ,$SearchTarget = 'id'){
	  $SQL_String = "SELECT * FROM search_level WHERE  organ='".$OrganCode."' AND type='".$LevelType."' AND ".$SearchTarget." = :Value;";
	  return $SQL_String;
	}
	
	//-- Admin Archive : Get User Folders
	public static function ADMIN_ARCHIVE_GET_FOLDERS(){
	  $SQL_String = "SELECT ufno,owner,user_folder.name,files,count(*) AS queue,_share,createtime FROM user_folder LEFT JOIN task_upload ON ufno=folder WHERE owner=:owner AND ftype='folder' AND _keep=1 AND (_upload !='' || _upload IS NULL) GROUP BY ufno ORDER BY createtime DESC;";
	  return $SQL_String;
	}
	
	//-- Admin Archive : Get User Tags  // 取得標籤參照
	public static function ADMIN_ARCHIVE_GET_TAGS(){
	  $SQL_String = "SELECT utno,owner,tag_term,edit_time,user_name FROM user_tags LEFT JOIN user_info ON owner=uid WHERE 1 ORDER BY edit_time DESC;";
	  return $SQL_String;
	}
	
	
	/****---  使用者搜尋SQL組  ---*****/
    
	
	// 取得 Search Parent Note
	public static function GET_SEARCH_TEMP_RECORD(){
	  $SQL_String = "SELECT * FROM search_temp WHERE Search_Action_Num=:ACCNUM AND User_Access_ID=:UAID;";
	  return $SQL_String; 
	}
	
	// 存入search_history 資料表
	public static function INSERT_SEARCH_HISTORY_TABLE(){
	  $SQL_String = "INSERT INTO search_history VALUES(NULL,:UID,:USID,:ACCNUM,:QTSET,:PAGE,'','".date('Y-m-d H:i:s')."','');";
	  return $SQL_String; 
	}
	
	// 註冊 search_temp 資料表
	public static function INSERT_SEARCH_TEMP(){
	  $SQL_String = "INSERT INTO search_temp VALUES(NULL,:UAID,:SQL_Mysql,:SQL_Sphinx,:Query_Set,'','".date('Y-m-d H:i:s')."',:ACTION,:ACCNUM);";
	  return $SQL_String; 
	}
	
	// 搜尋 file_level 資料表
	public static function GET_FILE_LEVEL_TARGET(){
	  $SQL_String = "SELECT name FROM search_level WHERE organ=:organ AND lvcode=:LV;";
	  return $SQL_String; 
	}
	
	// 取得 file_level 資料表
	public static function SELECT_FILE_LEVEL_DATA(){
	  $SQL_String = "SELECT * FROM search_level WHERE organ=:organ AND type=:type;";
	  return $SQL_String; 
	}
	
	
	// 確認 user folder
	public static function CHECK_FOLDER_CAN_ACCESS(){
	  $SQL_String = "SELECT ufno,owner,name,descrip,files FROM user_folder WHERE ufno=:ufno AND (owner=:owner OR _share=1) AND ftype='folder' AND _keep=1 ;";
	  return $SQL_String; 
	}
	
	
	
	
	//檢索 SQL 設定
	public static function SEARCH_SQL_HEADER_MYSQL($UserId){
	  $SQL_String = "SELECT * FROM metadata ";
	  return $SQL_String; 
	}
	
	//檢索 SphinxSearch 
	public static function SEARCH_SQL_HEADER_SPHINX($UserId){
	  $SQL_String = "SELECT * FROM metadatafts LEFT JOIN metadata ON metadatafts.id=metadata.system_id ";
	  return $SQL_String; 
	}
	
	
	
	public static function GET_USER_SEARCH_HISTORY_LIST($Limit_String=''){
	  //會傳入是否要限制筆數
	  $SQL_String = "SELECT * FROM search_history ,(SELECT max(HisNum) as hn FROM search_history WHERE User_Name=:UID GROUP BY Query_Term_Set ) AS sh WHERE HisNum=sh.hn ORDER BY Search_Time DESC ".$Limit_String.";"; 
	  return $SQL_String; 
	}
	
	public static function GET_USER_HISTORY(){
	  //
	  $SQL_String = "SELECT * FROM search_history WHERE HisNum=:HSN AND User_Name=:UID;";
	  return $SQL_String; 
	}
	
	//-- 取得物件顯示資料
	public static function GET_OBJECT_METADATA(){
	  $SQL_String = "SELECT * ".
	                "FROM metadata WHERE identifier=:id AND _keep=1 ;";
	  return $SQL_String; 
	}
	
	
	//-- Admin Archive : Modify Photo Meta
	public static function ADMIN_ARCHIVE_UPDATE_META_DATA( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE metadata SET ".join(',',$condition)." WHERE identifier=:identifier AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- Admin Archive : Select User Tags
	public static function ADMIN_ARCHIVE_SEARCH_USER_TAG(){
	  $SQL_String = "SELECT * FROM user_tags WHERE owner=:owner AND tag_term=:tag;";
	  return $SQL_String;
	}
	
	//-- Admin Archive : Create User Tags
	public static function ADMIN_ARCHIVE_CREATE_USER_TAG(){
	  $SQL_String = "INSERT INTO user_tags VALUES (NULL,:owner,:tag_term,NULL);";
	  return $SQL_String;
	}
	
	
	
	//-- 取得物件所在資料夾
   	public static function GET_OBJECT_FOLDERS(){
	  $SQL_String = "SELECT * FROM folder_map LEFT JOIN user_folder ON fid=ufno WHERE sid=:sid;";
	  return $SQL_String; 
	}
	
	
	//-- 移除資料夾對應表資料
	public static function REMOVE_FROM_FOLDER_MAP(){
	  $SQL_String = "DELETE FROM folder_map WHERE sid=:sid;";
	  return $SQL_String; 
	}
	
	//-- 移除資料meta
	public static function REMOVE_FROM_METADATA(){
	  $SQL_String = "UPDATE metadata SET _keep=0,update_user=:user WHERE system_id=:sid;";
	  return $SQL_String; 
	}
	
	//-- user_folder count -1
	public static function SUB_COUNT_USER_FOLDER(){
	  $SQL_String = "UPDATE user_folder SET files=(files-1) WHERE ufno=:ufno;";
	  return $SQL_String; 
	}
	
	//-- system_level count -1
	public static function SUB_COUNT_SYSTEM_LEVEL(){
	  $SQL_String = "UPDATE search_level SET count=(count-1) WHERE organ='iis' AND site=:site AND info=:level;";
	  return $SQL_String; 
	}
	
	
	
	// 
	public static function GET_OBJECT_ACCESS_RULE($GroupList="'999'"){
	  $SQL_String = "SELECT * FROM rule_table WHERE (type='object_limit' OR ( type='image_access' AND target='group' AND rvalue IN(".$GroupList."))) AND keep=1;";
	  return $SQL_String; 
	}
	
	// 註冊存取資料
	public static function REGIST_RESULT_HISTORY(){
	  $SQL_String = "INSERT INTO result_history VALUES(NULL,:CODE,:SNO,:UID,:ACCPER,NULL);";
	  return $SQL_String; 
	}
	
	public static function GET_DATA_DISPLAY(){
	  $SQL_String = "SELECT * FROM result_history LEFT JOIN metadata ON result_history.StoreNo = metadata.StoreNo WHERE CONCAT(RslNum,Code)=:ACCCODE;";
	  return $SQL_String; 
	}
	
	public static function GET_DISPLAY_StoreNo($AccType = 'Public'){
	  switch($AccType){
	    case 'Private':
		  $SQL_String = "SELECT StoreNo,Acc_Permission FROM result_history WHERE CONCAT(RslNum,Code)=:ACCCODE AND User_Name=:UID;";
	      break;
		
		case 'Public':
		default:
		  $SQL_String = "SELECT StoreNo,Acc_Permission FROM result_history WHERE CONCAT(RslNum,Code)=:ACCCODE;";
	      break;
	  }
	  return $SQL_String; 
	}
    
	public static function REGIST_DISPLAY_PAGE(){
	  $SQL_String = "INSERT INTO result_visit VALUES(NULL,:ACCCODE,:ACCPAGE,:UIP,:UID,'".date('Y-m-d H:i:s')."');";
	  return $SQL_String; 
	}
	
	public static function GET_VISIT_PAGE($limit=20){
	  $SQL_String = "SELECT Vtime,Visit_Code,Visit_Page,result_history.StoreNo,Title FROM (SELECT Visit_Time AS Vtime,VisitId,Visit_Code,Visit_Page FROM result_visit,(SELECT max(Visit_Time) as vt FROM result_visit WHERE User_Name=:UID GROUP BY Visit_Code) as rv WHERE Visit_Time = rv.vt ORDER BY Visit_Time DESC) AS temp LEFT JOIN result_history ON temp.Visit_Code=CONCAT(result_history.RslNum,result_history.Code) LEFT JOIN metadata ON result_history.StoreNo = metadata.StoreNo ORDER BY Vtime DESC LIMIT 0,".$limit.';';
	  return $SQL_String;
	}
    
	/***-- 使用者工作 SQL SET --***/
	
	//-- regist upload task 
	public static function REGIST_USER_TASK(){
	  $SQL_String = "INSERT INTO user_task VALUES (NULL,:user,:task_name,:task_type,:task_num,:task_done,:time_initial,'0000-00-00 00:00:00','','',1);";
	  return $SQL_String;
	}
	
	//-- bind photo process task 
	public static function BIND_PHOTO_IMPORT_TASK(){
	  $SQL_String = "UPDATE task_upload SET utkid=:utkid WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	
    /***--  使用者上傳 SQL SET  --***/
	
	//-- regist upload folder 
	public static function REGIST_USER_UPLOAD_FOLDER(){
	  $SQL_String = "INSERT INTO user_folder VALUES (NULL,:owner,:ftype,:name,'',:path,0,'".date('Y-m-d H:i:s')."',:updtime,1,0,1) ON DUPLICATE KEY UPDATE uploadtime=:updtime,_uploading=1 ;";
	  return $SQL_String;
	}
	
	//-- get upload folder 
	public static function GET_USER_UPLOAD_FOLDER(){
	  $SQL_String = "SELECT * FROM user_folder WHERE owner=:owner AND name=:name AND ftype='folder' AND _keep=1;";
	  return $SQL_String;
	}
	
	//-- get uploaded file list 
	public static function SELECT_UPLOAD_PHOTO_LIST(){
	  $SQL_String = "SELECT * FROM task_upload WHERE user=:user AND folder=:folder AND flag=:flag AND _upload!='' AND _process='';";
	  return $SQL_String;
	}
	
	//-- finish folder upload state 
	public static function FINISH_USER_UPLOAD_TASK(){
	  $SQL_String = "UPDATE user_folder SET uploadtime='',_uploading=0 WHERE owner=:uno AND ufno=:ufno;";
	  return $SQL_String;
	}
	
	
	//- check upload file exist
	public static function CHECK_FILE_UPLOAD_LIST(){
	  $SQL_String = "SELECT folder,_regist FROM task_upload WHERE hash=:hash AND _upload!='';";
	  return $SQL_String;
	}
	
	//- regist upload file 
	public static function REGIST_FILE_UPLOAD_RECORD(){
	  $SQL_String = "INSERT INTO task_upload VALUES(NULL,:utkid,:folder,:flag,:user,:hash,:creater,:classlv,:name,:size,:mime,:type,:last,'".date('Y-m-d H:i:s')."','','','','');";
	  return $SQL_String;
	}
	
	//- update upload process  
	public static function UPDATE_FILE_UPLOAD_UPLOADED(){
	  $SQL_String = "UPDATE task_upload LEFT JOIN user_folder ON ufno=folder SET _upload='".date('Y-m-d H:i:s')."' , _uploading=1 WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	/***--  使用者打包 SQL SET  --***/
	//- regist export file 
	public static function REGIST_FILE_EXPORT_RECORD(){
	  $SQL_String = "INSERT INTO task_export VALUES(NULL,:utkid,:meta_id,:locat,:save,'".date('Y-m-d H:i:s')."','','');";
	  return $SQL_String;
	}
	
	//-- check export meta
	public static function SELECT_EXPORT_META($meta=array(1)){
	  $id_list = count($meta) ? join("','",$meta) : "";	
	  $SQL_String = "SELECT * FROM metadata WHERE identifier IN('".$id_list."') AND _display=1 AND _keep=1;";
	  return $SQL_String;
	}
	
	
	
	/***--  使用者下載 SQL SET  --***/
	
	//- logs photo download  
	public static function LOGS_FILE_DOWNLOAD(){
	  $SQL_String = "INSERT INTO logs_download VALUES(NULL,NULL,:file_id,:file_type,:user_id,:user_ip,:request_url);";
	  return $SQL_String;
	}
	
	//- count photo download  
	public static function COUNT_FILE_DOWNLOAD(){
	  $SQL_String = "SELECT count(*) FROM logs_download WHERE file_id=:id GROUP BY file_id;";
	  return $SQL_String;
	}
	
	
	//-- check export folder
	public static function SELECT_EXPORT_FOLDER(){
	  $SQL_String = "SELECT * FROM user_folder WHERE name=:hash AND ftype='package' AND _keep=1;";
	  return $SQL_String;
	}
	
	
	//-- check export folder
	public static function SELECT_EXPORT_PHO_LIST(){
	  $SQL_String = "SELECT * FROM task_export WHERE utkid=:utkid AND _access='1';";
	  return $SQL_String;
	}
	
	
	//-- update package download count
	public static function UPD_PACKAGE_DOWNLOAD_COUNT(){
	  $SQL_String = "UPDATE user_folder SET _share=(_share+1) WHERE name=:hash;";
	  return $SQL_String;
	}
	
	
	/***--  使用者加入新資料夾 SQL SET  --***/
	
	//-- 搜尋同名資料夾
	public static function CHECK_PUTIN_FOLDER_NAME(){
	  $SQL_String = "SELECT ufno FROM user_folder WHERE owner=:owner AND ftype='folder' AND name=:name;";
	  return $SQL_String;
	}
	
	//-- 註冊新資料夾
	public static function REGIST_USER_PUTIN_FOLDER(){
	  $SQL_String = "INSERT IGNORE INTO user_folder VALUES (NULL,:owner,:ftype,:name,'','',0,'".date('Y-m-d H:i:s')."','',0,1,1);";
	  return $SQL_String;
	}
	
    //-- 補充註冊新資料夾路徑
	public static function UPDATE_USER_PUTIN_FOLDER(){
	  $SQL_String = "UPDATE user_folder SET path=:path WHERE ufno=:ufno;";
	  return $SQL_String;
	}
	
	
	//-- 將資料放入資料夾
	public static function INSERT_DATA_TO_FOLDER(){
	  $SQL_String = "INSERT IGNORE INTO folder_map VALUES (:fid,:sid,NULL);";
	  return $SQL_String;
	}
	
	//-- 重新計算數量
	public static function RECOUNT_FOLDER_DATA(){
	  $SQL_String = "UPDATE user_folder SET user_folder.files=(SELECT count(*) FROM folder_map WHERE fid=:fid GROUP BY fid),user_folder._keep=1 WHERE ufno=:fid;";
	  return $SQL_String;
	}
	
	
	
	
  }	