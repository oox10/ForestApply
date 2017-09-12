<?php 
  
  /*
  *   [RCDH10 System Module] - Request Object 
  *   Url Request Passer For Normal Mode
  *  
  *   2016 ed.  
  */
  
  
  class Request_url{
	
    protected $_controller;
	protected $_action;
	protected $_args;
	
	protected $_default;
	
	public function __construct($default='Account/index'){
	  $refer = explode('/', isset($_REQUEST['act'] )? $_REQUEST['act'] :$default);
	  $this->_default    = $default;
	  $this->_controller = ($c = array_shift($refer)) ? $c : 'Error';;
	  $this->_action     = ($a = array_shift($refer)) ? $a : 'index';;
	  $this->_args	     = isset($refer[0]) ? $refer : array();
	}
	
	public function getController(){   
	  return  $this->_controller;
	}
	
	public function getAction(){   
	  return  $this->_action;  
	}
	
	public function getArgs(){   
	  return  $this->_args;
	}
	
	public function setDefault(){  
	  list($this->_controller,$this->_action) = explode('/', $this->_default);
	}
	
  }
  

?>