<?php
  
  /*
  *   [RCDH10 System Module] - Router Object 
  *   MVC Action Root Object 
  *   - check controller & action
  *   - check action & base filter 
  *   
  *   2016 ed.  
  */
  
  class Router{
	  
	
	protected $objController;
	public $nowController;
	public $nowAction;
	public $nowArgs;
	
	public function route(Request_url $request){
	  
	  $controller     = $request->getController();
	  $action         = $request->getAction();
      $this->nowArgs  = $request->getArgs();
	  
	  try{
		
		try{
			switch($controller){
			
			  // unfilter
			  case 'Account': break;
			  case 'Landing': break;
			  case 'Error': break;
			  case 'Jobs': break;
			  
			  // archive
			  case 'Display':
			  
				if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['CLIENT']['CLIENT_LOGIN_TOKEN'])){
				   throw new Exception('re set to default page');
				}
				break;
			
			  // admin
			  case 'Main': case 'Staff': case 'Booking': case 'Post': case 'Area': case 'Lotto': case 'Record': case 'Tracking':  case 'Mailer':  
			  case 'Archive': case 'Classify':
				
				// 檢查登入
				if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['ADMIN_LOGIN_TOKEN'])){
					
				  // 檢查是否為查驗單位直通
				  if(  $controller=='Booking' && ($action=='R3check' || $action=='R5review') ){
					$_SESSION[_SYSTEM_NAME_SHORT]['RrDIRECT'] = $_SERVER["QUERY_STRING"]; // 暫存搜尋條件
				  }
				  
				  throw new Exception('re set to default page');
				}
				
				$ctl = strtolower($controller);
				$act = strtolower($action);
				
				if( isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION']['*']) ){
				  //R00 全部通過  	  
				  
				}else if( isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][$ctl])){
				  
				  $controller_permission = $_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][$ctl];
				
				  if( (isset($controller_permission['*']) && intval($controller_permission['*'])) || 
				      (isset($controller_permission[$act]) && intval($controller_permission[$act]) ) ){
					// 符合權限 // 通過	  
				  }else{
					$controller     = 'Main';
					$action         = 'denial';
					//throw new Exception('Main/denial');  
				    //其他全部不通過
				  }
				
				}else{
				  $controller     = 'Main';
				  $action         = 'denial';
				}
				
				break;
		  
			  default: // other...
				throw new Exception('re set to default page');
				break;
			}
		
		}catch (Exception $e){
          $request->setDefault();
		  $controller     = $request->getController();
		  $action         = $request->getAction();
	      header("location:index.php?act=".$controller.'/'.$action );
		  exit(1);
		}
        
		
	    $this->nowAction	 = $action;
	 	$this->nowController = $controller.'_Controller';  
	    
		// check class exist
		if(!class_exists($this->nowController, true)){   // false : 確認前先不執行 autoload
		  throw new Exception('404 Controller "'.$request->getController().'" not found');
		}
		
		// check class::method exist
		$this->nowAction	    = method_exists($this->nowController,$this->nowAction) ? $this->nowAction : 'index';
		
		// map args to object method 
		$c = new ReflectionClass($this->nowController);
        $m = $this->nowAction;
        $f = $c->getMethod($m);
        $p = $f->getParameters();            
        $params_new = array();
        $params_old = $this->nowArgs;
        
		// re-map the parameters
        for($i = 0; $i<count($p);$i++){
          $key = $p[$i]->getName();
          if(array_key_exists($key,$params_old)){
            $params_new[$i] = $params_old[$key];
            unset($params_old[$key]);
          }
        }
        
		// after reorder, merge the leftovers
        $params_new = array_merge($params_new, $params_old);// 將多出的參數移到最後面
        
	  }catch (Exception $e){
        $params_new = array($e->getMessage());
	    $this->nowController = 'Error_Controller';
	    $this->nowAction = 'index';
	  }
	  
	  
	  $this->objController 	= new $this->nowController;
	  // call the action method
	  call_user_func_array(array($this->objController , $this->nowAction), $params_new);  
	  
	  return 1;
	  exit(1);
	}
	
	
	
  }

?>