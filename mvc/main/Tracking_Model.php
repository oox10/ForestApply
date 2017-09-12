<?php

  class Tracking_Model extends Admin_Model{
    
	public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/***--  Function Set --***/
    
	//-- Get Report List Data
	// [input] : UserGroup 	: [int] ;
	// Note :  
	// 根據account role 決定存取資料範圍	
	// R00 : All Record  /   R01 : All Record  /  OTHER: SELF Report
	public function ADReport_Get_Report_List(){
	  
	  $result_key = parent::Initial_Result('tracks');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
		// 查詢資料庫
		if( isset($this->USER->PermissionNow['group_roles']['R00']) && $this->USER->PermissionNow['group_roles']['R00'] ){  
		  //1. R00 可取得所有回報資料
		  $DB_OBJ = $this->DBLink->prepare(  SQL_AdTrack::ADMIN_REPORT_SELECT_SYSTEM_REPORTS() );	
		  $result['mode']   =  'admin';
		
		}else if(isset($this->USER->PermissionNow['group_roles']['R01'])){ 
		  
		  //1. R01 可取得所有回報資料
		  $DB_OBJ = $this->DBLink->prepare(  SQL_AdTrack::ADMIN_REPORT_SELECT_SYSTEM_REPORTS() );	
		  $result['mode']   =  'admin';
		  
		  // R01現為系統管理  // 2. R01 可取得群組回報資料 
		  //$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdTrack::ADMIN_REPORT_SELECT_GROUP_REPORTS()));	
		  //$DB_OBJ->bindValue(':group_code', $this->USER->PermissionNow['group_code'] );
		
		}else{
		  //3. 其他可取得自己的回報資料
		  $DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdTrack::ADMIN_REPORT_SELECT_USER_REPORTS())); 
		  $DB_OBJ->bindValue(':user_id',$this->USER->UserID);
		
		}
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		
		$reports = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		$report_pool = array();
		foreach($reports as &$r){
		  $report_pool[] = $r['fno'];
		  switch($r['fb_status']){
		    case '_INITIAL' : $r['fb_status_info'] = "新增回報"; break;
            case '_HANDEL'  : $r['fb_status_info'] = "系統處理中"; break;
            case '_FINISH'  : $r['fb_status_info'] = "已結案"; break; 
            default: $r['fb_status_info'] = $r['fb_status']; break; 			
		  }
		}

		// 取得資料欄位
		$result['action'] = true;		
		$result['data']   = $reports;
		$result['session']['tracks']= $report_pool;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	  
	  return $result;
	}
	
	
	
	//-- Admin Report Get One Report Data 
	
	// [input] : ReportQueue  :  array();
	// [input] : ReportNo 	  :  string(0001);
	
	public function ADReport_Get_Report_Data($ReportQueue=array() , $DataNo=0){
	  $result_key = parent::Initial_Result('report');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 確定存取權限
		if( !in_array($DataNo,$ReportQueue)){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		// 取得資料
		$report_data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdTrack::ADMIN_REPORT_GET_REPORT_DATA() );
		$DB_GET->bindParam(':fno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$report_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 若管理者有讀取 add admin logs
		if( isset($this->USER->PermissionNow['group_roles']['R00']) || isset($this->USER->PermissionNow['group_roles']['R01']) ){
		  
		  if($report_data['fb_status']=='_INITIAL'){
		    $update = self::ADReport_Update_Report_Field($ReportQueue , $DataNo , array('fb_status'=>'_HANDEL'));
		  }
		  $active = self::ADReport_Set_Report_Note($ReportQueue , $DataNo , 'admin read feedback.');
		  
		  // 下行MASK因不讓資料讀取者讀到最新一筆的閱讀資料
		  //$report_logs = ($active['action']) ?   $active['data']+json_decode($report_data['fb_note'],true) : json_decode($report_data['fb_note'],true);
          
		  $report_logs = json_decode($report_data['fb_note'],true);
		  krsort($report_logs); //倒敘顯示
		  $report_data['fb_logs'] = array_unique($report_logs); // 移除重複的讀取資訊
		}
		
		// final
		$result['action'] = true;
		$result['data']   = $report_data;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  return $result;  
	}
	
	//-- Admin Report Update Report Note 
	// [input] : ReportQueue  :  array();
	// [input] : ReportNo 	  :  string(0001);
	// [input] : UMessage 	  :  string();
	public function ADReport_Set_Report_Note($ReportQueue=array() , $DataNo=0 , $UMessage=''){
	  
	  $result_key = parent::Initial_Result('note');
	  $result     = &$this->ModelResult[$result_key];
	  
	  $message_content = strip_tags(rawurldecode($UMessage));
	  
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 確定存取權限
		if( !in_array($DataNo,$ReportQueue)){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		// 確定訊息內容
		if( !strlen($message_content)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$report_data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdTrack::ADMIN_REPORT_GET_REPORT_DATA() );
		$DB_GET->bindParam(':fno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$report_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$message_index = date('Y-m-d H:i:s').' '.$this->USER->UserID;
        $report_note = json_decode($report_data['fb_note'] ,true);
		$report_note = array($message_index=>$message_content)+$report_note;
		$report_logs = json_encode($report_note);
		
		// 更新訊息
		$report_data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdTrack::ADMIN_REPORT_SET_REPORT_NOTE() );
		$DB_GET->bindParam(':fno'   , $DataNo , PDO::PARAM_INT);
		$DB_GET->bindValue(':fb_note'  , $report_logs , PDO::PARAM_STR);		
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		// final
		$result['action'] = true;
		$result['data']   = array($message_index=>$message_content);
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Report Update Report Field 
	// [input] : ReportQueue  :  array();
	// [input] : ReportNo 	  :  string(0001);
	// [input] : ModifyFieldSet  :  array();
	public function ADReport_Update_Report_Field($ReportQueue=array() , $DataNo=0 , $FieldUpdate=''){
	  
	  $result_key = parent::Initial_Result('update');
	  $result     = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// 確定存取權限
		if( !in_array($DataNo,$ReportQueue)){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		// 更新訊息
		$report_data = NULL;
		$DB_SET	= $this->DBLink->prepare( SQL_AdTrack::ADMIN_REPORT_UPDATE_REPORT_DATA(array_keys($FieldUpdate)));
		$DB_SET->bindParam(':fno'   , $DataNo , PDO::PARAM_INT);
		foreach($FieldUpdate as $mf => $mv){
		  $DB_SET->bindValue(':'.$mf , $mv);
		}
		if( !$DB_SET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// final
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- 使用者建立回報
	
	public function ADReport_User_Feedback_Submit($RpFrom='' , $RpData=array()  ){
	  
	  $result_key = parent::Initial_Result('feedback');
	  $result     = &$this->ModelResult[$result_key];
	  $report_data = json_decode(rawurldecode($RpData) ,true);
	  try{  
		
		// 確定存取權限	
		$DB_OBJ = $this->DBLink->prepare(SQL_AdTrack::ADMIN_REPORT_USER_FEEDBACK_INITIAL());
	
		$DB_OBJ->bindValue(':fb_from' 		, $RpFrom );
		$DB_OBJ->bindValue(':fb_group' 		, $this->USER->PermissionNow['group_code'] );  // 管理系統回報預設為 群組1
		$DB_OBJ->bindValue(':fb_type' 		, isset($report_data['type'])?$report_data['type']:'') ;
		$DB_OBJ->bindValue(':fb_url' 		, isset($report_data['url'])?$report_data['url']:'' );
		$DB_OBJ->bindValue(':fb_content' 	, isset($report_data['content']) ? nl2br($report_data['content']):'');
		$DB_OBJ->bindValue(':fb_preview' 	, isset($report_data['preview'])?$report_data['preview']:'' );
		$DB_OBJ->bindValue(':user_account' 	, $this->USER->UserID );
		$DB_OBJ->bindValue(':user_browse' 	, isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']:'' );
		$DB_OBJ->bindValue(':user_ip' 		, System_Helper::get_client_ip() );
		$DB_OBJ->bindValue(':fb_treatment' 	, '' );
		
		if( !$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// final
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	  
	}
	
	
  }
?>