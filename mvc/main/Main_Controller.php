<?php
  
  
  /********************************************* 
  ***   ForestApply Admin Post Control Set   ***
  *********************************************/
	
  class Main_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Admin_Model;
	}
	
	// PAGE: 管理訊息介面 O
	public function index(){
	  
	  self::redirectTo('index.php?act=Booking');
	  exit(1);
	  
	  echo "<pre>";
	  echo 'session admin USER:<br/>';
	  var_dump(unserialize($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']) );
	 
      echo '<br/><br/><br/>session admin PERMISSION:<br/>';
	  var_dump($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'] );
	  
	}
	
  }
  
  
  
  
  
?>


