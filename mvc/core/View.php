<?php
  
  /*******************************************
      MVC main View class
      
	   
  ********************************************/
  
  //抽象化 顯示 類別
  abstract class View{
	//定義樣板參考變數
    protected $vars = array();
    
	
    // 設定樣版變數
	public function setVar($tpl_var,$value = NULL){
	  if(is_array($tpl_var)){    
		
		foreach($tpl_var as $key=>$val){
		  
		  if($key != '' ){
		    $this->vars[$key] = $val;
		  }
		}
	  
	  }else{
	    if($tpl_var != ''){
		  $this->vars[$tpl_var] = $value;
		}
	  }
	}
	
	 // 設定樣版變數
	public function addVar($tpl_var,$key='',$value = NULL){
	  
	  if(isset( $this->vars[$tpl_var] )){
	    $this->vars[$tpl_var][$key] = $value;  
	  }else{
	    $this->vars[$tpl_var] = array($key=>$value);
	  }
	  
	}
    
	// 自動取得對應的樣版變數
	public function __get($name){
		return isset($this->vars[$name]) ? $this->vars[$name] : NULL;
	}
	
	//抽象Method：擷取結果
    public abstract function fetch();
    public abstract function render();
  
  }
  

?>