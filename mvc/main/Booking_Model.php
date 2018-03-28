<?php

  class Booking_Model extends Admin_Model{
    
	
	/*[ Book Function Set ]*/ 
    
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	  //parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	
	protected $ResultCount;   // 查詢結果數量
	protected $PageNow;       // 當前頁數 
	protected $LengthEachPage;// 每頁筆數
	
	
	//-- Get Book Checker Active Area List
	// [input] : NULL 
	public function Admin_Book_Get_Active_Area_List(){
	  
	  $result_key = parent::Initial_Result('areas');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		
		// 取得管制區域列表
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SELECT_USER_AREA()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		$area_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		$result['data']   = $area_list ;
		$result['action'] = true;		
	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	
	//-- Admin Meta Page Data OList 
	// [input] : $PagerMaxNum => int // 頁面按鈕最大數量
	public function Admin_Get_Page_List( $PagerMaxNum=1 ){
		
	  $result_key = parent::Initial_Result('page');
	  $result  = &$this->ModelResult[$result_key];
      
	  try{
        
		$page_show_max = intval($PagerMaxNum) > 0 ? intval($PagerMaxNum) : 1;
		
		
	    $pages = array();
		
		$pages['all'] = array(1=>'');
		
		
		// 必要參數，從ADMeta_Get_Meta_List而來
		$this->ResultCount;   // 查詢結果數量
	    $this->PageNow;   
	    $this->LengthEachPage;
		
		$total_page = ( $this->ResultCount / $this->LengthEachPage ) + ($this->ResultCount%$this->LengthEachPage ? 1 :0 );
		
		// 建構分頁籤
		for($i=1;$i<=$total_page;$i++){
		  $pages['all'][$i] = (($i-1)*$this->LengthEachPage+1).'-'.($i*$this->LengthEachPage);
		}
		
		$pages['top']   = reset($pages['all']);
		$pages['end']   = end($pages['all']);
		$pages['prev']  = ($this->PageNow-1 > 0 ) ? $pages['all'][$this->PageNow-1] : $pages['all'][$this->PageNow];
		$pages['next']  = ($this->PageNow+1 < $total_page ) ? $pages['all'][$this->PageNow+1] : $pages['all'][$this->PageNow];
		$pages['now']   = $this->PageNow;  
		
		$check = ($page_show_max-1)/2;
	    if($total_page < $page_show_max){
		  $pages['list'] = $pages['all'];  	
		}else {  
          if( ($this->PageNow - $check) <= 1 ){    // 抓最前面 X 個
            $start = 0;
		  }else if( ($this->PageNow + $check) > $total_page ){  // 抓最後面 X 個
            $start = $total_page-(2*$check)-1;    
		  }else{
            $start = $this->PageNow - $check -1;
		  }
	      $pages['list'] = array_slice($pages['all'],$start,$page_show_max,TRUE);
		}
		
		// 建構選項
		$effect_page = count($pages['all']);
		
		if(count($pages['all']) > 500 ){
			for($x=1;$x<=$effect_page;$x++){
			  if($x==1 || $x==$effect_page || abs($x-$this->PageNow)<20){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)<100 && $x%10===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)<1000 && $x%200===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)>=1000 &&  abs($x-$this->PageNow)<10000 && $x%1000===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }else if(abs($x-$this->PageNow)>=10000 && $x%10000===0){
				$pages['jump'][$x] = $pages['all'][$x];
			  }
			}
		  
		}else{
		  $pages['jump'] = $pages['all'];	
		}
		unset($pages['all']);
		
		$result['data']   = $pages;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	
	}
	
	
	
	
	//-- Admin Book Get Book List 
	// [input] : NULL;
	public function Admin_Book_Get_List($BookType,$Pageing='1-20',$SearchString){
	  
	  $result_key = parent::Initial_Result('records');
	  $result     = &$this->ModelResult[$result_key];
	  
	  $area_types   = array();
	  $apply_search = json_decode(base64_decode(str_replace('*','/',rawurldecode($SearchString))),true); 
	  
	  try{
	    
		
		// 計算頁數
		$Pageing = trim($Pageing);
		if(!preg_match('/^\d+\-\d+$/', $Pageing )) $Pageing = '1-20';	
		list($p_start,$p_end) = explode('-',$Pageing);
		$this->LengthEachPage = $p_end - $p_start + 1;
		$this->PageNow     	  = intval($p_end/($this->LengthEachPage));   
	    $this->ResultCount    = 0;
		
		
		// 取得資料
		$area_list = array(); // 存放區域資料
		$area_sets = array(); // 存放區域索引
		
		$user_groups = array(); // 存放使用者群組
		if(isset($this->USER->PermissionQue)){
		  $user_groups = array_keys($this->USER->PermissionQue);	
		}
		
		// 取得管制區域列表
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SELECT_USER_AREA()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $area_list[$tmp['ano']] = $tmp;
		  $area_sets[$tmp['ano']] = $tmp['area_code']; 
		}
		
		// 取得申請列表資料總表
		$search_condition = array();
		
		
		// 搜尋條件
		$condition = array();
		$orderby   = '_time_update DESC';
		
		
		// 類型篩選
		if(count($area_types)){
		  $condition[] = "area_type IN('".join("','",$area_types)."')";	
		}
		
		// 條件篩選 
		if(is_array($apply_search)){
		  foreach( $apply_search as $filter_target => $filter_set ){   // 取名為filter 是因為篩選欄位設定與meta欄位不完全一樣，需要重新轉換
		    switch($filter_target){
			  
			  case 'area_type'   :   $condition[] = " area_type='".$filter_set."' "; break;
			  case 'apply_area'  :   $condition[] = " am_id=".$filter_set." "; break;
			  case 'apply_search':   $condition[] = " (apply_code='".$filter_set."' OR apply_date='".$filter_set."' OR applicant_name LIKE'%".$filter_set."%' OR applicant_mail LIKE'%".$filter_set."%' OR applicant_id LIKE'%".$filter_set."%' OR apply_reason LIKE'%".$filter_set."%' OR _status LIKE'%".$filter_set."%')"; break;
			  case 'apply_status':   $condition[] = " _status = '".$filter_set."' "; break;
			  case 'apply_review':   $condition[] = " _review = 1 " ; break;
			  case 'apply_checked':  $condition[] = " _status IN('正取送審','備取送審','急件送審','補件送審')" ; break;
			  case 'apply_unfinish':  $condition[] = " _stage < 5" ; break;
			  
			  case 'apply_date': 
              case 'date_enter':
 			  case 'date_exit':
			    $date_filter_conf = array();
				if( isset($filter_set['date_start']) && strtotime($filter_set['date_start'])){
				  $filter_date_string = date('Y-m-d',strtotime($filter_set['date_start']));	
				  $date_filter_conf[] = $filter_target." >='".$filter_date_string."'";
				}
				if( isset($filter_set['date_end']) && strtotime($filter_set['date_end'])){
				  $filter_date_string = date('Y-m-d',strtotime($filter_set['date_end']));	
				  $date_filter_conf[] = $filter_target." <='".$filter_date_string."'";
				}
				$condition[] = "(".join(' AND ',$date_filter_conf).")" ; 
			    break;
			}
		  }
		}
		
		// 取得篩選後申請資料
		$records = array(); // 存放列表資料
		$sqlsearch = count($condition) ? join(' AND ',$condition) : ('_stage < 5');
		 
		
		// 計算總數
		$DB_BOOK = $this->DBLink->prepare(SQL_AdBook::COUNT_AREA_BOOKING(array_keys($area_list),$sqlsearch));
		if(!$DB_BOOK->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		$this->ResultCount = $DB_BOOK->fetchcolumn();
		
		
		// 執行查詢
		$sqllimit = [($this->PageNow-1)*$this->LengthEachPage,$this->LengthEachPage];
		$DB_BOOK = $this->DBLink->prepare(SQL_AdBook::SELECT_AREA_BOOKING(array_keys($area_list),$sqlsearch,$orderby,$sqllimit));
		if(!$DB_BOOK->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		
		while($tmp = $DB_BOOK->fetch(PDO::FETCH_ASSOC)){
			
		  if(isset($area_type)){
			if($area_type != $area_list[$tmp['am_id']]['area_type']){
			  continue;	
			}  
		  }	
		  
		  $record = array();
		  $record['abno'] = $tmp['abno'];
		  $record['r_apply_date'] = $tmp['apply_date'];
		  $record['r_apply_code'] = $tmp['apply_code'];
		  $record['r_apply_area'] = $area_list[$tmp['am_id']]['area_name'];
		  $record['r_apply_user'] = $tmp['applicant_name'];
		  $record['r_countmbr']   = $tmp['member_count'];
		  $record['r_apply_period'] = $tmp['date_enter'].' ~ '.$tmp['date_exit'];
          $record['r_review'] = $tmp['_review'] ? 1 : 0;		   
		  
		  // 狀態設定
		  switch($tmp['_status']){
			case '_INITIAL'		: $status_info = '註冊'; break;
			case '_FORM'		: $status_info = '填寫資料'; break;
			case '_MEMBER'		: $status_info = '填寫名單'; break;
			case '_SUBMIT'		: $status_info = '遞交申請'; break;
            case '_ACCEPT'		: $status_info = '收件待審'; break;
            case '_APPROVE'		: $status_info = '申請核准'; break;
            case '_REJECT'		: $status_info = '申請駁退'; break;
            case '_CANCEL'		: $status_info = '申請註銷'; break;
            case '_REVIEW'		: $status_info = '正取送審'; break;
            case '_W_REVIEW'	: $status_info = '備取送審'; break;
            case '_ADDOC'		: $status_info = '正取補件'; break;
            case '_W_ADDOC'		: $status_info = '備取補件'; break;
            case '_A_REVIEW'	: $status_info = '正取補件送審'; break;
			case '_W_A_REVIEW'	: $status_info = '備取補件送審'; break;
			case '_UNLOTTED'	: $status_info = '抽籤未中'; break;
			case '_GIVEUP'		: $status_info = '申請取消'; break;
			case '_WAITTING'	: $status_info = '備取等待'; break;
			case '_A_REJECT'	: $status_info = '補件駁退'; break;
			case '_GETIN'		: $status_info = '備取成功'; break;
			case '_TIMEOUT'		: $status_info = '備取失敗'; break;
		    default: $status_info = $tmp['_status']; break;
		  }
		  $record['r_status'] = $status_info;	
		  $records[] = $record;
		 
		}
		
		$result['action'] = true;		
		$result['data']['list']   = $records;	
		$result['data']['filter'] = $apply_search;
		$result['data']['limit']  = $BookType;
		$result['data']['count']  = $this->ResultCount ;
		$result['data']['start']  = $p_start;
		$result['data']['range']  = '1-'.($p_end-$p_start+1);
		
        $result['session']['ADAREASMAP'] = $area_sets;	
        		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Admin Meta Export User Select
	// [input] : RecordsString  :  encoed array string;
	
	public function ADBook_Export_Selected( $RecordsString=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $excel_template = 'template_book_export.xlsx';
	  
	  try{  
	    
		$abno_array = json_decode(base64_decode(str_replace('*','/',rawurldecode($RecordsString))),true); 
		if(!count($abno_array)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// 取得管制區域列表
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SELECT_USER_AREA()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $area_list[$tmp['ano']] = $tmp;
		}
		
		// 取得管制區域申請清單
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SEARCH_BOOKING_RECORDS($abno_array )));
		 
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$export_record = [];
		$total_count = 0;
		$i=1;
		while($book = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $applyform = json_decode($book['apply_form'],true);  
		  $apply = [];
		  $apply['apply_code'] =  $book['apply_code'];
		  $apply['apply_date'] =  $book['apply_date'];
		  $apply['apply_area'] =  $area_list[$book['am_id']]['area_name'];
		  $apply['apply_reason'] =  $book['apply_reason'];
		  $apply['apply_user'] = $book['applicant_name']; 
		  $apply['apply_mail'] =  $book['applicant_mail'];
		  $apply['enter_date'] =  $book['date_enter'] .' ～ '.$book['date_exit'];
		  $apply['enter_block'] =  isset($applyform['area']['inter'])&&is_array($applyform['area']['inter']) ? join('、',$applyform['area']['inter']) : '';   
		  $apply['enter_gate'] = isset($applyform['area']['gate']['entr']) ? $applyform['area']['gate']['entr'] : ''; 
		  $apply['enter_time'] = $apply['enter_gate']&&isset($applyform['area']['gate']['entr_time']) ? $applyform['area']['gate']['entr_time'] : ''; 
		  $apply['exit_gate']  = isset($applyform['area']['gate']['exit']) ? $applyform['area']['gate']['exit'] : ''; ; 
		  $apply['exit_time']  = $apply['exit_gate']&&isset($applyform['area']['gate']['exit_time']) ? $applyform['area']['gate']['exit_time'] : ''; ; 
		  $apply['apply_check']= $book['check_note'];
		  $apply['apply_status']= $book['_status'];
		  $apply['member_count']= $book['member_count'];
		  if(isset($applyform['fields']) && is_array($applyform['fields'])){
			foreach($applyform['fields'] as $af){
			  $apply[$af['field']] = $af['value'];	
			}
		  }
		  $total_count+= intval($book['member_count']);
		  
		  $export_record[] = $apply; 
		}
		
		
		//php excel initial
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objReader->load(_SYSTEM_ROOT_PATH.'mvc/templates/'.$excel_template);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(0);
		
		// 填入日期
		$active_sheet->getCellByColumnAndRow(1,1)->setValueExplicit(date('Y-m-d'), PHPExcel_Cell_DataType::TYPE_STRING);  	
		
		// 填入總人次
		$active_sheet->getCellByColumnAndRow(1,2)->setValueExplicit($total_count, PHPExcel_Cell_DataType::TYPE_NUMERIC);  	
		
		// 填入資料
        $row = 4;
		foreach($export_record as  $record){
          $col = 0;
          foreach($record as $f=>$v){
			$active_sheet->getCellByColumnAndRow($col++, $row)->setValueExplicit($v, PHPExcel_Cell_DataType::TYPE_STRING);    
		  }  		  
		  $row++;
		}
		 		
		// 設定序號
		$objPHPExcel->setActiveSheetIndex(0);
		$excel_file_name =  _SYSTEM_NAME_SHORT.'_export_'.date('Ymd');
		$objPHPExcel->setActiveSheetIndex(0);
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	    $objWriter->save(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$excel_file_name.'.xlsx'); 
		unset($objPHPExcel);
		
		// final
		$result['data']['fname']   = $excel_file_name;
		$result['data']['count']   = count($export_record);
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	//-- Admin Book : Batch Export XLSX 
	// [input] : FileName  : logs_digital.note	
	public function ADBook_Access_Export_File( $FileName=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
	    
		if(!$FileName){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		} 
		
		$file_path = _SYSTEM_USER_PATH.$this->USER->UserID.'/'.$FileName.'.xlsx';
		if(!file_exists($file_path)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		
		}
		
		// final 
		$result['data']['name']  = $FileName.'.xlsx';
		$result['data']['size']  = filesize($file_path);
		$result['data']['location']  = $file_path;
		
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	//-- Admin Booking Get Book Data 
	// [input] : DataCode        :  \d+ / area_booking.apply_code;
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	
	public function ADBook_Get_Book_Data($DataCode='',$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('read');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得申請資料
		$record = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode );	
		if( !$DB_GET->execute() || !$record = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
	    
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($record['am_id'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		// 取得區域資料
		$area_meta = array();
		$DB_AREA= $this->DBLink->prepare( SQL_AdBook::GET_AREA_META() );
		$DB_AREA->bindParam(':ano'   , $record['am_id'] );	
		if( !$DB_AREA->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$area_meta = $DB_AREA->fetch(PDO::FETCH_ASSOC);
		
		
		// 取得申請紀錄
		// area_name,apply_date,apply_reason,member_count
		$DB_HIS= $this->DBLink->prepare( SQL_AdBook::GET_APPLY_HISTORY() );
		$DB_HIS->bindParam(':apply_code'   	   , $DataCode );	
		$DB_HIS->bindParam(':applicant_name'   , $record['applicant_name']  );	
		$DB_HIS->bindParam(':applicant_mail'   , $record['applicant_mail']  );	
		if( !$DB_HIS->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$apply_history = $DB_HIS->fetchAll(PDO::FETCH_ASSOC);
		
		
		//-- 整理輸出資料
		$apply_read = array();
		
		// for editor  default
		$apply_read['area_name'] = $area_meta['area_name'];
		$apply_read['area_type'] = $area_meta['area_type'];
		$apply_read['apply_code'] = $record['apply_code'];
		$apply_read['apply_date'] = $record['apply_date'];
		$apply_read['apply_review'] = $record['_review'];
		$apply_read['applicant_name'] = $record['applicant_name'];
		$apply_read['apply_checker'] = $record['_checker'];
		$apply_read['check_note'] = $record['check_note'];
		
		
		$apply_data = json_decode($record['apply_form'],true);
		
		
		// for field
		$result['data']['apply'] = $apply_read;
		
		// for applicant
		$result['data']['applicant'] = array('info'=>array(),'history'=>array());
		$result['data']['applicant']['info'] = $record['applicant_info']  ? json_decode($record['applicant_info'],true) : array();
		
		// for attachment
		$result['data']['attachment'] = isset($apply_data['attach']) ? $apply_data['attach'] : array();
		
		// for progress
		$result['data']['progress'] = $record['_progres'] ? json_decode($record['_progres'],true) :  ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		$result['data']['stagenow'] = $record['_stage'];
		
		// for applicant history 
		$result['data']['history'] = $apply_history;
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Booking Review Book Data 
	// [input] : DataCode    :  area_booking.apply_code;
	// [input] : ReviewData  :  base64M_encode => array();
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_Review_Book_Data($DataCode='',$ReviewData=array(),$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('review');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$apply_review = json_decode(base64_decode(str_replace('*','/',rawurldecode($ReviewData))),true); 
		
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($booking['am_id'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		
		$apply_process   = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		
		
		
		$apply_new_stage = $booking['_stage'];
		
		$ballot_flag   = $booking['_ballot'];
		$ballot_result = $booking['_ballot_result'];
		
		$to_sent_mail  = false;
		$apply_new_status  =  $booking['_status'];
		$is_final      = false;
		
		//$apply_review['status']
		//$apply_review['notes']
		
		// 設定審查狀態
		switch($apply_review['status']){
		  
		  case '外審同意':
		    if(!isset($apply_process['admin'])) $apply_process['admin'] = [ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]];
			$apply_process['admin'][0][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'外審同意','note'=>'經'.$this->USER->UserID.'同意申請','user'=>$this->USER->UserID,'logs'=>'');
            $review_note = date('Y-m-d H:i:s').':外審同意 by '.$this->USER->UserID;
			$check_note = trim($booking['check_note']);
			$check_note = $check_note ? $review_note."\n".$check_note : $review_note;
			
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('check_note'))); 
			$DB_UPD->bindValue(':apply_code' , $booking['apply_code']);
		    $DB_UPD->bindValue(':check_note' , $check_note );
		    $DB_UPD->execute();
			
			break;
		  
		  case '外審異議':
		    if(!isset($apply_process['admin'])) $apply_process['admin'] = [ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]];
			$apply_process['admin'][0][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'外審不同意','note'=>'理由：'.$apply_review['notes'],'user'=>$this->USER->UserID,'logs'=>'');
            $review_note = date('Y-m-d H:i:s').'外審不同意 by '.$this->USER->UserID."\n"."理由：".$apply_review['notes'];
			$check_note = trim($booking['check_note']);
			$check_note = $check_note ? $review_note."\n".$check_note : $review_note;
			
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('check_note'))); 
			$DB_UPD->bindValue(':apply_code' , $booking['apply_code']);
		    $DB_UPD->bindValue(':check_note' , $check_note );
		    $DB_UPD->execute();
			break;
		  
		  
		  case '個案通過':
            
		    
			$apply_process['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審查通過','note'=>$apply_review['notes'],'logs'=>'');
		    $apply_process['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取核准','note'=>'','logs'=>'');		
			$apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
			
            $apply_process['admin'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'個案通過','note'=>'因特殊原因讓個案核准進入','logs'=>'');
		                         
			
			$apply_new_status='核准進入';
			$apply_new_stage = 5;
			$to_sent_mail = true;
			  
			$is_final = true;
			break;  
			  
			  
		  case '審查通過':
          case '不須審查':		  
		    
			$apply_process['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$apply_review['status'],'note'=>$apply_review['notes'],'logs'=>'');
		      
			//確認抽籤狀態
			if(!$ballot_flag || ($ballot_flag && ($ballot_result==1||$ballot_result==4)) ){ //不用抽籤 || 抽籤而且正取	|| 擊劍送沈
			  $apply_process['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取核准','note'=>'','logs'=>'');		
			  $apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
			  
			  $apply_new_status='核准進入';
			  $apply_new_stage = 5;
			  $to_sent_mail = true;
			  
			  $is_final = true;
			  
			}else if($ballot_flag && $ballot_result==2){ 
			  // 抽籤但備取
			  $apply_process['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取等待','note'=>'','logs'=>'');		
			  $apply_new_status='備取等待';
			  $apply_new_stage = 4;
			  $to_sent_mail = true;
			}else{
              // 要抽籤但是處於未知狀態
			  $apply_process['review'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'抽籤錯誤','note'=>'','logs'=>'');
			  
			  
			}
			break;
			
		  case '資格未符':
          case '補件駁退':		 
            $apply_process['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$apply_review['status'],'note'=>$apply_review['notes'],'logs'=>'');
		    $apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審查未過','note'=>'','logs'=>'');
			
			$apply_new_status='審查未過';
			$apply_new_stage = 5;
			$to_sent_mail = true;
			$is_final = true;  
			  
			break;
			
		  case '備取成功':
		    $apply_process['admin'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取成功','note'=>'','logs'=>'');	
			$apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'備取成功','logs'=>'');
		    
			$apply_new_status='核准進入';
			$apply_new_stage = 5;
			$to_sent_mail = true;
			$is_final = true;  
			break;
			
		  
          case '備取失敗':		 
            $apply_process['admin'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取失敗','note'=>'','logs'=>'');	
		    $apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'備取失敗','logs'=>'');
			
			$apply_new_status='申請註銷';
			$apply_new_stage = 5;
			$to_sent_mail = true;
			$is_final = true;  
			break; 
			
		  
          case '陳核長官':	
		    $DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('_review'))); 
	        $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		    $DB_UPD->bindValue(':_review'   , 1);
		    $DB_UPD->execute();
		    
		  case '重新審查':
		    
			array_pop($apply_process['client'][5]);
		    
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('_final'))); 
	        $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		    $DB_UPD->bindValue(':_final'    , '');
		    $DB_UPD->execute();
		    
          case '資料不全':  
			$apply_process['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$apply_review['status'],'note'=>$apply_review['notes'],'logs'=>'');
		    $apply_new_stage = 3;
			$apply_new_status=$apply_review['status'];
			
			if($apply_review['status']=='資料不全' ){
			  $to_sent_mail = true;	
			}
			  
			break;
		   
		  case '申請未到':
          case '申請註銷':
            $apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$apply_review['status'],'note'=>'經由後台設定@'.$this->USER->UserID,'logs'=>'');
		    $apply_new_status=$apply_review['status'];
			$apply_new_stage = 5;
			$is_final = true;
			break;
          
          
			
		  default:
            throw new Exception('_APPLY_SUBMIT_FAIL');  
			break;
		}
		
		// checked if is final
		if($is_final){
		  // UPD Final Status
		  $DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('_final'))); 
	      $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		  $DB_UPD->bindValue(':_final'    , $apply_new_status);
		  $DB_UPD->execute();
		}
		
		// UPDATE status & progress 
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STATUS()); 
	    $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		$DB_UPD->bindValue(':status'    , $apply_new_status);
		$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		// Update stage
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STAGE()); 
	    $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		$DB_UPD->bindValue(':stage'     , $apply_new_stage );
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		// final
		$result['data']['progress'] = $apply_process;
		$result['data']['stagenow'] = $apply_new_stage;
		$result['data']['sentmail'] = $to_sent_mail;
		
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Booking Set Book Stage 
	// [input] : DataCode    :  area_booking.apply_code;
	// [input] : StageNo     :  int;
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_Set_Data_Stage($DataCode='',$StageNo=NULL ,$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('review');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$apply_stage = intval($StageNo); 
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($booking['am_id'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		
		$apply_process   = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		$apply_new_stage = $booking['_stage'];
		
		$ballot_flag   = $booking['_ballot'];
		$ballot_result = $booking['_ballot_result'];
		
		// 設定審查狀態
		switch($StageNo){
		  case 3:  // 急件送審
		    $apply_process['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'急件送審','note'=> $this->USER->UserID.' 經由後台設定送審','logs'=>'');
		    $apply_process['admin'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'取消抽籤','note'=> '','logs'=>'');
		    
			$apply_new_stage = 3;
			
			
			break;
		  
		  default:
            break;
		}
		
		$booking_update = [];
		$booking_update['_stage'] = $apply_new_stage;
		$booking_update['_status'] = '急件送審';
		$booking_update['_progres'] = json_encode($apply_process);
		$booking_update['_ballot_result'] = 4;
		
		
		$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array_keys($booking_update))); 
	    $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		$DB_UPD->bindValue(':_status'    ,'急件送審');
		$DB_UPD->bindValue(':_stage'     , $apply_new_stage );
		$DB_UPD->bindValue(':_progres'   , json_encode($apply_process));
		$DB_UPD->bindValue(':_ballot_result'   , 4);

		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		$result['data']['progress'] = $apply_process;
		$result['data']['stagenow'] = $apply_new_stage;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Booking Export Attachment 
	// [input] : DataCode    	:  area_booking.apply_code;
	// [input] : AttachmentFile :  hash.pdf ;
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_Get_Apply_Attachment($DataCode='',$AttachmentFile='' ,$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($booking['am_id'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		$file_location = _SYSTEM_CLIENT_PATH.$DataCode.'/'.$AttachmentFile;
		if(!is_file(_SYSTEM_CLIENT_PATH.$DataCode.'/'.$AttachmentFile)){
		  throw new Exception('_APPLY_SUBMIT_ATTACHMENT_UNFOUND');
		}
		
		$apply_form = json_decode($booking['apply_form'],true);
		$apply_attachs = $apply_form['attach'];
		
		$file_meta = array();
		foreach($apply_attachs as $attachment){
		  if( $attachment['code'] == $AttachmentFile){
			$file_meta = $attachment;
			break;
		  }
		}
		
		if(!count($file_meta)){
		  throw new Exception('_APPLY_SUBMIT_ATTACHMENT_UNFOUND');	
		}
		
		$result['data']['name'] = $DataCode.'-'.$file_meta['file'].'.'.pathinfo($file_location,PATHINFO_EXTENSION);
		$result['data']['size'] = filesize($file_location);
		$result['data']['location'] = $file_location;  
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	//-- Admin Area Save Area Data 
	// [input] : ApplyCode    :  \w\d{8};  = DB.area_booking.apply_code;
	// [input] : DataModify   :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_Save_Data( $ApplyCode='' , $DataModify='' ,$AreaAccessMap=array() ){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataModify))),true); 
	  
	  try{  
		
		// 檢查申請序號
	    if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_EDITED() );
		$DB_GET->bindParam(':abno'   , $ApplyCode );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($orl_data['AREAID'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		// 檢查更新欄位是否合法
		foreach($data_modify as $mf => $mv){
		  if(!isset($orl_data[$mf])){
		    unset($data_modify[$mf]);
		  }
		}
		
		if($data_modify && count($data_modify)){
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array_keys($data_modify)));
		  $DB_SAVE->bindValue(':apply_code' , $ApplyCode);
		  foreach($data_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		}
		
		// final 
		$result['data']   = $ApplyCode;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Book Admin regist mail 
	// [input] : DataCode  :  string;
	public function ADBook_Regist_Mail($DataCode=0 ){
	  $result_key = parent::Initial_Result('mail');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 取得區域資料
		$area_meta = array();
		$DB_AREA= $this->DBLink->prepare( SQL_AdBook::GET_AREA_META() );
		$DB_AREA->bindParam(':ano'   , $booking['am_id'] );	
		if( !$DB_AREA->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$area_meta = $DB_AREA->fetch(PDO::FETCH_ASSOC);
		
		// 解析訊息
		$progress = json_decode($booking['_progres'],true);
		$application = json_decode($booking['apply_form'],true);
		
		// 設定通知信種類
		$mail_title_type  = '';
		$mail_status_type = '';
		$mail_status_info = '';
		
		switch( $booking['_stage'] ){
		  case 1: 
		    $mail_title_type  = '受理通知'; 
		    $mail_status_type = '已確認收到申請資料，進入待審程序';
		    break;
          
		  case 3: 
		    $mail_title_type = '審查通知'; 
		    if(isset($progress['review'][$booking['_stage']])){
			  $message_conf = array_pop($progress['client'][$booking['_stage']]);
			  
			  $mail_status_type = $message_conf['status'];
			  $mail_status_info = $message_conf['note'];
              $mail_check_note = $booking['check_note'];

			  
			}
			break;
			
		  case 5: $mail_title_type = '結果通知'; 
		    if(isset($progress['client'][$booking['_stage']])){
			  $message_conf = array_pop($progress['client'][$booking['_stage']]);
			  $mail_status_type = $message_conf['status']; 
			  $mail_status_info = $message_conf['note'];	
			}
			break;
		  default: $mail_title_type = '申請通知';break;
		}
		
		
		// 註冊存取序號
		$license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);
		
		// 設定信件內容
        $to_sent = $booking['applicant_mail'];
		
        $mail_title = _SYSTEM_HTML_TITLE." / ".$mail_title_type." / 申請編號：".$booking['apply_code'];        
		
        $mail_content  = "<div>申請人 您好：</div>";
		$mail_content .= "<div>台端於 <strong>".$booking['apply_date']."</strong> 申請進入『".$area_meta['area_name']."』 </div>";
		$mail_content .= "<div>申請狀態：".$mail_status_type."</div>";
		if($mail_status_info){
		  $mail_content .= "<div >訊息通知：<span style='color:red;font-weight:bold;'>".$mail_status_info."</span></div>";	
		}
		if(isset($mail_check_note)){
		  $mail_content .= "<div >審核註記：<span style='color:red;font-weight:bold;'>".$mail_check_note."</span></div>";		
		}
		
		$mail_content .= "<div>申請連結："._SYSTEM_SERVER_ADDRESS.'index.php?act=Landing/direct/'.$booking['apply_code'].'/'.$license_access_key."</div>";
		
		$mail_content .= "<div> <br/> </div>";
		$mail_content .= "<div>一、本案申請資料如下：</div>";
		$mail_content .= "<table><tr><td>(一)進入期間</td><td>：".$booking['date_enter']." ~ ".$booking['date_exit']."</td></tr>";
		$mail_content .= "<tr><td>(二)進入區域/入口/出口</td><td>：".join(';',$application['area']['inter']).' / '.$application['area']['gate']['entr'].' / '.$application['area']['gate']['exit']."</td></tr>";
		$mail_content .= "<tr><td>(三)申請代表人或領隊</td><td>：".$booking['applicant_name']."</td></tr>";
		$mail_content .= "<tr><td>(四)人數</td><td>：共 ".$booking['member_count']." 人</td></tr>";
		$mail_content .= "<tr><td>(五)申請編號</td><td>：".$booking['apply_code']." </td></tr></table>";
		$mail_content .= "<div><br/><br/></div>";
		$mail_content .= "<div>二、請妥善保管申請編號及隨時注意電子信箱訊息或登入「申請單查詢」頁面，掌握申請進度狀態、補發編號、申請資料補充或修改及取消申請等事宜，並以查詢之內容為準。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>三、審查管理機關(構)依保護(留)區相關法規、經營管理計畫等，保有核准及後續進入之管制權利(例如：為災害防救或重大疫病蟲害及其他原因必須緊急處理之必要時，得逕行關閉或限制人員進出等措施)，並以系統最新消息公告為準 。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>四、為維護自然生態，各保護區設有進入人數之承載量管制，若申請截止日(依據各區設定)總人數逾越承載量，系統將進行隨機抽籤，並發給審查通知或結果通知等狀態之電子郵件，請隨時留意通知內容，有資料不全通知補件時，應盡速補件，未於期限內補件者將予以駁退。已通知核准進入者，請登入申請單查詢頁面下載許可證，未出示許可證者禁止進入自然保護區域。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域管理機關(構)查詢。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
		$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";
			
		      			  
        
		// 註冊信件工作
		$mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$to_sent,$mail_paser)) ? trim($mail_paser[1]) : trim($to_sent);
		if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
		  throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
		}
		
		$mail_logs = [date('Y-m-d H:i:s')=>'Regist Alert Mail From ['.$booking['apply_code'].'].' ];
		
		$DB_MAILJOB	= $this->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
		$DB_MAILJOB->bindValue(':mail_type','狀態通知');
		$DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
		$DB_MAILJOB->bindValue(':mail_to',$mail_to_sent);
		$DB_MAILJOB->bindValue(':mail_title',$mail_title);
		$DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
		$DB_MAILJOB->bindValue(':creator',$this->USER->UserID);
		$DB_MAILJOB->bindValue(':editor',$this->USER->UserID);
		$DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
		$DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
		if(!$DB_MAILJOB->execute()){
		  throw new Exception('_APPLY_MAIL_REGIST_FAIL');	
		}
		
		// final 
		$result['data']['maildate']   = date('Y-m-d');
		$result['data']['mailuser']   = $booking['applicant_name'].'('.$booking['applicant_mail'].')';
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Client Form Application Page 
	// [input] : ApplyCode  : from submit;
	// [input] : ShowType 顯示模式 / preview / license / ...
	
	public function ADBook_Feform_Application_Page($ApplyCode='',$ShowType='preview'){
      
	  $result_key = parent::Initial_Result('license');
	  $result  = &$this->ModelResult[$result_key];
	  try{ 	  
       
	    // 取得申請資料
		//SELECT area_booking.*,area_code,area_type,area_name FROM area_booking LEFT JOIN area_main ON ano=am_id WHERE apply_code=:apply_code AND area_booking._keep=1;
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_META());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCode))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		$applicant 	 = json_decode($booking['applicant_info'],true);
		$joinmember	 = json_decode($booking['member'],true);
		$application = json_decode($booking['apply_form'],true);
		
		$enter_dates = ($booking['date_exit'] == $booking['date_enter']) ? 1 : intval(abs(strtotime($booking['date_exit']) - strtotime($booking['date_enter']))/86400);
		
		
		// apply license part 1 // 投遞聯
		
		$license  = array();
        
		$license[0]  = "  <h1>".$booking['area_type']."進入許可證</h1>"; 
		$license[0] .= "  <div class='note'> 投遞聯<br/>（請沿虛線撕下本聯，若出入口及行程中設有投遞信箱時投入） </div>"; 
		$license[0] .= "  <h2>申請事項：</h2> ";
		$license[0] .= "  <table class='application'>";
		$license[0] .= "    <tr ><th> 申請區域名稱 </th><td colspan=4 >".$booking['area_name']."</td></tr>";
		$license[0] .= "    <tr ><th> 申請編號 </th><td>".$booking['apply_code']."</td><th> 申請人與進入人數 </th><td>".$booking['applicant_name']."，等" .$booking['member_count']."人 </td></tr>";
		$license[0] .= "    <tr ><th> 申請目的/項目 </th><td colspan=4 >".$booking['apply_reason']."</td></tr>";
		$license[0] .= "    <tr ><th> 進入期間 </th><td colspan=4 >".$booking['date_enter']." ~ ".$booking['date_exit']."，共 ".$enter_dates." 日</td></tr>";
		$license[0] .= "    <tr ><th> 進入範圍 </th><td colspan=4 >".join('、',$application['area']['inter'])."</td></tr>";
		$license[0] .= "    <tr ><th> 預計進入入口 </th><td>".$application['area']['gate']['entr']."</td><th> 預計離開出口 </th><td>".$application['area']['gate']['exit']."</td>";
		$license[0] .= "    <tr ><th> 當日抵達入口時間 </th><td class='handwriting'>(請自行填寫)</td><th> 當日實際進入人數 </th><td class='handwriting'>(請自行填寫)</td>";
		$license[0] .= "  </table >";
		$license[0] .= "  <hr></hr>";
		
		// apply license part 2 // 收執聯
		$field_group = '申請資料';
		
		$license[0] .= "  <h1>".$booking['area_type']."自然保留區進入許可證</h1>"; 
		$license[0] .= "  <div class='note'> 收執聯<br/>（請保留本聯，進入時隨身攜帶以備查驗） </div>"; 
		
		$license[1]  = "  <h2>申請事項：</h2> ";
		$license[1] .= "  <table class='application'>";
		$license[1] .= "    <tr ><th> 申請區域名稱 </th><td colspan=4 >".$booking['area_name']."</td></tr>";
		$license[1] .= "    <tr ><th> 申請編號 </th><td>".$booking['apply_code']."</td><th> 申請人與進入人數 </th><td>".$booking['applicant_name']."，" .$booking['member_count']."人 </td></tr>";
		$license[1] .= "    <tr ><th> 申請目的/項目 </th><td colspan=4 >".$booking['apply_reason']."</td></tr>";
		$license[1] .= "    <tr ><th> 進入期間 </th><td colspan=4 >".$booking['date_enter']." ~ ".$booking['date_exit']."，共 ".$enter_dates." 日</td></tr>";
		$license[1] .= "    <tr ><th> 進入範圍 </th><td colspan=4 >".join('、',$application['area']['inter'])."</td></tr>";
		$license[1] .= "    <tr ><th> 預計進入入口 </th><td>".$application['area']['gate']['entr']."<br/>".$application['area']['gate']['entr_time']."</td><th> 預計離開出口 </th><td>".$application['area']['gate']['exit']."<br/>".$application['area']['gate']['exit_time']."</td>";
		
		if( isset($application['fields'])&&count($application['fields'])){
		  $license[1] .= "    <tbody class='additional_fields'>";	
		  $license[1] .= "    <tr ><th colspan=4 class='field_set'> ".$field_group." </th></tr>";
		  foreach($application['fields'] as $field_id => $fcontent){
            /*
			if($field_group != $fcontent['group']){
			  $license .= "    <tr ><th colspan=6> ".$field_group." </th></tr>";	
			  $field_group == $fcontent['group'];
			} 
			*/
			$license[1] .= "    <tr ><th colspan=4> ".$fcontent['field']." </th></tr>";	
			$license[1] .= "    <tr ><td colspan=4> ".nl2br($fcontent['value'])." </th></tr>";	
		  }
		  $license[1] .= "    </tbody>";	
		}
		$license[1] .= "  </table >";
		
		
		// apply license part 3 // 隊員名單
		$license[2]  = "  <hr class='break' style='visibility:hidden;'></hr>";
		$license[2] .= "  <h2>隊員名單：</h2> ";
		$license[2] .= "  <table class='joing_member' >";
		$license[2] .= "    <tr ><th>NO.</th><th>角色</th><th>姓名</th><th>基本資料</th><th>緊急聯繫人資料</th></tr>";
		foreach($joinmember as $mbrno => $member){
          $license[2] .= "  <tbody class=''><tr class='member_detail'>";	
		  $license[2] .= "    <td>".($mbrno+1)."</td>"; 
		  $license[2] .= "    <td>".$member['member_role']."</td>"; 
		  $license[2] .= "    <td>".$member['member_name']."</td>";
		  $license[2] .= "    <td class='member_info'>";
		  $license[2] .= "      <div><label>證件號碼</label><span>".$member['member_id']."</span><label>出生日期</label><span>".$member['member_birth']."</span></div>";
		  $license[2] .= "      <div><label>聯絡電話</label><span>".$member['member_tel']." / ".$member['member_cell']."</span></div>";
		  $license[2] .= "      <div><label>聯絡地址</label><span>".$member['member_addr']."</span></div>";
		  $license[2] .= "    </td>";
		  $license[2] .= "    <td>".$member['member_contacter']."<br/>".$member['member_contactto']."</td>";
		  $license[2] .= "  </tr></tbody>"; 
		}
		$license[2] .= "  </table >";
		
		
		// apply license part 4 // 注意事項與聯繫電話
		$license[3]  = "  <hr class='break' style='visibility:hidden;'></hr>";
		$license[3] .= "  <h2>進入規定與注意事項：</h2> ";
		$license[3] .= "  <div class='regulation'>";
		$license[3] .= "    <p>一、進入保護(留)區人員應隨身攜帶許可證收執聯及身分證明證照，並隨時接受管理機關（構）查驗。</p>";
		$license[3] .= "    <p>二、確實遵守保護(留)區各項有關法令(文化資產保存法、野生動物保育法及森林法等)，若有違反即取消本許可。</p>";
		$license[3] .= "    <p>三、進入自然保留區人員除經主管機關許可外，禁止為下列行為，違反規定者，管理機關(構)應即制止取締，報請主管機關依本法相關規定處理及廢止其進入許可。違規行為人三年內不得再行申請進入自然保留區：";
		$license[3] .= "    <ul>";
		$license[3] .= "      <li>（一）改變或破壞其原有自然狀態。</li>";
		$license[3] .= "      <li>（二）攜入非本自然保留區原有之動植物(含寵物)。</li>";
		$license[3] .= "      <li>（三）採集標本。</li>";
		$license[3] .= "      <li>（四）在自然保留區內喧鬧或干擾野生物。</li>";
		$license[3] .= "      <li>（五）於植物、岩石及標示牌上另加文字、圖形或色帶等標示。</li>";
		$license[3] .= "      <li>（六）擅自進入指定地點以外之區域。</li>";
		$license[3] .= "      <li>（七）污染環境，丟棄廢棄物。</li>";
		$license[3] .= "      <li>（八）其他破壞或改變原有自然狀態之行為。</li>";
		$license[3] .= "    </ul>";
		$license[3] .= "    <p>四、保護(留)區有遭受天然、人為或其他不明原因危害或重大疫病蟲害侵襲之虞時，管理機關（構）得逕行關閉或限制人員進出，或採取其他必要措施並以系統最新公告為準；已申請核准許可進入者，應重新申請。</p>";
		$license[3] .= "    <p>五、如因天候不佳等不可抗力之因素，最晚可於進入前1日允許註銷，但為顧及備取等待者權益，進入日期6日以前確實無法進入，請務必提早註銷，以利系統提供名額備取。</p>";
		$license[3] .= "    <p>六、若經查1年內有3次以上惡意申請之情形(例如：未註銷無故不進入、申請團體3人以上卻進入人數未達3人、未申請進入等)，將予以登錄為禁止申請名單並嚴格管制。</p>";
		$license[3] .= "    <p>七、保護(留)區內部分地區常有毒蛇、毒蜂、蟲獸出沒及天候易變、地形險峻， 常有落石崩塌等危險，進入者應自行注意安全。</p>";
		$license[3] .= "  </div>";
		$license[3] .= "  <hr class='break' style='visibility:hidden;'></hr>";
		
		$license[3] .= "  <h2>各管理機關(構)聯繫電話：</h2> ";
		$license[3] .= "  <table class='contect' >";
		$license[3] .= "    <tr ><th>管理單位</th><th>聯絡電話</th><th>轄管自然保留區名稱</th></tr>";
		$license[3] .= "    <tr ><td>新竹林區管理處育樂課</td><td>03-5224163</td><td>插天山自然保留區<br/>苗栗三義火炎山自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>南投林區管理處育樂課</td><td>049-2365226</td><td>九九峰自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>嘉義林區管理處育樂課</td><td>05-2787006</td><td>臺灣一葉蘭自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>屏東林區管理處育樂課</td><td>08-7236941</td><td>出雲山自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>臺東林區管理處育樂課</td><td>089-324121</td><td>大武山自然保留區<br/>大武事業區臺灣穗花杉自然保留區<br/>臺東紅葉村臺東蘇鐵自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>羅東林區管理處育樂課</td><td>03-9545114</td><td>淡水河紅樹林自然保留區<br/>坪林台灣油杉自然保留區<br/>烏石鼻海岸自然保留區<br/>南澳闊葉樹林自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>新北市政府農業局</td><td>02-2960-3456</td><td>挖子尾自然保留區</td></tr>";
		$license[3] .= "    <tr ><td>澎湖縣政府農漁局</td><td>06-9262620</td><td>澎湖玄武岩自然保留區<br/>澎湖南海玄武岩自然保留區</td></tr>";
		$license[3] .= "    <tr ><td></td><td></td><td></td></tr>";
		$license[3] .= "  </table >";
		
		$license[3] .= "  <h1>林務局自然保留區暨自然保護區進入申請系統 敬啟</h1> ";
		
		// 設定輸出模式
		$license_display = $ShowType=='preview' ? $license[1].$license[2] : join('',$license);
		
		$result['data']['EXPORT_INFO']   = date('Y-m-d H:i:s').' by '.$this->USER->UserID;
		$result['data']['BOOKED_DATE']   = $booking['apply_date'];
		$result['data']['BOOKED_CODE']   = $booking['apply_code'];
		$result['data']['REVIEW_NOTE']   = $booking['check_note'];
		$result['data']['PAGE_CONTENT']   = "<div class='license_page'>". $license_display ."</div>";
		
		
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  return $result; 	
	}
	
	
	
	/*== [ Book Checker Function ] ==*/
	
	
	
	//-- Admin Book : Get Group Roles Book List 
	// [input] : SearchString ;
	
	public function Admin_Book_Get_List_For_GroupRole( $SearchString='' ){
	  
	  $result_key = parent::Initial_Result('records');
	  $result     = &$this->ModelResult[$result_key];
	  
	  
	  $apply_search = json_decode(base64_decode(str_replace('*','/',rawurldecode($SearchString))),true); 
	  
	  try{
	    
		$area_list = array(); // 存放區域資料
		$area_sets = array(); // 存放區域索引
		
		$user_groups = array(); // 存放使用者群組
		if(isset($this->USER->PermissionQue)){
		  $user_groups = array_keys($this->USER->PermissionQue);	
		}
		
		// 取得管制區域列表
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SELECT_USER_AREA()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $area_list[$tmp['ano']] = $tmp;
		  $area_sets[$tmp['ano']] = $tmp['area_code']; 
		}
		
		
		// 搜尋條件
		$condition = array();
		$orderby   = 'date_enter DESC,date_exit DESC';
		
		// 條件篩選 
		if(is_array($apply_search)){
		  foreach( $apply_search as $filter_target => $filter_set ){   // 取名為filter 是因為篩選欄位設定與meta欄位不完全一樣，需要重新轉換
		    switch($filter_target){
			  case 'apply_search':   $condition[] = " (apply_code='".$filter_set."' OR apply_date='".$filter_set."' OR applicant_name LIKE'%".$filter_set."%' OR applicant_mail LIKE'%".$filter_set."%' OR applicant_id LIKE'%".$filter_set."%' OR apply_reason LIKE'%".$filter_set."%' OR _status LIKE'%".$filter_set."%' OR member LIKE'%".$filter_set."%')"; break;
			  case 'apply_area':   $condition[] = " am_id='".$filter_set."' "; break;
			  case 'apply_code':   $condition[] = " apply_code='".$filter_set."' " ; break;
			  case 'range_start':  
			    if( strtotime($filter_set) ){
				  $condition[] = "date_exit >='".$filter_set."' ";	
				}
				break;
			  case 'range_end':  
			    if( strtotime($filter_set) ){
				  $condition[] = "date_enter <='".$filter_set."' ";	
				}
				break;
			}
		  }
		}else{
		  $apply_search['range_start'] = date('Y-m-d');
		  $apply_search['range_end'] = date('Y-m-d');
		  $condition[] = "date_exit >='".date('Y-m-d')."' ";	
		  $condition[] = "date_enter <='".date('Y-m-d')."' ";		
		}
		
		// 取得篩選後申請資料
		$records = array(); // 存放列表資料
		
		$sqlsearch = count($condition) ? join(' AND ',$condition) : 1;
		
		$DB_BOOK = $this->DBLink->prepare(SQL_AdBook::SELECT_AREA_BOOKING(array_keys($area_list),$sqlsearch,$orderby));
		if(!$DB_BOOK->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($tmp = $DB_BOOK->fetch(PDO::FETCH_ASSOC)){
		  
		  $record = array();
		  $record['abno'] = $tmp['abno'];
		  $record['r_apply_date'] = $tmp['apply_date'];
		  $record['r_apply_code'] = $tmp['apply_code'];
		  $record['r_apply_area'] = $area_list[$tmp['am_id']]['area_name'];
		  $record['r_apply_user'] = $tmp['applicant_name'];
		  $record['r_countmbr']   = $tmp['member_count'];
		  $record['r_apply_period'] = $tmp['date_enter'].' ~ '.$tmp['date_exit'];
		  $record['r_status']     = $tmp['_status'];
          $records[] = $record;
		}
		
		$result['action'] = true;		
		$result['data']['list']   = $records;	
		$result['data']['filter'] = $apply_search;
		
		$result['session']['ADAREASMAP'] = $area_sets;	
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Admin Book : Get Group Roles Book List  // 外審人員確認單
	// [input] : SearchString ;
	// 外審人員僅能就尚未進入審查之資料進行標示
	
	public function Admin_Book_Get_List_For_Preview( $SearchString='' ){
	  
	  $result_key = parent::Initial_Result('records');
	  $result     = &$this->ModelResult[$result_key];
	  
	  $apply_search = json_decode(base64_decode(str_replace('*','/',rawurldecode($SearchString))),true); 
	  
	  try{
	    
		$area_list = array(); // 存放區域資料
		$area_sets = array(); // 存放區域索引
		
		$user_groups = array(); // 存放使用者群組
		if(isset($this->USER->PermissionQue)){
		  $user_groups = array_keys($this->USER->PermissionQue);	
		}
		
		// 取得管制區域列表
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdBook::SELECT_USER_AREA()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $area_list[$tmp['ano']] = $tmp;
		  $area_sets[$tmp['ano']] = $tmp['area_code']; 
		}
		
		
		// 搜尋條件
		$condition = array();
		$orderby   = 'date_enter DESC,date_exit DESC';
		
		// 條件篩選 
		// 外審人員條件僅限制搜尋申請日期
		
		if(is_array($apply_search)){
		  foreach( $apply_search as $filter_target => $filter_set ){   // 取名為filter 是因為篩選欄位設定與meta欄位不完全一樣，需要重新轉換
		    switch($filter_target){
			  case 'range_start':  
			    if( strtotime($filter_set) ){
				  $condition[] = "date_exit >='".$filter_set."' ";	
				}
				break;
			  case 'range_end':  
			    if( strtotime($filter_set) ){
				  $condition[] = "date_enter <='".$filter_set."' ";	
				}
				break;
			}
		  }
		}else{
		  $apply_search['range_start'] = date('Y-m-d');
		  $apply_search['range_end'] = date('Y-m-d');
		  $condition[] = "date_exit >='".date('Y-m-d')."' ";	
		  $condition[] = "date_enter <='".date('Y-m-d')."' ";		
		}
		
		// 取得篩選後申請資料
		$records = array(); // 存放列表資料
		
		$sqlsearch = count($condition) ? join(' AND ',$condition) : 1;
		$sqlsearch = "((".$sqlsearch.") OR apply_date <= '".date('Y-m-d')."') AND _status IN('收件待審','正取送審')";
		
		
		$DB_BOOK = $this->DBLink->prepare(SQL_AdBook::SELECT_AREA_BOOKING(array_keys($area_list),$sqlsearch,$orderby));
		if(!$DB_BOOK->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($tmp = $DB_BOOK->fetch(PDO::FETCH_ASSOC)){
		  
		  $record = array();
		  $record['abno'] = $tmp['abno'];
		  $record['r_apply_date'] = $tmp['apply_date'];
		  $record['r_apply_code'] = $tmp['apply_code'];
		  $record['r_apply_area'] = $area_list[$tmp['am_id']]['area_name'];
		  $record['r_apply_user'] = $tmp['applicant_name'];
		  $record['r_countmbr']   = $tmp['member_count'];
		  $record['r_apply_period'] = $tmp['date_enter'].' ~ '.$tmp['date_exit'];
		  $record['r_status']     = $tmp['_status'];
		  
		  $record['r_application'] = json_decode($tmp['apply_form'],true);
		  $record['r_members'] = [];
		  $members = json_decode($tmp['member'],true);
		  foreach($members as $m){
			$mbr = array();
            $mbr['role'] = $m['member_role'];
 			$mbr['name'] = $m['member_name'];
 			$mbr['sex']  = isset($m['member_sex']) ? $m['member_sex'] : '未填' ;
 			$mbr['addr'] = mb_substr($m['member_addr'],0,10);
 		    $record['r_members'][] = $mbr;  
		  }
		  
		  $apply_process  = json_decode($tmp['_progres'],true);
		  $record['r_reviewlogs'] = isset($apply_process['admin'])&&is_array($apply_process['admin']) ? array_reverse($apply_process['admin'][1]) : [];
		  
		  $records[] = $record;
		}
		
		$result['action'] = true;		
		$result['data']['list']   = $records;	
		$result['data']['filter'] = $apply_search;
		
		$result['session']['ADAREASMAP'] = $area_sets;	
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Admin Booking Local master Review Book Data //外審人員審查申請  僅可設定 : 外審同意 | 外審異議
	// [input] : DataCode    :  area_booking.apply_code;
	// [input] : ReviewData  :  base64M_encode => array();
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_LocalAD_Review_Book_Data($DataCode='',$ReviewData=array(),$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('review');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$apply_review = json_decode(base64_decode(str_replace('*','/',rawurldecode($ReviewData))),true); 
		
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8,10}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查是否有存取權限
	    if(!isset($AreaAccessMap[intval($booking['am_id'])])){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
		}
		
		
		$apply_process   = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		
		$apply_new_stage = $booking['_stage'];
		$ballot_flag   = $booking['_ballot'];
		$ballot_result = $booking['_ballot_result'];
		
		$to_sent_mail  = false;
		$apply_new_status  =  $booking['_status'];
		$is_final      = false;
		
		
		// 設定審查狀態
		switch($apply_review['status']){
		  
		  case '外審同意':
		    if(!isset($apply_process['admin'])) $apply_process['admin'] = [ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]];
			$apply_process['admin'][1][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'外審同意','note'=>'經'.$this->USER->UserID.'同意申請','user'=>$this->USER->UserID,'logs'=>'');
            $review_note = date('Y-m-d H:i:s').':外審同意 by '.$this->USER->UserID;
			$check_note = trim($booking['check_note']);
			$check_note = $check_note ? $review_note."\n".$check_note : $review_note;
			
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('check_note'))); 
			$DB_UPD->bindValue(':apply_code' , $booking['apply_code']);
		    $DB_UPD->bindValue(':check_note' , $check_note );
		    $DB_UPD->execute();
			
			break;
		  
		  case '外審異議':
		    if(!isset($apply_process['admin'])) $apply_process['admin'] = [ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]];
			$apply_process['admin'][1][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'外審不同意','note'=>'理由：'.$apply_review['notes'],'user'=>$this->USER->UserID,'logs'=>'');
            $review_note = date('Y-m-d H:i:s').'外審不同意 by '.$this->USER->UserID."\n"."理由：".$apply_review['notes'];
			$check_note = trim($booking['check_note']);
			$check_note = $check_note ? $review_note."\n".$check_note : $review_note;
			
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('check_note'))); 
			$DB_UPD->bindValue(':apply_code' , $booking['apply_code']);
		    $DB_UPD->bindValue(':check_note' , $check_note );
		    $DB_UPD->execute();
			break;
		  
		  default:
            throw new Exception('_APPLY_SUBMIT_FAIL');  
			break;
		}
		
		
		// UPDATE status & progress 
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STATUS()); 
	    $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		$DB_UPD->bindValue(':status'    , $apply_new_status);
		$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		// Update stage
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STAGE()); 
	    $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		$DB_UPD->bindValue(':stage'     , $apply_new_stage );
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		// final
		$result['data']   = array_reverse($apply_process['admin'][1]);
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Booking Local master Review Book Data //外審人員審查批次同意
	// [input] : ReviewData  :  base64M_encode => array();
	// [input] : AreaAccessMap       :  array(abno=>1); // from get list save to session 
	public function ADBook_LocalAD_Batch_Review_Accept($ReviewData='',$AreaAccessMap=array()){
		
	  $result_key = parent::Initial_Result('review');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		$apply_list = json_decode(base64_decode(str_replace('*','/',rawurldecode($ReviewData))),true); 
		
		foreach($apply_list as $apply_code){
			// 檢查序號
			if(!preg_match('/^[\d\w]{8,10}$/',$apply_code)){
			  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
			}
			
			// 取得資料
			$booking = NULL;
			$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
			$DB_GET->bindParam(':abno'   , $apply_code , PDO::PARAM_INT);	
			if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
			}
			
			// 檢查是否有存取權限
			if(!isset($AreaAccessMap[intval($booking['am_id'])])){
			  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');
			}
			
			$apply_process     = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
			$apply_new_stage   = $booking['_stage'];
			$apply_new_status  = $booking['_status'];
			
			if(!isset($apply_process['admin'])) $apply_process['admin'] = [ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]];
			$apply_process['admin'][1][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'外審同意','note'=>'經'.$this->USER->UserID.'同意申請','user'=>$this->USER->UserID,'logs'=>'');
			$review_note = date('Y-m-d H:i:s').':外審同意 by '.$this->USER->UserID;
			$check_note = trim($booking['check_note']);
			$check_note = $check_note ? $review_note."\n".$check_note : $review_note;
			
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('check_note'))); 
			$DB_UPD->bindValue(':apply_code' , $booking['apply_code']);
			$DB_UPD->bindValue(':check_note' , $check_note );
			$DB_UPD->execute();
			
			// UPDATE status & progress 
			$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STATUS()); 
			$DB_UPD->bindValue(':apply_code', $booking['apply_code']);
			$DB_UPD->bindValue(':status'    , $apply_new_status);
			$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
			if( !$DB_UPD->execute() ){
			  throw new Exception('_APPLY_SUBMIT_FAIL');  
			}
			
			// Update stage
			$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STAGE()); 
			$DB_UPD->bindValue(':apply_code',$booking['apply_code']);
			$DB_UPD->bindValue(':stage'     , $apply_new_stage );
			if( !$DB_UPD->execute() ){
			  throw new Exception('_APPLY_SUBMIT_FAIL');  
			}
		
		}
		// final
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
  }
  
  
?>