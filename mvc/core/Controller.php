<?php 

  /*******************************************
      MVC main controler class
      
	   
  ********************************************/
  
  abstract class Controller{
    
	protected $action   = '';      //動作名稱
    protected $addition = NULL;    //附加參數  各動作可自行回傳相關值
	protected $router = NULL;
    
	//抽象：預設動作，繼承此物件之類別都需要定義 index() method
	public abstract function index();
	
	
	public function setAction($action,$addition=''){ 
	   $this->action   = $action;
	   $this->addition = $addition;
	}
	
	// 執行選擇的動作
    public final function run(){
	   $this->{$this->action}($this->addition);
	}
	
	
	// 重新導向
    public function redirectTo($url){
        header('Location: ' . $url);
    }
	
	
	
  }
  
?>