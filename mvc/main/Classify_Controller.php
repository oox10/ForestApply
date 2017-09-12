<?php
  
  class Classify_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Classify_Model;
	}
	
	// PAGE: 分類管理介面
	public function index(){
	  
	  $this->Model->GetUserInfo();
	  $this->Model->GetUserGroups();
	  $this->Model->Get_User_Classify_Data();
	  
	  self::data_output('html','admin_classify',$this->Model->ModelResult);
	}
	
	// AJAX: 儲存詞彙修改
	public function modify($TermId,$TermName){
	  $this->Model->Save_Term_Name_Modify( $TermId,$TermName );
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	
	// AJAX: 刪除詞彙
	public function delete($TermId){
	  $this->Model->Delete_Term( $TermId);
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	// AJAX: 新增專題
	public function lvadd($UpLevelNo,$NewLevelCode,$NewLevelTerm){
	  $this->Model->Insert_New_ClassLevel( $UpLevelNo,$NewLevelCode,$NewLevelTerm);
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	
	
	// FILE: 匯出資料
    public function excel($type='index',$condition1='',$condition2=''){
	  
	  switch($type){
		case 'index':
        case 'project':		
		  $access = $this->Model->ADRecord_Get_Project_Record($condition1);
          if($access['action']){
		    $this->Model->ModelResult['excel'] = array('action'=>true,'data'=>$this->Model->ModelResult['projects']['data']);
			self::data_output('xlsx',$type,$this->Model->ModelResult);
		  }
		  break;
		case 'account':
		  //$this->Model->ADRecord_Get_Project_Record($filter);
          break;
		case 'doclog':
		  $access = $this->Model->ADRecord_Get_Document_Info($condition1);
	      if($access['action']){
		    $this->Model->ADRecord_Get_Document_Logs($access['data']['docId'],$condition2);    
	        $this->Model->ModelResult['excel'] = array('action'=>true,'data'=>$this->Model->ModelResult['doclogs']['data']);
			
			
			
			self::data_output('xlsx',$type,$this->Model->ModelResult);
		  }
		  break;
	    default:break;
	  }
	}
	
	
	
	
  }	