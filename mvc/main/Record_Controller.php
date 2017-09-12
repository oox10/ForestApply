<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  /*
  *    Forest Admin - Record Admin Module
  *    申請管理模組
  *      - Record_Model.php
  *      -- SQL_AdRecord.php
  *      - admin_record.html5tpl.php
  *      -- theme/css/css_record_admin.css
  *      -- js_record_admin.js  
  */
	
  class Record_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Record_Model;
	}
	
	// PAGE: 統計資料葉面
	public function index( $D_Start='',$D_to=''){
	  $this->Model->GetUserInfo();
	  $this->Model->ADRecord_Get_Apply_Record($D_Start , $D_to);
	  self::data_output('html','admin_record',$this->Model->ModelResult);
	}
	
	// PAGE: 統計資料葉面
	public function search( $D_Start='',$D_to='' ){
	  $this->Model->GetUserInfo();
	  $this->Model->ADRecord_Get_Apply_Record($D_Start , $D_to);
	  self::data_output('html','admin_record',$this->Model->ModelResult);
	}
	
	// FILE: 下載申請清單
	public function export( $D_Start='',$D_to='' ){
	  $this->Model->ADRecord_Get_Apply_Record($D_Start , $D_to);
      $this->Model->ADRecord_Built_Record_File('template_record.xlsx');
	  self::data_output('file','',$this->Model->ModelResult);
	}
	
  }
  
  
  
  
?>