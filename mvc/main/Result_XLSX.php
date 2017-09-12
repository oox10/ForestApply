<?php
  
  class Result_XLSX extends View{
    
	public function fetch(){
	  return false;
	}  
    
	public function render(){
	  ob_end_clean();
	    
	  $args = func_get_args();
	  
	  $xlsx_temp = $args[0];  // excel name
	  $data_sheet = isset( $this->vars['server']['data']['excel'] ) ?  $this->vars['server']['data']['excel'] : array();
	  $xlsx_name = isset( $this->vars['server']['data']['fname'] ) ?  $this->vars['server']['data']['fname'] : _SYSTEM_NAME_SHORT.'_export_'.date('YmdHis');
	  $sheet_name = isset( $this->vars['server']['data']['title'] ) ?  $this->vars['server']['data']['title'] : '';
	    
	  
	  //php excel initial
	  $objReader = PHPExcel_IOFactory::createReader('Excel2007');
	  $objPHPExcel = $objReader->load(_SYSTEM_XLSX_PATH.$xlsx_temp);
	    
		
	  /* 2003 xls
	  $objPHPExcel = new PHPExcel();
	  $myWorkSheet = new PHPExcel_Worksheet($objPHPExcel, $data_name);
	  $objPHPExcel->addSheet($myWorkSheet, 0);
	  $objPHPExcel->setActiveSheetIndex(0);
	  */

	  foreach($data_sheet as $sheet=>$data_list ){	
		
		$objPHPExcel->setActiveSheetIndex($sheet);
		if($sheet_name) $objPHPExcel->getActiveSheet()->setTitle($sheet_name);
		
		$col = 0 ;
		$row = 4 ;
 		
		foreach( $data_list as $data){
		  $col = 0;
		  foreach($data as $f=>$v){
			if(!is_array($v)){
			  $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->setValueExplicit($v, PHPExcel_Cell_DataType::TYPE_STRING);  	
			}
			$col++;
		  }
		  $row++;
		}
	    //$objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->setValueExplicit($data[0], PHPExcel_Cell_DataType::TYPE_STRING); //set value to string 
		//$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
		//$objPHPExcel->getActiveSheet()->getStyle("B4:AS$x")->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
	  }	
  	
      $objPHPExcel->setActiveSheetIndex(0);
	
	  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	  header('Content-Description: File Transfer');
	  header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	  header('Content-Disposition: attachment; filename="'.$xlsx_name.'.xlsx"');	
	  header('Expires: 0');
	  header('Cache-Control: max-age=0');
	  header('Pragma: public');
	  $objWriter->save('php://output'); 
	  $objPHPExcel->disconnectWorksheets();
	  unset($objPHPExcel);

	}
  }



?>