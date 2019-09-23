<?php
  
  class Result_JSONOAI extends View{
    
	public function fetch(){
	  $args = func_get_args();
	  $JsonCode = json_encode($this->vars[$args[0]]['data']);
      return $JsonCode;
	}  
    
	public function render(){
	  $args = func_get_args();
	  //exit($args[0]);
	  header("Content-type: text/json");
      echo $this->fetch($args[0]);
	}
  }



?>