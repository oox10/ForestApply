<?php
  /*
  *   [RCDH10 Admin Module] - Customized Module
  *   2016 ed.  
  */
  /*
  *    Hakkarchive Admin - Tracking Module Control Set
  *    回報追蹤管理模組
  *      - Tracking_Model.php
  *      -- SQL_AdTrack.php
  *      - admin_track.html5tpl.php
  *      -- theme/css/css_track_admin.css
  *      -- js_track_admin.js  
  */
	
  class Tracking_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Tracking_Model;
	}
	
	// PAGE: 回報首頁
	public function index(){
	  $this->Model->ADReport_Get_Report_List();
	  $this->Model->GetUserInfo();
	  self::data_output('html','admin_track',$this->Model->ModelResult);
	}
	
	// AJAX: 取得回報資料
	public function read($report_no){
	  $this->Model->ADReport_Get_Report_Data($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 更新回報訊息
	public function messg($report_no,$message){
	  $this->Model->ADReport_Set_Report_Note($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,$message);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 更新回報資料
	public function update($report_no,$paser_data){
	  $field_modity  = json_decode(base64_decode(rawurldecode($paser_data)),true);	
	  $this->Model->ADReport_Update_Report_Field($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,$field_modity);
	  $this->Model->ADReport_Get_Report_Data($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 回報結案
	public function endfb($report_no){
	  $this->Model->ADReport_Set_Report_Note($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,"admin close this feedback.");
	  $this->Model->ADReport_Update_Report_Field($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,array('fb_status'=>'_FINISH'));
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 提報改版
	public function request($report_no){
	  $this->Model->ADReport_Set_Report_Note($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,"admin push feedback to request..");
	  $this->Model->ADReport_Update_Report_Field($_SESSION[_SYSTEM_NAME_SHORT]['tracks'],$report_no,array('fb_status'=>'_FINISH'));
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	//AJAX : user feedback submit 
	public function submit($feedback_from,$feedback_data){
	  $this->Model->ADReport_User_Feedback_Submit($feedback_from,$feedback_data);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	
  }

?>  


