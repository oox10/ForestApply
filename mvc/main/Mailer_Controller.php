<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  /*
  *    AHAS Admin - Mailer Module Control Set
  *    信件管理模組
  *      - Mailer_Model.php
  *      -- SQL_AdMailer.php
  *      - admin_mailer.html5tpl.php
  *      -- theme/css/css_mailer_admin.css
  *      -- js_mailer_admin.js  
  */
	
  class Mailer_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Mailer_Model;
	}
	
	// PAGE: 管理訊息介面 O
	public function index($DataType='_all'){
	  $this->Model->GetUserInfo();
	  $this->Model->ADMailer_Get_Mailer_List($DataType);
	  self::data_output('html','admin_mailer',$this->Model->ModelResult);
	}
	
	// AJAX: 取得發信內容
	public function read($DataNo){
	  $this->Model->ADMailer_Get_Mailer_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存信件 
	public function save($DataNo , $DataJson){
	  if($DataNo=='_addnew'){
	    //$action = $this->Model->ADMailer_Newa_Mailer_Data($DataJson);
	  }else{  
	    $action = $this->Model->ADMailer_Save_Mailer_Data($DataNo,$DataJson);
	  }
	  
	  if($action['action']){
		$this->Model->ADMailer_Get_Mailer_Data($action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 發送信件
	public function sent($DataNo){
	  $action = $this->Model->ADMailer_Mail_Sent_Now($DataNo);
	  if($action['action']){
		$this->Model->ADMailer_Get_Mailer_Data($DataNo);
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
    // AJAX: 刪除消息
	public function dele($DataNo){
	  $this->Model->ADMailer_Del_Mailer_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX : regist Mail
	public function regist($ApplyCode){   
	  $this->Model->Mailer_Regist_Mail($ApplyCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	
  }
  
  
  
  
  
?>


