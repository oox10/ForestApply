<?php
  
  
  /********************************************* 
  ** Council MtDoc Collect Staff Control Set **
  *********************************************/
	
  class Staff_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Staff_Model;
	}
	
	// PAGE: 員工管理介面 O
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADStaff_Get_Rouls_Data();
	  $this->Model->ADStaff_Get_Staff_List();
	  self::data_output('html','admin_staff',$this->Model->ModelResult);
	}
	
	// AJAX: 取得員工資料 O
	public function read($staff_no){
	  $this->Model->ADStaff_Get_Staff_Data($staff_no);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存員工資料 
	public function save($StaffNo , $DataJson,$UserRoles){
	  if($StaffNo=='_addnew'){
	    $action = $this->Model->ADStaff_Newa_Staff_Data($DataJson,$UserRoles);
	  }else{
	    $action = $this->Model->ADStaff_Save_Staff_Data($StaffNo,$DataJson,$UserRoles);
	  }
	  if($action['action']){
		$this->Model->ADStaff_Get_Staff_Data($action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
    // AJAX: 刪除員工帳號 O
	public function dele($StaffNo){
	  $this->Model->ADStaff_Del_Staff_Data($StaffNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
    
	// AJAX: 寄發帳號開通通知信
	public function startmail($StaffNo){
	  $this->Model->ADStaff_Staff_Account_Accept_Mail($StaffNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 取得群組成員清單(包含非主要群組)
	public function gmember(){
	  $this->Model->ADStaff_Get_Group_List();	
	  $this->Model->ADStaff_Get_Group_Memners();
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 加入群組成員
	public function gpadd($user,$group,$roles='',$Filter=''){
	  $this->Model->ADStaff_Add_Group_Memner($user,$group,$roles,$Filter);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 移出群組成員
	public function gpdef($user,$group){
	  $this->Model->ADStaff_Del_Group_Memner($user,$group);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存群組資料
	public function gsave($group){
	  $this->Model->ADStaff_Group_Meta_Save($group);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除群組資料
	public function gdele($GroupCode=''){
	  $this->Model->ADStaff_Group_Meta_Delete($GroupCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	
	
  }
  
  
  
  
  
?>