<?php
  class Result_PDF extends View{
    
	public function fetch(){
	  //ini_set('memory_limit', '500M');
	  
	  $args = func_get_args();
      $template_filename = $args[0];
	  $pdf_page_template = '';
	  if(!is_file(_SYSTEM_ROOT_PATH.'mvc/templates/'.$template_filename)){
	    return false;
	  }	  
	  
	  $license_data = $this->vars['server']['data']['license'];
	  $pdf_page_template = file_get_contents(_SYSTEM_ROOT_PATH.'mvc/templates/'.$template_filename);
	  $pdf_page_generate = $pdf_page_template;
	  
	  foreach($license_data as $field => $content){
		$pdf_page_generate = preg_replace('/#\{'.$field.'\}/',$content,$pdf_page_generate);    
	  }
	  
	  $new_file_folder = _SYSTEM_FILE_PATH.'license/review/';
	  $new_file_name   = time().'-'.$license_data['BOOKED_CODE'];
	  $new_file_export = $new_file_name.'.pdf';
	  $new_file_temp   = $new_file_folder.$new_file_name.'.html';
	  
	  file_put_contents( $new_file_temp , $pdf_page_generate);
	  exec(_SYSTEM_PDF_CONVERT.$new_file_temp.' '.$new_file_folder.$new_file_export,$output);  // 做完才結束
	  unlink($new_file_temp);
	  return readfile($new_file_folder.$new_file_export);
	}
	
	public function render(){
	  $args = func_get_args();
	  $template_filename = $args[0].'.html5tpl.php';
	  ob_clean();
	  header('Content-Type: application/pdf');
	  header("Content-Disposition:inline;filename=".mb_convert_encoding(_SYSTEM_HTML_TITLE.'_申請單'.$this->vars['server']['data']['license']['BOOKED_CODE'],'big5','utf8').'_'.date('Ymd').".pdf");
	  print $this->fetch($template_filename);
	}
  }

?>