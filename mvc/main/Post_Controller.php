<?php
  
  /*
  *    ForestApply Admin - Post Module Control Set
  *    公告管理模組
  *      - Post_Model.php
  *      -- SQL_AdPost.php
  *      - admin_post.html5tpl.php
  *      -- theme/css/css_post_admin.css
  *      -- js_post_admin.js  
  */
	
  class Post_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Post_Model;
	}
	
	// PAGE: 管理訊息介面 O
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADPost_Get_Post_Config();
	  $this->Model->ADPost_Get_Post_List();
	  self::data_output('html','admin_post',$this->Model->ModelResult);
	}
	
	// AJAX: 取得消息內容
	public function read($DataNo){
	  $this->Model->ADPost_Get_Post_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存編輯消息 
	public function save($DataNo , $DataJson){
	  if($DataNo=='_addnew'){
	    $action = $this->Model->ADPost_Newa_Post_Data($DataJson);
	  }else{  
	    $action = $this->Model->ADPost_Save_Post_Data($DataNo,$DataJson);
	  }
	  
	  if($action['action']){
		$this->Model->ADPost_Get_Post_Data($action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 開啟公告
	public function show($DataNo){
	  $this->Model->ADPost_Switch_Post_Data($DataNo,1);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 關閉消息
	public function mask($DataNo){
	  $this->Model->ADPost_Switch_Post_Data($DataNo,0);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
    // AJAX: 刪除消息
	public function dele($DataNo){
	  $this->Model->ADPost_Del_Post_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
  }
  
  
  
  
  
?>


