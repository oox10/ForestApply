<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  /*
  *    Forest Admin - Ballot Admin Module
  *    抽籤管理模組
  *      - Lotto_Model.php
  *      -- SQL_AdLotto.php
  *      - admin_lotto.html5tpl.php
  *      -- theme/css/css_lotto_admin.css
  *      -- js_lotto_admin.js  
  */
	
  class Lotto_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Lotto_Model;
	}
	
	// PAGE: 管理抽籤介面
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADArea_Get_Area_List();
	  $this->Model->ADLotto_Get_Lotto_List('_all','');
	  self::data_output('html','admin_lotto',$this->Model->ModelResult);
	}
	
	// PAGE: 管理抽籤介面
	public function search($TargetArea='_all',$TargetDate=''){
	  $this->Model->GetUserInfo();
	  $this->Model->ADArea_Get_Area_List();
	  $this->Model->ADLotto_Get_Lotto_List($TargetArea,$TargetDate);
	  self::data_output('html','admin_lotto',$this->Model->ModelResult);
	}
	
	// AJAX: 讀取抽籤資料
	public function read($DataNo=0){
	  $this->Model->ADLotto_Read_Lotto_Data($DataNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 建立抽籤資料
	public function built( $Date='' ){
      $scan_date = strtotime($Date) ? date('Y-m-d',strtotime($Date)) : date('Y-m-d');	
	  $this->Model->ADLotto_Built_Lotto_Data($scan_date);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 執行抽籤當日抽籤
	public function active($Target=0, $Date=''){
      $scan_date = strtotime($Date) ? date('Y-m-d',strtotime($Date)) : date('Y-m-d');	
	  $this->Model->ADLotto_Active_Booking_Lotto($Target,$scan_date);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
  }
  
  
  
  
?>