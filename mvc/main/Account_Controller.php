<?php
class Account_Controller extends Admin_Controller{
  
    
	//-- 共用物件
	public  $IPSaveZoon;
	
	//初始化要執行的動作以及物件
	public function __construct(){
	  parent::__construct();	
      
	  $this->Model = new Account_Model;		
	  //$this->IPSaveZoon = json_decode(_USER_IP_SAVE_ZOON,true);
	  //header('HTTP/1.0 400 Bad Request', true, 400);
	  //exit(1);
	}
	
	
    /********************************************* 
    ***** Council MtDoc Account Control Set *****
    *********************************************/
	
	
	/***--- SIGN ACTION SET ---***/
	
	// PAGE: system sign page
	public function index(){   
	  self::data_output('html','account_login',$this->Model->ModelResult);
	}
	
	// AJAX: system login paser
	// [input] LoginRefer : encode string 
	public function signin($LoginRefer = array()){
	  $this->Model->Account_Login_Process($LoginRefer);
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
    // PAGE: system forgot page
	public function forgot(){   
	  self::data_output('html','account_forgot',$this->Model->ModelResult);
	}
	
	// AJAX: system password seset mail
	// [input] DataRefer : encode string 
	public function reseter($DataRefer = array()){
	  $verificationCode = isset($_SESSION['turing_string']) ? $_SESSION['turing_string'] : NULL;	  
	  $this->Model->Account_Sent_RePassword_Mail($DataRefer,$verificationCode);
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	// PAGE: account inter system 
	public function inter($InterKey=''){
	  $result = $this->Model->Account_Inter_System($InterKey);
	  
	  if(!$result['action']){
		self::data_output('html','account_login',$this->Model->ModelResult); 
	    exit(1);
	  }
	  
	  self::data_output('session','ADMIN',$this->Model->ModelResult);
	  
	  if(isset($_SESSION[_SYSTEM_NAME_SHORT]['RrDIRECT']) && $_SESSION[_SYSTEM_NAME_SHORT]['RrDIRECT'] !=''){
		// 舊有連結導回搜尋頁面
		self::redirectTo('index.php?'.$_SESSION[_SYSTEM_NAME_SHORT]['RrDIRECT']);   
		unset($_SESSION[_SYSTEM_NAME_SHORT]['RrDIRECT']);
	  }else{
		self::redirectTo('index.php?act=Staff'); 
	  }
	  
	  
	}
	
	// PAGE: account logout 
	public function logout(){
      session_unset();
	  $this->redirectTo('index.php');
	}
	
	// PAGE: account unlogin  
	public function unlogin(){
      $this->Model->ModelResult[0] = array('action'=>false,'message'=>'_LOGIN_INFO_ACCOUNT_UNLOGIN');
	  self::data_output('html','account_login',$this->Model->ModelResult);
	}
	
	
	/***--- REGIST ACTION SET ---***/
	
	
	// PAGE: system account regist page
	public function regist(){
	  $this->Model->Account_Get_Regist_Group_List();	
	  self::data_output('html','account_register',$this->Model->ModelResult); 
	} 
	
	// AJAX: system account register //使用者註冊申請
	public function signup($Captcha,$RegData){
	  $captcha_save = isset($_SESSION['turing_string']) ? $_SESSION['turing_string'] : false ;
	  $captcha_user = strlen($Captcha) ? $Captcha : '';
	  $account_data = strlen($RegData) ? json_decode(rawurldecode($RegData),true) : array();
	  $regist = $this->Model->Account_Sign_Up($captcha_save,$captcha_user,$account_data);
	  if($regist['action']) $this->Model->Account_System_Space_Allocat($regist['data']['group'],$regist['data']['account']);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// PAGE: system account start //使用者帳號啟動，開啟重設密碼頁面
	public function start($regCode=''){
	  $result  = $this->Model->Check_Regist_Code($regCode);
	  if($result['action']){
	    self::data_output('html','account_repass',$this->Model->ModelResult);
	  }else{
		self::redirectTo('index.php?act=Error/error/'.$this->Model->Get_Action_Message($result['message']));
	  }
	}

	// AJAX: system account pw initial //新使用者設定密碼
	public function repass($PassData){
	  $reg_code = isset($_SESSION[_SYSTEM_NAME_SHORT]['regcode']) ? $_SESSION[_SYSTEM_NAME_SHORT]['regcode'] : '';
	  $reg_uno  = isset($_SESSION[_SYSTEM_NAME_SHORT]['user_no']) ? $_SESSION[_SYSTEM_NAME_SHORT]['user_no'] : NULL;
	  $reg_uid  = isset($_SESSION[_SYSTEM_NAME_SHORT]['user_id']) ? $_SESSION[_SYSTEM_NAME_SHORT]['user_id'] : NULL;
	  $this->Model->Account_Password_Initial($reg_code,$reg_uno,$reg_uid,$PassData);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
}
?>