<?php
  
  
  /********************************************* 
  ***   ForestApply Admin Evalucation Control Set   ***
  *********************************************/
	
  /*
    
	Rule 1. 所有帳號皆可填寫 
	
	a. 編輯區域資料
	
  */  
  
  class Evaluation_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Evaluation_Model;
	}
	
	// PAGE: 評估介面 O
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADArea_Get_Area_List();
	  $this->Model->ADEvaluation_Get_Record_List();
	  self::data_output('html','admin_evaluation',$this->Model->ModelResult);
	}
	
	public function mett(){
	  $this->Model->GetUserInfo();
	  $this->Model->ADArea_Get_Area_List();
	  $this->Model->ADEvaluation_Get_Record_List();
	  self::data_output('html','admin_evaluation',$this->Model->ModelResult);
	}
	
	// AJAX: 取得區域內容
	public function read($DataID){
	  $this->Model->ADEvaluation_Get_Record($DataID);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 新增評量記錄 
	public function create($DataJson){
	  $action = $this->Model->ADEvaluation_Newa_Record($DataJson);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 更新評量記錄 
	public function update($DataID,$TableID,$DataJson){
	  $action = $this->Model->ADEvaluation_Update_Record($DataID,$TableID,$DataJson);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 帶入去年填寫資料
	public function bringin($DataID){
	  $action = $this->Model->ADEvaluation_Bringin_Record($DataID);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 完成評量記錄 
	public function finish($DataID){
	  $action = $this->Model->ADEvaluation_Finish_Record($DataID);
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	// AJAX: 下載評量記錄 
	public function download(){
	  $this->Model->ADArea_Get_Area_List();	
	  $this->Model->ADEvaluation_Export_Records();
	  self::data_output('file','',$this->Model->ModelResult); 
	}
	
	
	
	
	
	
	// AJAX: 儲存編輯區域 
	public function save($DataCode , $DataJson , $BlockJson=''){
	  if($DataCode=='_addnew'){
	    $action = $this->Model->ADArea_Newa_Area_Data($DataJson);
	  }else{  
	    $action = $this->Model->ADArea_Save_Area_Data($DataCode,$DataJson,$BlockJson);
	  }
	  
	  if($action['action']){
		$this->Model->ADArea_Get_Area_Data($action['data']);
	  }
	  self::data_output('json','',$this->Model->ModelResult); 
	}
	
	
	// AJAX: 開啟資料
	public function show($DataCode){
	  $this->Model->ADArea_Switch_Area_Open($DataCode,1);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 關閉資料
	public function mask($DataCode){
	  $this->Model->ADArea_Switch_Area_Open($DataCode,0);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
    // AJAX: 刪除資料
	public function dele($DataCode){
	  $this->Model->ADArea_Del_Area_Data($DataCode);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	/*== [ Module - STOP DATE ] ==*/
	
	// AJAX: 儲存禁申日期
	public function stop_save( $DataCode , $DataJson ){
	  $this->Model->ADArea_Stop_Date_Save($DataCode,$DataJson);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除禁申日期
	public function stop_dele( $DataCode , $StopNo ){
	  $this->Model->ADArea_Stop_Date_Delete($DataCode,$StopNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 取消申請單
	public function stop_active( $DataCode , $StopNo ){
	  $this->Model->ADArea_Stop_Date_Active($DataCode,$StopNo);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	/*== [ Module - BLOCK EDIT ] ==*/
	
	
	
	// AJAX: 上傳照片
	public function addimg($DataCode){
	  $this->Model->ADArea_Add_Area_Image($DataCode,$_FILES);
	  self::data_output('html','load_referimg',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除參考資料
	public function delrefer($DataCode,$ReferType,$ReferIndex=''){
	  $this->Model->ADArea_Del_Area_Refer($DataCode,$ReferType,$ReferIndex);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 新增子區
	public function addblock($DataCode){
	  $this->Model->ADArea_Add_Area_Block($DataCode);
	   self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 刪除子區
	public function delblock($BlockId){
	  $this->Model->ADArea_Del_Area_Block($BlockId);
	   self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	/*== [ Module - FORM CONFIG ] ==*/
	
	// AJAX: save apply form config
	public function formconfig($DataCode,$FormConfig){
	  $this->Model->ADArea_Save_Area_Form_Config($DataCode,$FormConfig);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
  }
  
  
  
  
  
?>


