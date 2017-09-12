<?php 
  
  class Request{
	
    protected $_controller;
	protected $_action;
	protected $_args;
	
	public function __construct(){
      $parts = array_filter(explode('/',$_SERVER['REQUEST_URI']));
	  $this->_controller = ($c = array_shift($parts)) ? $c : 'index';
	  $this->_action     = ($a = array_shift($parts)) ? $a : 'index';
	  $this->_args	     = isset($parts[0]) ? $parts : array();
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
	
  }
  

?>