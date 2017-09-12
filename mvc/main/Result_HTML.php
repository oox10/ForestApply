<?php

  class Result_HTML extends View{
    
	//取得檔案名稱參數   $template_filename = $args[0]
	//讀取對應 template 檔案 並輸出
	public function fetch(){
	   $args = func_get_args();
       $template_filename = $args[0];
       
	   //開啟緩衝  放入 $html 中 並傳回
       $html = '';
	   ob_start();
	   
       if(is_file(_SYSTEM_ROOT_PATH.'mvc\\templates\\'.$template_filename)){
	     include _SYSTEM_ROOT_PATH.'mvc\\templates\\'.$template_filename;
	   }else{
	     $this->vars['message'] = '找不到網頁，或瀏覽器版本不符'; 
	     include _SYSTEM_ROOT_PATH.'mvc\\templates\\page_wrong.html5tpl.php';
	   }
	   
	   $html = ob_get_contents();
       ob_end_clean();
       return $html;
	}  
    
	public function render(){
	   // 因為 View 類別的 render 函式沒有參數所以 render 要自行取得 func_get_args : array()
       $args = func_get_args();
       $template_filename = $args[0].'.html5tpl.php';
       header('Content-Type: text/html; charset=utf-8');
	   header("X-Frame-Options: SAMEORIGIN");
       echo $this->fetch($template_filename);
	}
  }
?>