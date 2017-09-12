<?php

  /*
  *   Admin Catalog SQL SET
  *  
  *
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdCatalog{
	  
	  
	/***-- 1. Admin Catalog SQL --***/  
	  
	  
	//-- Get Current Group Resouce Catalog
	public static function GET_BOOK_CATALOG_DATA(){
	  $SQL_String = "SELECT * FROM data_book_catalog WHERE keep=1 ORDER BY bookid ASC;";
	  return $SQL_String;
	}  
	
	//-- Get Target Book Catalog Meta
	public static function GET_BOOK_CATALOG_META(){
	  $SQL_String = "SELECT * FROM data_book_catalog WHERE bookid=:bookid AND keep=1;";
	  return $SQL_String;
	}  
	
	
	//-- Modify Book Catalog Meta
	public static function SAVE_BOOK_CATALOG_META( $MmodifyFields = array(1) ){
	  $condition = array();
	  foreach($MmodifyFields as $field){
	    $condition[] = $field.'=:'.$field;
	  }
	  $SQL_String = "UPDATE data_book_catalog SET ".join(',',$condition)." WHERE bookid=:bookid AND keep=1;";
	  return $SQL_String;
	}
	
	
	//-- Select group code
	public static function GET_RESOUSE_CODE(){
	  $SQL_String = "SELECT ono FROM data_resource_organ WHERE name=:group AND resouse=:resouse;";
	  return $SQL_String;
	} 
	
	//-- count book cata n
	public static function COUNT_CATA_SERIES(){
	  $SQL_String = "SELECT (CAST(MAX(SUBSTRING(bookid, -2, 2) ) AS UNSIGNED)+1)  AS num FROM data_book_catalog WHERE bookid like :bookcode AND keep=1 GROUP BY council;";
	  return $SQL_String;
	} 
	
	
	//-- insert new catalog
	public static function CREATE_NEW_BOOK_CATALOG(){
	  $SQL_String = "INSERT INTO data_book_catalog (bno,bookid,keep,create_info,update_time) VALUES(NULL,:bookid,1,:create,NULL);";
	  return $SQL_String;
	} 
	
	//-- mask book catalog
	public static function MASK_BOOK_CATALOG(){
	  $SQL_String = "UPDATE data_book_catalog SET create_info=:info , keep=0 WHERE bookid=:bookid;";
	  return $SQL_String;
	}
	
	//-- delete book catalog
	public static function DELETE_BOOK_CATALOG(){
	  $SQL_String = "DELETE FROM data_book_catalog WHERE bookid=:bookid;";
	  return $SQL_String;
	} 
	
	
	
	/***-- 2. Export Catalog SQL --***/  
	
	
	//-- Get Books Catalog Export Data
	public static function GET_BOOK_CATALOG_EXPORT_DATA(){
	  $SQL_String = "SELECT bookid,council,book_type,period,book_name,language,keep_status,keep_source,keep_gatted,keep_time,keep_verson,storage_id,storage_locat,limit_tiff,limit_jpg,print_type,descrip,meta_count,page_count FROM data_book_catalog WHERE council=:council AND book_type like :type AND keep=1 ORDER BY bookid ASC;";
	  return $SQL_String;
	} 
	
	//-- Get Books Export Metadata 
	public static function GET_BOOK_METADATA_EXPORT_DATA (){
	  $SQL_String = "SELECT dmno,organ_no,sequ_no,class_no,main_no,cata_no,case_no,summary,full_text,session_no,assembly_no,meeting_no,volume_no,
	                        meeting_name,book_type,page_type,page_num_start,page_num_end,page_ser_start,page_ser_end,store_no,scan_no,date_start,
						    date_end,keep_status,keep_source,keep_gatted,keep_time,keep_verson,chairman,members_main,members_related,members_attend,
						    petitioner,organ_main,organ_related,organ_petiti,doc_no,reference,language,storage_id,storage_locat,limit_tiff,limit_jpg,temp 
					FROM digital_meta WHERE bookid=:bookid AND meta_keep=1 ORDER BY page_ser_start ASC,page_ser_end ASC , dmno ASC;";
					
	  return $SQL_String;
	}
	
	//-- Get Books Export Scanner
	public static function GET_BOOK_DIGITAL_IMAGE_DATA(){
	  $SQL_String = "SELECT * FROM digital_scan WHERE bookid=:bookid AND keep=1 ORDER BY image_order ASC,scid ASC;";
	  return $SQL_String;
	} 
	
	
	
	/***-- 3. Upload Catalog SQL / 使用者上傳 SQL--***/  
	
	//-- get uploaded file list 
	public static function SELECT_UPLOAD_PHOTO_LIST(){
	  $SQL_String = "SELECT * FROM task_upload WHERE user=:user AND folder=:folder AND flag=:flag AND _upload!='' AND _process='';";
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
	  $SQL_String = "UPDATE task_upload SET _upload='".date('Y-m-d H:i:s')."' WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	/***-- 4. User Task Process SQL / 使用者工作 SQL--***/  
	
	//-- regist upload task 
	public static function REGIST_USER_TASK(){
	  $SQL_String = "INSERT INTO task_admin VALUES (NULL,:user,:task_name,:task_type,:task_num,:task_done,:time_initial,'0000-00-00 00:00:00','','',1);";
	  return $SQL_String;
	}
	
	//-- bind photo process task 
	public static function BIND_CATA_IMPORT_TASK(){
	  $SQL_String = "UPDATE task_upload SET utkid=:utkid WHERE urno=:urno;";
	  return $SQL_String;
	}
	
	
	
	  
	  
  
  }