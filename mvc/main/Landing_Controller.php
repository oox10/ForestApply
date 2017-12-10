<?php
class Landing_Controller extends Admin_Controller{
    
  /*
  *   [RCDH10 Archive Module] - Customized Module
  *   2017 ed.  
  */
  
  /*
  *    Forest  AreaBooking System - Client Module
  *    客戶端模組
  *      - Landing_Model.php
  *      -- SQL_Client.php
  *      - client_landing.html5tpl.php / 首頁
  *      -- theme/css/css_landing.css
  *      -- js_landing.js
  *      - client_booking.html5tpl.php / 申請頁
  *      -- theme/css/css_booking.css
  *      -- js_apply.js  
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
	
	
	/***--- LANDING ACTION SET ---***/
	
	// PAGE: client landing page
	public function index(){   
	  $this->Model->Access_Get_Client_Post_List();
	  $this->Model->Access_Get_Active_Area_List();
	  self::data_output('html','client_landing',$this->Model->ModelResult);
	}
	
	// AJAX : 取得地區日程表
	public function schedule($ApplyCode,$CalendarStart,$CalendarNum){
	  $this->Model->Access_Get_Select_Area_Info($ApplyCode);	
	  $this->Model->Access_Get_Select_Area_Date($ApplyCode,$CalendarStart,intval($CalendarNum));
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	/***--- SEARCH ACTION SET ---***/
	// ACTION: client search page
	public function verify($UserSubmit){
	  $result = $this->Model->Check_User_Applied_Data($UserSubmit);	
	  if(!$result['action']){
		$this->Model->Access_Get_Active_Area_List();  
		self::data_output('html','wrong',$this->Model->ModelResult);  
	    exit(1);
	  }
	  
	  self::data_output('session','APPLYTOKEN',$this->Model->ModelResult);
	  self::redirectTo('index.php?act=Landing/license/'.$result['code']);
	  
	}
	
	// PAGE: client link to license page
	public function direct($ApplyCode='',$AccessKey=''){
	  $result = $this->Model->Check_User_Applied_Link($ApplyCode,$AccessKey);	
	  if(!$result['action']){
		$this->Model->Access_Get_Active_Area_List();
		self::data_output('html','wrong',$this->Model->ModelResult);  
		exit(1);
	  }	
	  self::data_output('session','APPLYTOKEN',$this->Model->ModelResult);
	  self::redirectTo('index.php?act=Landing/license/'.$ApplyCode);  
	}
	
	
	
	/***--- BOOKING ACTION SET ---***/
	
	// PAGE: client booking page
	public function reserve($AreaCode){
	  $MonthStart = '_now'; //default month
      $MonthLength=1;  
	  $this->Model->Access_Get_Active_Area_List();
	  
	  $active = $this->Model->Access_Get_Select_Area_Info($AreaCode);
	  if(!$active['action']){
		self::data_output('html','wrong',$this->Model->ModelResult);   
	    exit(1);
	  }
	  
	  $this->Model->Access_Get_Select_Area_Date($AreaCode,$MonthStart,$MonthLength);
	  $this->Model->Access_Get_Area_DatePicker_Config($AreaCode);
	  self::data_output('html','client_booking',$this->Model->ModelResult);
	  
	}
	
	// AJAX : 查詢申請人資料
	public function history($ApplicantData){   
	  $this->Model->Applicant_Record_Search($ApplicantData);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX : 初始化申請
	public function initial($ApplicantData,$ApplyCode=''){   
	  $this->Model->Apply_Record_Initial($ApplicantData,$ApplyCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 上傳與處理清單
	public function uplath($ApplyNo=0){
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;
	  $active = $this->Model->Apply_Upload_Attachment($ApplyNo,$_FILES,$apply_token);
      self::data_output('html','load_attachmnt',$this->Model->ModelResult);
	}
	
    // AJAX : 更新申請資料
	public function applyform($ApplyNo,$Application){   
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;
	  $this->Model->Apply_Input_ApplyForm($ApplyNo,$Application,$apply_token);
	  $this->Model->Apply_Get_Member_Record($ApplyNo,$apply_token);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// FILE: 下載空白人員清單
	public function getlist($ApplyNo=0){
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;	
	  $this->Model->Apply_Get_Member_Record($ApplyNo,$apply_token);
      $this->Model->Apply_Built_Member_List_File('template_apply_member_list.xls');
      self::data_output('file','',$this->Model->ModelResult);
	}
	
	// AJAX: 上傳與處理清單
	public function uplmbr($ApplyNo=0){
	  $active = $this->Model->Apply_Upload_MbrFile($ApplyNo,$_FILES);
      if($active['action']){
		$this->Model->Apply_Process_MbrFile($ApplyNo,$active['data']);  
	  }
	  self::data_output('html','load_member',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存成員名單
	public function savembr($ApplyNo=0,$MemberString){
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;	
	  $applicant   = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLICANT']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLICANT'] : array();
	  $this->Model->Apply_Save_MbrEdit($ApplyNo,$MemberString,$apply_token,$applicant);
      self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// AJAX : 遞交申請資料
	public function submit($ApplyNo){   
	  //提取資料必須先登入或先前有註冊
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;	
	  $result = $this->Model->Apply_Record_Check($ApplyNo,$apply_token);
      if($result['action']){
		$this->Model->Landing_Regist_Mail($ApplyNo);  
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	//== PAGE : apply status width license
	public function license($ApplyNo,$ShowType='preview'){
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;	
	  $result = $this->Model->Apply_License_Status_Read($ApplyNo,$apply_token);	
	  if(!$result['action']){
		self::redirectTo('index.php');  
		exit(1);
	  }
	  $this->Model->Access_Get_Active_Area_List(); 
	  $this->Model->Apply_Feform_Application_Page($ApplyNo,$ShowType); 
	  self::data_output('html','client_license',$this->Model->ModelResult);  
	} 
	
	
	
	// AJAX : 讀取申請資料
	public function recover($ApplyNo){   
	  //提取資料必須先登入或先前有註冊
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;
	  $this->Model->Apply_Record_Read( $ApplyNo, $apply_token );
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX : 讀取申請資料
	public function cancel($ApplyNo){   
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;
	  $this->Model->Apply_Record_Cancel($ApplyNo,$apply_token);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: Download Apply License // 下載陳核單 
	public function download($ApplyNo){
	  $apply_token = isset($_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN']) ? $_SESSION[_SYSTEM_NAME_SHORT]['APPLYTOKEN'] : false;
	  $result = $this->Model->Apply_Record_Read( $ApplyNo, $apply_token );
	  $active = $this->Model->Apply_Download_Check();
	  $this->Model->Access_Get_Active_Area_List();
	  if(!$result['action'] || !$active['action']){
		self::data_output('html','wrong',$this->Model->ModelResult);    
	    exit(1);
	  }
	  $this->Model->Apply_Feform_Application_Page($ApplyNo,'license');
	  self::data_output('pdf','print_user_license',$this->Model->ModelResult);  
	   
	}
	
	
	/***--- POST ACTION SET ---***/
	// PAGE: get client announcement
	public function getann($DataNo){   
	  $this->Model->Get_Client_Post_Target($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	/***--- Applied ---***/
	// PAGE: get client area applied 各區申請情況
	public function applied(){   
	  $this->Model->Access_Get_Active_Area_List();
	  self::data_output('html','client_applied',$this->Model->ModelResult);
	}
	
	// AJAX : 讀取申請資料
	public function lotto($AreaCode,$InterDate){   
	  $this->Model->Get_Area_Lotto_Data($AreaCode,$InterDate);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
}
?>