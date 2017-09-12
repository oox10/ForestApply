<?php
  
  class Result_FILEVIEW extends View{
    
	public function fetch(){
	  $args = func_get_args();
	  readfile($args[0]);
	}  
    
	public function render(){
	  ob_end_clean();
	  $file_meta = $this->vars['server']['data']['file'];
      $outputfilename = mb_convert_encoding($file_meta['name'],'big5','utf8');
	  
	  header('Content-Description: File Transfer');
	  header('Content-Type: application/pdf');
	  header('Content-Transfer-Encoding: binary');
	  header('Content-Disposition: inline; filename="'.$outputfilename.'"');	 // Content-Disposition: attachment; 以下載的形式儲存
	  header('Expires: 0');
	  header('Cache-Control: max-age=0');
      header('Cache-Control: private', false);
	  header('Cache-Control: must-revalidate');
      header('Pragma: public');
      header('Content-Length: ' . $file_meta['size']);
	  $this->fetch($file_meta['location']);
      exit(1);  
	}
  }



?>