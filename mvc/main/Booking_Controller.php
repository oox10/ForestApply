<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  /*
  *    Forest Admin - Book Admin Module
  *    申請管理模組
  *      - Booking_Model.php
  *      -- SQL_AdBook.php
  *      - admin_book.html5tpl.php
  *      -- theme/css/css_book_admin.css
  *      -- js_book_admin.js  
  *
  *    SESSION
         - ADAREASMAP : 帳號可存取之aid index = [aid=>1]
  
  */
	
  class Booking_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Booking_Model;
	}
	
	/*[ Book Action Set ]*/ 
	
	// PAGE: book initial page
	public function index($AreaType='ALL',$Pageing='1-20',$SearchString=''){
	  $this->Model->GetUserInfo();	
	  $this->Model->Admin_Book_Get_Active_Area_List();
	  $this->Model->Admin_Book_Get_List($AreaType,$Pageing,$SearchString);
	  $this->Model->Admin_Get_Page_List(5);
	  self::data_output('html','admin_book',$this->Model->ModelResult);
	}
	
	// PAGE: book search page
	public function search($AreaType='ALL',$Pageing='1-20',$SearchString=''){
	  $this->Model->GetUserInfo();	
	  $this->Model->Admin_Book_Get_Active_Area_List();
	  $this->Model->Admin_Book_Get_List($AreaType,$Pageing,$SearchString);
	  $this->Model->Admin_Get_Page_List(5);
	  self::data_output('html','admin_book',$this->Model->ModelResult);
	}
	
	
	// AJAX: Get Target Data
	public function read($DataNo){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();	
	  $active = $this->Model->ADBook_Get_Book_Data($DataNo,$access);
	  if($active['action']){
		$this->Model->ADBook_Feform_Application_Page($DataNo);   
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: Review Apply Data
	public function review($DataNo,$ReviewData){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();	
	  $active = $this->Model->ADBook_Review_Book_Data($DataNo,$ReviewData,$access);
	  if($active['action'] && $active['data']['sentmail'] ){
		$this->Model->ADBook_Regist_Mail($DataNo);   
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: Set Apply Stage
	public function setstage($DataNo,$Stage){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();		
	  $active = $this->Model->ADBook_Set_Data_Stage($DataNo,$Stage,$access);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// FILE: Get Apply Attachment
	public function attach($DataNo,$FileName){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();		
	  $active = $this->Model->ADBook_Get_Apply_Attachment($DataNo,$FileName,$access);
	  self::data_output('file','',$this->Model->ModelResult);
	}
	
	// AJAX: Save Book data modify 
	public function save($DataCode , $DataJson ){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();			
	  if($DataCode=='_addnew'){
	   // $action = $this->Model->ADArea_Newa_Area_Data($DataJson);
	  }else{  
	    $action = $this->Model->ADBook_Save_Data($DataCode,$DataJson,$access);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: Download Apply Check // 下載陳核單 
	public function ticket($DataId){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();		
	  $active = $this->Model->ADBook_Get_Book_Data($DataId,$access);
	  if($active['action']){
		$this->Model->ADBook_Feform_Application_Page($DataId,'preview',$access); 
		self::data_output('pdf','print_apply_check',$this->Model->ModelResult);   
	  }else{
		self::redirectTo('index.php?act=Booking/denial');   
	  }
	}
	
	// AJAX: Download Apply License // 下載許可證 
	public function chklicense($DataId){
		$apply_token = ['CODE'=>$DataId,'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
	    $this->Model =  new Landing_Model;		 
	    $result = $this->Model->Apply_Record_Read( $DataId, $apply_token );
	    $active = $this->Model->Apply_Download_Check();
	    $this->Model->Access_Get_Active_Area_List();
	    self::data_output('json','',$this->Model->ModelResult);    
	}
	
	public function getlicense($DataId){
	  $apply_token = ['CODE'=>$DataId,'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
	  $this->Model =  new Landing_Model;		 
	  $result = $this->Model->Apply_Record_Read( $DataId, $apply_token );
	  $active = $this->Model->Apply_Download_Check();
	  $this->Model->Access_Get_Active_Area_List();
	  $this->Model->Apply_Feform_Application_Page($DataId,'license');
	  self::data_output('pdf','print_user_license',$this->Model->ModelResult);   
	}
	
	
	// AJAX: 批次匯出
	public function batchexport($Records){
	  $this->Model->ADBook_Export_Selected($Records);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 下載打包
	public function getexport($ExportName){
	  $this->Model->ADBook_Access_Export_File($ExportName);
	  self::data_output('file','',$this->Model->ModelResult);
	}
	
	
	/*[ Book for Check Action Set ] 警政單位申請資料查驗頁面  */ 
	
	
	// PAGE: book search page for checker R03
	public function R3check( $SearchString='' ){
	  $this->Model->GetUserInfo();	
	  $this->Model->Admin_Book_Get_Active_Area_List();
	  $this->Model->Admin_Book_Get_List_For_GroupRole($SearchString);
	  self::data_output('html','admin_book4check',$this->Model->ModelResult);
	}
	
	
	// PAGE: book search page for outreview R04
	public function R5review( $SearchString='' ){
	  $this->Model->GetUserInfo();	
	  $this->Model->Admin_Book_Get_List_For_Preview($SearchString);
	  self::data_output('html','admin_book4review',$this->Model->ModelResult);
	}
	
	
	// AJAX:Local Admin Review Apply Data // 外審人員審查
	public function localreview($DataNo,$ReviewData){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();	
	  $this->Model->ADBook_LocalAD_Review_Book_Data($DataNo,$ReviewData,$access);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX:Local Admin Review Apply Data // 外審人員批次同意
	public function locreviewaccept($ReviewData){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();	
	  $this->Model->ADBook_LocalAD_Batch_Review_Accept($ReviewData,$access);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	// AJAX: 系統檢視RAWDATA
	public function rawdata($DataNo){
	  $access = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP']) ? $_SESSION[_SYSTEM_NAME_SHORT]['ADAREASMAP'] : array();	
	  $active = $this->Model->ADBook_Get_Book_RawData($DataNo,$access);
	  echo "<pre>";
	  var_dump($this->Model->ModelResult);
	  exit(1);
	}
	
	
  }
  
  
  
  
?>