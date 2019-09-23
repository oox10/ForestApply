<?php
  
  class Result_JSONV extends View{
    
	public function fetch(){
	  $args = func_get_args();
	  $JsonCode = json_encode($this->vars[$args[0]]);
      return $this->vars[$args[0]];
	}  
    
	public function render(){
	  $args = func_get_args();
	  //exit($args[0]);
	  echo "<pre>";
	  print_r($this->fetch($args[0]));
	}
  }



?>