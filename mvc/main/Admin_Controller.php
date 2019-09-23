<?php
  
  /*
  *   [RCDH10 System Module] - Acmin Controller 
  *   System Main Controller For base function
  *  
  *   2016 ed.  
  */
  
  
  class Admin_Controller extends Controller{
  
    //-- 共用物件
	protected $Model     = NULL;
	protected $ActiveClass    = '';
	protected $ActiveAction   = '';
	
	//--  Class Initial
	public function __construct(){
	  
	  //RBAC : action filter 
	  $func_trace 		  = debug_backtrace();
	  
	  $this->ActiveClass  = isset($func_trace[2]['object']->nowController) ? $func_trace[2]['object']->nowController : 'unknow';
	  $this->ActiveAction = isset($func_trace[2]['object']->nowAction)     ? $func_trace[2]['object']->nowAction : 'unknow';
	  
	  if(!Admin_Model::Role_Action_Filter($this->ActiveClass,$this->ActiveAction)){
		self::denial();
		exit(1);
	  }
	}
	
	//--  Class Destruct
	public function __destruct(){		
	  // logs used info
	  if(!$this->Model){$this->Model = new Admin_Model;};
	  $this->Model->System_Logs_Used_Action($this->ActiveClass.'::'.$this->ActiveAction);
	}
	
	
	
	/********************************************
	*******  Default Action Control Func  *******
	********************************************/
	
	// FUNC: 資料匯出
	protected function data_output($IOType='' ,  $Theme='' , $IOData = array()){
	  
	  $action  = 1;        // handle model final action 
	  $result  = array();  // keep result data
	  $usrmode = array();  // save current user type
	  $message = array();  // keep all message
	  
	  // paser model result
	  foreach($IOData as $set_name => $set_result){
        
		// 處理 session
		if(isset($set_result['session'])){
		  foreach($set_result['session'] as $skey=>$sval){
		    $_SESSION[_SYSTEM_NAME_SHORT][$skey]	= $sval;
		    unset($set_result['session'][$skey]);
		  }  
	    }
		
		// 處理 output data
		if(isset($set_result['data'])){
		  $result[$set_name] = $set_result['data'];
		}
	  
	    // 處理 message 
		if(!isset($set_result['action']) || !$set_result['action']){
		  if(isset($set_result['message'])){
			if(is_array($set_result['message'])){
			  $message = array_merge($message,$set_result['message']);  
		    }else{
			  array_push($message,$set_result['message']);    
		    }  
		  }
		}
		// total action
		$action 	= $action * intval($set_result['action']);
	    
		if(isset($set_result['mode'])){
		  $usrmode[] = $set_result['mode'];
		}
	  }
	  
	  
	  if(count($result)==1 && array_keys($result)[0]===0){
		$result = array_pop($result);  
	  }
	  
	  $model_data = array('action'=>$action, 'data'=>$result);
	  $model_data['version'] = 'forest - apply v2.0 / 2019-09-30'; 
	  $model_data['mode']    = $usrmode;
	  
	  switch( strtolower($IOType) ){
	    
		case 'html':
		  $view = new Result_HTML;
		  $view->setVar('server',$model_data);
		  if(!$action){ $view->addVar('server','info',$this->Model->Get_Action_Message($message));   }
		  $view->render($Theme);
		  break;
		  
		case 'json':  
		  $view = new Result_JSON;
		  $view->setVar('server',$model_data);
		  if(!$action){ $view->addVar('server','info',$this->Model->Get_Action_Message($message));   }
		  $view->render('server');
	      break;  
        
		case 'jsonv':  
		  $view = new Result_JSONV;
		  $view->setVar('server',$model_data);
		  if(!$action){ $view->addVar('server','info',$this->Model->Get_Action_Message($message));   }
		  $view->render('server');
	      break;  
		
		case 'json-oai':
		  $view = new Result_JSONOAI;
		  $view->setVar('server',$model_data);
		  if(!$action){ $view->addVar('server','info',$this->Model->Get_Action_Message($message));   }
		  $view->render('server');
	      break;  
		
		case 'upload':  
		  $view = new Result_JSON;
		  $view->setVar('server',$model_data);
		  $view->addVar('server','link','test');
		  if(!$action){ $view->addVar('server','info',$this->Model->Get_Action_Message($message));   }
		  $view->render('server');
	      break; 
		  
		case 'session':
		  $session_tag = defined('_SYSTEM_NAME_SHORT') ? _SYSTEM_NAME_SHORT : _SYSTME_DB_NAME;
		  
		  if($Theme){
		    $_SESSION[$session_tag][$Theme] = $set_result['data'];
		  }else{
			$_SESSION[$session_tag] = $set_result['data'];  
		  }
		  
		  // SESSION 合併模式 
		  /*
		  if(isset($set_result['data']) && is_array($set_result['data'])){
		      foreach($set_result['data'] as $skey=>$sval){
		        $_SESSION[$session_tag][$Theme][$skey]	= $sval;
		      }  
	        }
		  */
		  
		  // SESSION 覆蓋模式	
          //$_SESSION[$session_tag] = $set_result['data']; 	 
          
		  break;
		
		case 'photo':
          $view = new Result_IMAGE;
		  $view->render($Theme);
		  break;
		  
		case 'xlsx':
		  if(!$action || !is_file(_SYSTEM_XLSX_PATH.$Theme) ){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
		  }else{
			$view = new Result_XLSX;  
            $view->setVar('server',$model_data);  
			$view->render($Theme);  
		  }
		  break;
		
		case 'csv':
		  if(!$action ){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
		  }else{
			$view = new Result_MetaCSV;  
            $view->setVar('server',$model_data);  
			$view->render();
		  }
		  break;  
		
		case 'pdf':
          if(!$action){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
		  }else{
			$view = new Result_PDF;    
			$view->setVar('server',$model_data);  
			$view->render($Theme);  
		  }
		  break;
		    
		  
		case 'zip':
          if(!$action){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
		  }else{
			$view = new Result_ZIP;    
			$view->setVar('server',$model_data);  
			$view->render('議事錄');  
		  }
		  break;
		
		case 'file':
		  if(!$action){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
          }else{
            $view = new Result_FILE;    
			$view->setVar('server',$model_data);
		    $view->render(); 
          }
          break;
		  
		case 'preview':
		  if(!$action){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
          }else{
            $view = new Result_FILEVIEW;    
			$view->setVar('server',$model_data);
		    $view->render(); 
          }
          break;  
		  
		
        case 'media':
		  if(!$action){ 
		    $view = new Result_HTML;
			$view->addVar('server','info',$this->Model->Get_Action_Message($message));   
		    $view->render('wrong');  
          }else{
            $view = new Result_MEDIA;    
			$view->setVar('server',$model_data);
		    $view->render(); 
          }
		  break;   
        
        
		case 'phpf':
          if($IOData['action']){
			$view = new Result_PHPF;		
		    $view->setVar('server',$IOData);
		    $view->render($Theme);     
		  }else{
			$view = new Result_HTML;  
			$view->setVar('server',$IOData);
		    if(isset($view->server['action']) && !$view->server['action'] ){
	          $view->addVar('server','info',$this->Model->Get_Action_Message());
	        }   
			$view->render('wrong');  
		  }
          break;
         
		case 'console':  
		  $view = new Result_CONSOLE;    
		  $view->setVar('server',$IOData);
		  $view->render();
		  break;  
        
		case 'error':
		  if(!$this->Model){$this->Model = new Admin_Model;};
		  $view = new Result_HTML;
		  $view->setVar('server',$IOData);
		  $view->addVar('server','info',$message[0]);   
		  $view->render('wrong');  
		  break;
		  
		default:
		  echo "OUTPUT TYPE : error ";
		  exit(1);
		  //session_unset();
		  //$this->redirectTo('index.php');
		  //header('HTTP/1.0 400 Bad Request', true, 400);
		  break;
	  }
	
	}
	
	// PAGE: 管理系統首頁
	public function index(){
	  self::data_output('html','index',array());
	}
	
	
	// PAGE: 權限錯誤 
	public function denial(){
	  if(!$this->Model){
		$this->Model = new Admin_Model;
	  }
	  
	  // check if ajax request // do not trust
      if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        $view = new Result_JSON;
		$this->Model->ModelResult['error'] = array('action'=>false,'message'=>'_SYSTEM_ERROR_PERMISSION_DENIAL');
		self::data_output('json','',$this->Model->ModelResult);
      }else{
		$this->Model->initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);  
		$this->Model->GetUserInfo();
	    //$this->Model->System_Dashboard_Data();
	    $this->Model->ModelResult['error'] = array('action'=>false,'message'=>'_SYSTEM_ERROR_PERMISSION_DENIAL');
	    self::data_output('html','admin_main',$this->Model->ModelResult); 
	  }
	}
	
	
	// AJAX: 群組切換 O
	public function gpswitch($gid){
	  $this->Model = new Admin_Model;
	  $this->Model->Group_Now_Switch($gid);
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	
	
	/*
	// PAGE: 錯誤頁面 
	protected function wrong(){
	  self::data_output('html',array(),'wrong');
	}

	protected function logout(){ 
	  session_destroy();
	  $this->redirectTo('index.php');
	}
    */
	
	
	
  }
  
?>