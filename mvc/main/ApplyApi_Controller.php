<?php
class ApplyApi_Controller extends Admin_Controller{
   
  /*
  *    Forest  AreaBooking System - Client Api Module
  *    API界接客戶端模組
  *      - Landing_Model.php
  * 
  */
  
	//-- 共用物件
	public  $IPSaveZoon;
	
	//初始化要執行的動作以及物件
	public function __construct(){
	  parent::__construct();	
      
	  $this->Model = new Landing_Model;		
	  //$this->IPSaveZoon = json_decode(_USER_IP_SAVE_ZOON,true);
	  //header('HTTP/1.0 400 Bad Request', true, 400);
	  //exit(1);
	}
	
	
	// JSON: 申請區域清單 
	public function paareas($Group=''){   
	  $this->Model->Access_Get_Active_Area_List($Group);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// JSON: 申請目標區域資訊 
	public function areainfo($AreaCode='',$CalendarStart='_now'){
	  $this->Model->Access_Get_Select_Area_Meta($AreaCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	//json_decode(base64_decode(str_replace('*','/',rawurldecode($Applicant))),true); 
	
	
	// AJAX : 初始化申請
	public function signon($ApplyCode=''){
	  $agentpost = isset($_REQUEST['applicant']) ? $_REQUEST['applicant'] : "[]";
	  $ApplicantData = rawurlencode(str_replace('/','*',base64_encode($agentpost)));
	  $this->Model->Apply_Record_SignOn($ApplicantData,$ApplyCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX : 更新申請資料
	public function applyform($ApplyCode='',$LoginKey=''){     
	  $agentpost = isset($_REQUEST['application']) ? $_REQUEST['application'] : "[]";
	  $ApplicationData = rawurlencode(str_replace('/','*',base64_encode($agentpost)));
	  
	  if($loginkey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;  
	  }
	  
	  $this->Model->Apply_Input_ApplyForm($ApplyCode,$ApplicationData,$apply_token);
	  $this->Model->Apply_Get_Member_Record($ApplyCode,$apply_token);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 儲存成員名單
	public function savembr($ApplyCode='',$LoginKey=''){
		
	  $agentpost = isset($_REQUEST['member']) ? $_REQUEST['member'] : "[]";
	  $MemberString = rawurlencode(str_replace('/','*',base64_encode($agentpost)));	
		
	  if($LoginKey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
		$applicant   = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLICANT'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;	
	    $applicant   = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLICANT']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLICANT'] : array();
	  }	
	  
	  $this->Model->Apply_Save_MbrEdit($ApplyCode,$MemberString,$apply_token,$applicant);
      self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// AJAX : 遞交申請資料
	public function submit($ApplyCode='',$LoginKey=''){
	  //提取資料必須先登入或先前有註冊
	  
	  if($LoginKey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;  
	  }
	  
	  $result = $this->Model->Apply_Record_Check($ApplyCode,$apply_token);
      self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX : 取消申請資料
	public function cancel($ApplyCode,$LoginKey=''){   
	  
	  if($LoginKey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;  
	  }
	  
	  $this->Model->Apply_Record_Cancel($ApplyCode,$apply_token);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// JSON: 取得申請狀態 
	public function status($ApplyCode='',$LoginKey=''){
	  if($LoginKey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;  
	  }
	  
	  $this->Model->Apply_License_Status_Rawdata($ApplyCode,$apply_token);	
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: Download Apply License // 下載許可證 
	public function download($ApplyCode='',$LoginKey=''){
	  
	  if($LoginKey&&isset($_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey])){
		$apply_token = $_SESSION[_SYSTEM_NAME_SHORT]['LOGINCACHE'][$LoginKey]['APPLYTOKEN'];
	  }else{
		$apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;  
	  }
	  
	  $result = $this->Model->Apply_Record_Read( $ApplyCode, $apply_token );
	  $active = $this->Model->Apply_Download_Check();
	  if(!$result['action'] || !$active['action']){
		self::data_output('json','',$this->Model->ModelResult);    
	    exit(1);
	  }
	  $this->Model->Apply_Feform_Application_Page($ApplyCode,'license');
	  self::data_output('pdf','print_user_license',$this->Model->ModelResult);   
	}
	
	
	
	
	
}