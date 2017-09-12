<?php

  class Post_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Post Function Set ]*/ 
    
	
	//-- Admin Post Page Config 
	// [input] : NULL;
	public function ADPost_Get_Post_Config(){
	  
	  $result_key = parent::Initial_Result('config');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 查詢資料庫欄位設定
		$DB_OBJ = $this->DBLink->prepare(SQL_AdPost::ADMIN_POST_GET_POST_TABLE());
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
	    $field_set = array();
		foreach($data_list as $data){
		  $field_set[$data['Field']] = array('type'=>'','default'=>'');
		  
		  // 選項欄位設定成介面選項
		  if(strstr($data['Type'],'enum')){
			$field_set[$data['Field']]['type'] = 'select';
			if(preg_match_all("/\'(.*?)\'/",$data['Type'],$match)){
			  $field_set[$data['Field']]['default'] = $match[1];		
			}
		  }
		}
		
		// 取得群組設定
		// 查詢資料庫欄位設定
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_GET_POST_GROUPS()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$group_list = array();
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $group_list[$tmp['ug_code']] = $tmp['ug_name'];
		}
		
		$result['data']['field']   = $field_set;
        $result['data']['group']   = $group_list;
        
		$result['action'] = true;
	   
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Post Page Data OList 
	// [input] : NULL;
	public function ADPost_Get_Post_List(){
	  
	  $result_key = parent::Initial_Result('posts');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_GET_POST_LIST()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		// 取得最新消息清單
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
		$time_now  = strtotime('now');
		
		foreach($data_list as &$data){
		  $data['@list_filter'][]  =  ($time_now > strtotime($data['post_time_end'])) && $data['post_level']!=4 ? 'over' : 'inuse';
		  $data['@list_status'][]  =  $data['post_display'] ? '' : 'mask';
		}
			
		$result['action'] = true;		
		$result['data']   = $data_list;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	//-- Admin Post Get Post Data 
	// [input] : uno  :  \d+;
	
	public function ADPost_Get_Post_Data($DataNo=0){
		
	  $result_key = parent::Initial_Result('post');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	   
		// 取得公告資料
		$post_data = NULL;
		$DB_GET	= $this->DBLink->prepare( parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_GET_POST_VIEW_DATA()) );
		$DB_GET->bindParam(':pno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$post_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
	    
		$post_data['post_content'] = htmlspecialchars_decode($post_data['post_content'],ENT_QUOTES);
		
		// final
		$result['action'] = true;
		$result['data'] = $post_data;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Post Save Post Data 
	// [input] : PostNo    :  \d+  = DB.system_post.pno;
	// [input] : DataModify  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADPost_Save_Post_Data( $PostNo=0 , $DataModify=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(rawurldecode($DataModify)),true);
	 
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$PostNo)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$post_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_GET_POST_EDIT_DATA()));
		$DB_GET->bindParam(':pno'   , $PostNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$post_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查更新欄位是否合法
		foreach($data_modify as $mf => $mv){
		  if(!isset($post_data[$mf])){
		    throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		  }
		  
		  if($mf == 'post_content'){
			$data_modify[$mf] = htmlspecialchars($mv,ENT_QUOTES,'UTF-8');  
		  }
		}
		
		if($data_modify && count($data_modify)){
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdPost::ADMIN_POST_UPDATE_POST_DATA(array_keys($data_modify)));
		  $DB_SAVE->bindValue(':pno' , $PostNo);
		  foreach($data_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		}
		
		// final 
		$result['data']   = $PostNo;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Post Create New Post 
	// [input] : DataCreate  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADPost_Newa_Post_Data($DataCreate='' ){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_newa   = json_decode(base64_decode(rawurldecode($DataCreate)),true);
	  
	  try{  
		
		// 檢查參數
		if(  !isset($data_newa['post_to']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['post_type']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['post_from']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['post_level']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['post_title']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		
		$date_start = isset($data_newa['post_time_start']) && strtotime($data_newa['post_time_start'])  ? date('Y-m-d H:i:s',strtotime($data_newa['post_time_start'])) : date('Y-m-d H:i:s');
		$date_end 	= isset($data_newa['post_time_end'])   && strtotime($data_newa['post_time_end']) 	? date('Y-m-d H:i:s',strtotime($data_newa['post_time_end']))   : date('Y-m-d').' 23:59:59';
		
		$DB_NEW	= $this->DBLink->prepare(SQL_AdPost::ADMIN_POST_INSERT_NEW_POST_DATA());
		
		$DB_NEW->bindParam(':post_type'  	  , $data_newa['post_type']);
		$DB_NEW->bindParam(':post_from'  	  , $data_newa['post_from']);
		$DB_NEW->bindParam(':post_to'  		  , $data_newa['post_to']);
		$DB_NEW->bindValue(':post_target'     , '_ALL');
		$DB_NEW->bindParam(':post_level'  	  , $data_newa['post_level']);
		$DB_NEW->bindParam(':post_time_start' , $date_start);
		$DB_NEW->bindParam(':post_time_end'   , $date_end );
		$DB_NEW->bindValue(':post_title'  	  , strip_tags($data_newa['post_title']));
		$DB_NEW->bindValue(':post_content'	  , htmlspecialchars($data_newa['post_content'],ENT_QUOTES,'UTF-8'));
		$DB_NEW->bindValue(':post_refer'	, '');
		$DB_NEW->bindParam(':edit_user'  	, $this->USER->UserID);
		$DB_NEW->bindParam(':edit_group'  	, $this->USER->PermissionNow['group_code']);
		
		if( !$DB_NEW->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');
		}
		
		$new_data_no  = $this->DBLink->lastInsertId('system_post');
		
		// final 
		$result['data']   = $new_data_no;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
		$result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Post Delete Post Data 
	// [input] : pno  :  \d+;
	public function ADPost_Del_Post_Data($DataNo=0){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// post_keep => 0
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_UPDATE_POST_DATA(array('post_keep'))));
		$DB_SAVE->bindParam(':pno'      , $DataNo , PDO::PARAM_INT);
		$DB_SAVE->bindValue(':post_keep' , 0 );
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $DataNo;
		$result['action'] = true;
		sleep(1);
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Post Delete Post Data 
	// [input] : pno  :  \d+;
	// [input] : Switch => 0/1
	public function ADPost_Switch_Post_Data($DataNo=0,$Switch){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$display = intval($Switch) ? 1 : 0;
		
		// post_keep => 0
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPost::ADMIN_POST_UPDATE_POST_DATA(array('post_display'))));
		$DB_SAVE->bindParam(':pno'      , $DataNo , PDO::PARAM_INT);
		$DB_SAVE->bindValue(':post_display' , $display );
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $DataNo;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
  }
?>