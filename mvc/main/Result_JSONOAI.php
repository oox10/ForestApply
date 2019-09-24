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
	  http_response_code((!$this->vars[$args[0]]['action'] ? 200 :405));
	  header("Content-type: application/json");
	  $JsonCode = isset($this->vars[$args[0]]['data']['openapi']) ? $this->vars[$args[0]]['data'] : $this->vars[$args[0]];
	  echo json_encode($JsonCode);
	}
  }



?>