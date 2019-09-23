<?php
    
	/*
	數位典藏資料
	2017
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/server_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
    require_once(dirname(dirname(__FILE__)).'/mvc/lib/PHPExcel-1.8/Classes/PHPExcel.php');
	
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	$file_path = dirname(__FILE__).'/rawdata/';
	file_put_contents('dump.log',"");
	try{ 
      
	  $files = array_slice(scandir($file_path),2);
	  
	  if(!count($files)){
		throw new Exception('No Source Files FROM :'.$file_path."\n");    
	  }
	  
	  foreach($files as $source){
		  
		  if(!is_file($file_path.$source)){
			throw new Exception($file_path.': File Not Exist.');  
		  }
		  
		  
		  $db->DBLink->query("TRUNCATE `evaluation_mettpressure`;");
		  $db->DBLink->query("TRUNCATE `evaluation_mettdata`;");
		  $db->DBLink->query("TRUNCATE `evaluation_mettevaluate`;");
		  
		  $excelReader = PHPExcel_IOFactory::createReaderForFile($file_path.$source);
		  $excelReader->setReadDataOnly(true);
		  $objPHPExcel = $excelReader->load($file_path.$source);
			 
		  $excel_sheet_num = $objPHPExcel->getSheetCount();
		  $excel_sheet_names = $objPHPExcel->getSheetNames();
		  
		  $counter = 0;
		  
		  for($sheet=1;$sheet<$excel_sheet_num;$sheet++){
			  
			  echo $sheet.'-';
			  $objSheet=$objPHPExcel->getSheet($sheet);
			  
			  //取欄位長度
			  $col = 0; $col_max = 1; $tablename='';
			  while(trim($objSheet->getCellByColumnAndRow($col,1)->getValue())){
				$col++;
			  }
			  switch($sheet){
				case 1: $col_max=37;  $tablename='evaluation_mettdata';  break;  
			    case 2: $col_max=271;  $tablename='evaluation_mettpressure';  break;  
			    case 3: $col_max=93; $tablename='evaluation_mettevaluate'; break;   
			  }
			  
			  
			  //讀取資料
			  $col=0; $row=4;
			  
			  while(trim($objSheet->getCellByColumnAndRow($col,$row)->getValue())){
				
                $record_id = trim($objSheet->getCellByColumnAndRow($col,$row)->getValue());
			    
				echo $record_id.':';
				$record = ['NULL'];
				
				for($c=0;$c<$col_max;$c++){
                  
				  if($sheet==1&&$c==1) {
					$v = '"'.date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP(trim($objSheet->getCellByColumnAndRow($c,$row)->getValue()))).'"'; 
				  }else{
					$v = trim($objSheet->getCellByColumnAndRow($c,$row)->getValue());  
				  }
				  
				  $record[] = $v && $v!='NA' ? '"'.preg_replace('/"/','',$v).'"' : 'NULL';
			    }  
				
                if(count(array_filter($record))){
					
					$record[] = '"SYSTEM"';
					$record[] = 'NULL';
					$record[] = 1;
					$record[] = 1;
					
					file_put_contents('dump.log',print_r("INSERT INTO ".$tablename." VALUES (".join(',',$record).");",true).PHP_EOL,FILE_APPEND);
					
					if($db->DBLink->query("INSERT INTO ".$tablename." VALUES (".join(',', $record).");")){
					  echo "done.";	
					}else{
				      echo "fail.";
					}
					
				}
				echo PHP_EOL;
				
				$row++;
				$counter++;
			  }
			  
		  }
	  }
	  
	  $objPHPExcel->disconnectWorksheets();  
	  unset($objPHPExcel);
    
	} catch (Exception $e) {
      echo $e->getMessage();
    }	
	
?>