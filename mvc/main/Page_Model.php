<?php

  class Page_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Page Function Set ]*/ 
    
	
	//-- Admin Site Page Config 
	// [input] : NULL;
	public function ADPage_Get_Config(){
	  
	  $result_key = parent::Initial_Result('config');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 取得群組設定
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPage::GET_SYSTEM_GROUPS()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$group_list = array();
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $group_list[$tmp['ug_code']] = $tmp['ug_name'];
		}
		
		$result['data']['group']   = $group_list;
        
		$result['action'] = true;
	   
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Post Page Data OList 
	// [input] : NULL;
	public function ADPage_Get_Page_List(){
	  
	  $result_key = parent::Initial_Result('records');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPage::GET_PAGE_LIST()));
		$DB_OBJ->execute(array('master'=>$this->USER->PermissionNow['group_code']));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		// 取得最新消息清單
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
		
		$result['action'] = true;		
		$result['data']   = $data_list;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	//-- Admin Page Get target  
	// [input] : DataNo  :  system_pages.spno \d+;
	public function ADPage_Get_Target_Data($DataNo=0){
		
	  $result_key = parent::Initial_Result('read');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	   
		// 取得資料
		$page = NULL;
		$DB_GET	= $this->DBLink->prepare( parent::SQL_Permission_Filter(SQL_AdPage::GET_PAGE_DATA()) );
		$DB_GET->bindParam(':spno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$page = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
	    
		// 轉換內容
		$page['page_content'] = htmlspecialchars_decode($page['page_content'],ENT_QUOTES);
		
		// final
		$result['action'] = true;
		$result['data']   = $page;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Post Save Post Data 
	// [input] : DataNo    	:  \d+  = DB.system_page.spno;
	// [input] : DataModify  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADPage_Save_Page_Data( $DataNo=0 , $DataModify=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataModify))),true);
	 
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$DataNo)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$post_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPage::GET_DB_EDIT_DATA()));
		$DB_GET->bindParam(':spno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$post_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查更新欄位是否合法
		foreach($data_modify as $mf => $mv){
		  if(!isset($post_data[$mf])){
		    throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		  }
		  
		  if($mf == 'page_content'){
			$data_modify[$mf] = htmlspecialchars($mv,ENT_QUOTES,'UTF-8');  
		  }
		}
		
		if($data_modify && count($data_modify)){
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdPage::UPDATE_PAGE_DATA(array_keys($data_modify)));
		  $DB_SAVE->bindValue(':spno' , $DataNo);
		  foreach($data_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		}
		
		// final 
		$result['data']   = $DataNo;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Post Create New Post 
	// [input] : DataCreate  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADPage_Newa_Page_Data($DataCreate='' ){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_newa   = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataCreate))),true);
	  
	  try{  
		
		// 檢查參數
		if(  !isset($data_newa['page_type']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['page_owner']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['page_show']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['page_title']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['page_content']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$DB_NEW	= $this->DBLink->prepare(SQL_AdPage::INSERT_NEW_PAGE());
		$DB_NEW->bindParam(':page_type'  	  , $data_newa['page_type']);
		$DB_NEW->bindParam(':page_owner'  	  , $data_newa['page_owner']);
		$DB_NEW->bindParam(':page_show'  	  , $data_newa['page_show']);
		$DB_NEW->bindParam(':page_title'      , $data_newa['page_title']);
		$DB_NEW->bindValue(':page_content'    , htmlspecialchars($data_newa['page_content'],ENT_QUOTES,'UTF-8'));
		$DB_NEW->bindParam(':user'  		  , $this->USER->UserID);
		 
		if( !$DB_NEW->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');
		}
		
		$new_data_no  = $this->DBLink->lastInsertId('system_pages');
		
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
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPage::ADMIN_POST_UPDATE_POST_DATA(array('post_keep'))));
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
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdPage::ADMIN_POST_UPDATE_POST_DATA(array('post_display'))));
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