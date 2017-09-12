<?php

  class Record_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Record Function Set ]*/ 

	
	
	
	//-- Admin Record Page Initial  - the same width get apply record 
	// [input] : $DateFrom :   date
    // [input] : $DateTo :   date

	public function ADRecord_Get_Apply_Record($DateFrom = '', $DateTo='' ){
	  
	  $result_key = parent::Initial_Result('statistics');
	  $result  = &$this->ModelResult[$result_key];
	 
	  try{  
		  
		// 設定日期範圍
		$record_range['date_start'] = strtotime($DateFrom) ? date('Y-m-01',strtotime($DateFrom)) :  date("Y-01-01");
		$record_range['date_end']   = strtotime($DateTo)   ? date('Y-m-t',strtotime($DateTo))    :  date("Y-12-31");
			
		// 取得區域範圍
		$area_list = array();
		$DB_AREA = $this->DBLink->prepare(SQL_AdRecord::SELECT_AREA_LIST()); 
		if(! $DB_AREA->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		$records = array(); 
		$area_sel_manual = array();
		
		while($dbraw = $DB_AREA->fetch(PDO::FETCH_ASSOC)){
          
		  // 建立清單
		  $area_sel_manual[$dbraw['area_code']] = $dbraw['area_name'];
		  
		  // 依據每個地區取得範圍內的申請資料
		  $DB_BOOK = $this->DBLink->prepare(SQL_AdRecord::GET_AREA_BOOK_RECORD()); 
		  $DB_BOOK->bindValue(':ano' , $dbraw['ano'] );
		  $DB_BOOK->bindValue(':date_start' , $record_range['date_start'] );
		  $DB_BOOK->bindValue(':date_end'   , $record_range['date_end']   );
		  if(! $DB_BOOK->execute()){
		    throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		  
		  // 建立統計框架
		  $area_apply_record = array();
		  $dindex = date('Y-m',strtotime($record_range['date_start']));
		  $atotal = array('apply_case'=>0,'apply_member'=>0,'accept_case'=>0,'accept_member'=>0,'undone_case'=>0,'apply_dates'=>array());
		  
		  do{
			$area_apply_record[$dindex] = [
			  'year'  => substr($dindex,0,4),
			  'month' => substr($dindex,-2,2),
              'apply_case'   =>0,
			  'apply_member' =>0,
			  'accept_case'  =>0,			  
              'accept_member'=>0,
			  'undone_case'  =>0,
			  'apply_dates'  =>array()
			];
			$dindex = date('Y-m',strtotime('+1 month',strtotime($dindex.'-01')));
		  }while(strtotime($dindex.'-01') <=  strtotime($record_range['date_end']));
          
		  while($dbtmp = $DB_BOOK->fetch(PDO::FETCH_ASSOC)){
			// DB:abno,am_id,apply_date,date_enter,member_count,_final,_stage
			
			// 每月統計
			$apply_index = date('Y-m',strtotime($dbtmp['date_enter']));
			$area_apply_record[$apply_index]['apply_case']++;
			$area_apply_record[$apply_index]['apply_member'] +=$dbtmp['member_count'] ;
			
			// 綜合統計
			$atotal['apply_case']++;
            $atotal['apply_member'] += $dbtmp['member_count'];			
			
			if($dbtmp['_stage']==5){
			  if($dbtmp['_final']=='核准進入'){
				
				$area_apply_record[$apply_index]['accept_case']++;
			    $area_apply_record[$apply_index]['accept_member'] +=$dbtmp['member_count'] ;  
			    $area_apply_record[$apply_index]['apply_dates'][$dbtmp['date_enter']] = 1;
			    
				$atotal['accept_case']++;
				$atotal['accept_member'] += $dbtmp['member_count'] ;  
			    $atotal['apply_dates'][$dbtmp['date_enter']] = 1;
				
			  }		  
			}else{
			  $area_apply_record[$apply_index]['undone_case']++;	
			  $atotal['undone_case']++;		
			}
		  }
		  
		  
		  if( !isset($records[$dbraw['area_type']]) ) $records[$dbraw['area_type']] = array();
		  
		  $records[$dbraw['area_type']][$dbraw['ano']] = [
		    'area'  => $dbraw,
			'total' => $atotal,
			'table' => $area_apply_record
		  ];
		  
		}
		
		$result['data']['filter'] = $record_range;
		$result['data']['record'] = $records;
		$result['data']['select'] = $area_sel_manual;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	  
	}
	
	
	
	//-- Admin Apply Get Apply Data 
	// [input] : uano  :  \d+;
	// [input] : list  :  string: \d+,\d+,...;
	public function ADRecord_Built_Record_File($ExcelTemplate=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		if( !isset($this->ModelResult['statistics']) || !$this->ModelResult['statistics']['action']){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');  	
		}
		
		if(!$ExcelTemplate || !file_exists(_SYSTEM_FILE_PATH.$ExcelTemplate)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');  	
		}
		
		$template = _SYSTEM_FILE_PATH.$ExcelTemplate;
		$records  = $this->ModelResult['statistics']['data']['record'];
		$dayrange = $this->ModelResult['statistics']['data']['filter'];
		
		$objReader = PHPExcel_IOFactory::createReaderForFile($template);
		$objPHPExcel = $objReader->load($template);
		
		// 輸入總表單
		$objPHPExcel->setActiveSheetIndex(0); 
		$objPHPExcel->getActiveSheet()->setTitle('統計總表');
		
		// 表頭
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 1)->setValueExplicit($this->USER->UserID, PHPExcel_Cell_DataType::TYPE_STRING);  	
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 2)->setValueExplicit(date('Y-m-d H:i:s'), PHPExcel_Cell_DataType::TYPE_STRING);  	
		$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 3)->setValueExplicit($dayrange['date_start'].' ～ '.$dayrange['date_end'], PHPExcel_Cell_DataType::TYPE_STRING);  	
		
		// 統計表
		$row = 5;
		foreach($records as $area_type => $area_list){
		  
		  $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0, $row)->setValueExplicit($area_type, PHPExcel_Cell_DataType::TYPE_STRING);
		  $area_type_start = $row;
		
		  foreach($area_list as $area_id => $area_data){
			$accept_rate = intval($area_data['total']['apply_case']) ? round(intval($area_data['total']['accept_case'])/intval($area_data['total']['apply_case']),4)*100  : 0 ;
			$inter_rate  = intval($area_data['total']['apply_case']) ? round(intval($area_data['total']['accept_member'])/intval($area_data['total']['apply_member']),4)*100  : 0 ;
			$everage_person_day = count($area_data['total']['apply_dates']) ?  round(intval($area_data['total']['accept_member'])/count($area_data['total']['apply_dates'])) : 0 ;
			
			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $row)->setValueExplicit($area_data['area']['area_name'], PHPExcel_Cell_DataType::TYPE_STRING);  // 區域名稱
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $row)->setValueExplicit($area_data['total']['apply_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 總件數
			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $row)->setValueExplicit($area_data['total']['accept_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 核准件數
			$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $row)->setValueExplicit($area_data['total']['undone_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 未完成件數
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5, $row)->setValueExplicit($area_data['total']['accept_member'], PHPExcel_Cell_DataType::TYPE_STRING);  // 核准人數
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6, $row)->setValueExplicit($accept_rate.'%' , PHPExcel_Cell_DataType::TYPE_STRING);  // 核准比例
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7, $row)->setValueExplicit($inter_rate.'%', PHPExcel_Cell_DataType::TYPE_STRING);  // 進入人數比例
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8, $row)->setValueExplicit($area_data['total']['accept_member'], PHPExcel_Cell_DataType::TYPE_STRING);  // 總進入人次
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9, $row)->setValueExplicit(count($area_data['total']['apply_dates']) , PHPExcel_Cell_DataType::TYPE_STRING);  // 總和可天數
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(10, $row)->setValueExplicit($everage_person_day.' 人次/每日', PHPExcel_Cell_DataType::TYPE_STRING); // 平均進入人次  
		    $row++;
		  }
		  $objPHPExcel->getActiveSheet()->mergeCells("A".$area_type_start.":A".($row-1));
		}
		
		// 輸出區間月份統計
		// 輸入總表單
		$area_index = 1;
		foreach($records as $area_type => $area_list){
		  foreach($area_list as $area_id => $area_data){
			
			if($area_index > 1){
			  //  Get the current sheet with all its newly-set style properties
			  $objWorkSheetBase = $objPHPExcel->getSheet(1);

			  //  Create a clone of the current sheet, with all its style properties
              $objWorkSheet1 = clone $objWorkSheetBase;
              //  Set the newly-cloned sheet title
              $objWorkSheet1->setTitle($area_data['area']['area_name']);
              //  Attach the newly-cloned sheet to the $objPHPExcel workbook
              $objPHPExcel->addSheet($objWorkSheet1); 
			 
			}
			
			$objPHPExcel->setActiveSheetIndex($area_index); 
		    $objPHPExcel->getActiveSheet()->setTitle($area_data['area']['area_name']); 
            $objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, 1)->setValueExplicit($area_data['area']['area_name'].' - 各月份統計', PHPExcel_Cell_DataType::TYPE_STRING);  	
			
			$row = 3;
            $count = 1;
			foreach($area_data['table'] as $date_index => $data){
				$accept_rate = intval($data['apply_case']) ? round(intval($data['accept_case'])/intval($data['apply_case']),4)*100  : 0 ;
				$inter_rate  = intval($data['apply_case']) ? round(intval($data['accept_member'])/intval($data['apply_member']),4)*100  : 0 ;
				$everage_person_day = count($data['apply_dates']) ?  round(intval($data['accept_member'])/count($data['apply_dates'])) : 0 ;
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(0, $row)->setValueExplicit($count++, PHPExcel_Cell_DataType::TYPE_STRING);  // 編號
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(1, $row)->setValueExplicit($date_index, PHPExcel_Cell_DataType::TYPE_STRING);  // 日期
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(2, $row)->setValueExplicit($data['apply_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 總件數
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(3, $row)->setValueExplicit($data['accept_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 核准件數
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(4, $row)->setValueExplicit($data['undone_case'], PHPExcel_Cell_DataType::TYPE_STRING);  // 未完成件數
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(5, $row)->setValueExplicit($data['accept_member'], PHPExcel_Cell_DataType::TYPE_STRING);  // 核准人數
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(6, $row)->setValueExplicit($accept_rate.'%' , PHPExcel_Cell_DataType::TYPE_STRING);  // 核准比例
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(7, $row)->setValueExplicit($inter_rate.'%', PHPExcel_Cell_DataType::TYPE_STRING);  // 進入人數比例
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(8, $row)->setValueExplicit($data['accept_member'], PHPExcel_Cell_DataType::TYPE_STRING);  // 總進入人次
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(9, $row)->setValueExplicit(count($data['apply_dates']) , PHPExcel_Cell_DataType::TYPE_STRING);  // 總和可天數
				$objPHPExcel->getActiveSheet()->getCellByColumnAndRow(10, $row)->setValueExplicit($everage_person_day.' 人次/每日', PHPExcel_Cell_DataType::TYPE_STRING); // 平均進入人次  
				$row++;
			}
		    $area_index++; 
		  }
		}
		$objPHPExcel->setActiveSheetIndex(0); 
		
		$export_file_name = _SYSTEM_USER_PATH.$this->USER->UserID.'/'.'statistics_export_'.time().'.xlsx';
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->save($export_file_name); 
		$objPHPExcel->disconnectWorksheets();
		
		// final
		$result['action'] = true;
		$result['data']['name']     = '保護留區申請進入統計['.$dayrange['date_start'].'_'.$dayrange['date_end'].']-'.date('Ymd').'Export.xlsx';
		$result['data']['size']     = filesize($export_file_name);
    	$result['data']['location'] = $export_file_name;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
  }
  
  
?>