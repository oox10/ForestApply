<?php
  
  /*
  *    ForestApply Admin - Page Module Control Set
  *    頁面管理模組
  *      - Page_Model.php
  *      -- SQL_AdPage.php
  *      - admin_post.html5tpl.php
  *      -- theme/css/css_post_admin.css
  *      -- js_post_admin.js  
  */
	
  class Page_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Page_Model;
	}
	
	// PAGE: 管理訊息介面 O
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADPage_Get_Config();
	  $this->Model->ADPage_Get_Page_List();
	  self::data_output('html','admin_page',$this->Model->ModelResult);
	}
	
	
	
	// AJAX: 取得消息內容
	public function read($DataNo){
	  $this->Model->ADPage_Get_Target_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存編輯消息 
	public function save($DataNo , $DataJson){
	  if($DataNo=='_addnew'){
	    $action = $this->Model->ADPage_Newa_Page_Data($DataJson);
	  }else{  
	    $action = $this->Model->ADPage_Save_Page_Data($DataNo,$DataJson);
	  }
	  
	  if($action['action']){
		$this->Model->ADPage_Get_Target_Data($action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
    // AJAX: 刪除消息
	public function dele($DataNo){
	  $this->Model->ADPost_Del_Post_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
  }
  
  
  
  
  
?>


