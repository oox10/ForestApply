<?php

  class Area_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Area Function Set ]*/ 
    
	
	//-- Admin Area Page Config 
	// [input] : NULL;
	public function ADArea_Get_Area_Config(){
	  
	  $result_key = parent::Initial_Result('config');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 查詢資料庫欄位設定
		$DB_OBJ = $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_GET_AREA_TABLE());
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
	    $field_set = array();
		foreach($data_list as $data){
		  $field_set[$data['Field']] = array('type'=>'','default'=>'');
		  
		  // 選項欄位設定成介面選項
		  if(strstr($data['Type'],'enum')){
			$field_set[$data['Field']]['type'] = 'select';
			if(preg_match_all("/\'(.*?)\'/",$data['Type'],$match)){
			  $field_set[$data['Field']]['default'] = $match[1];		
			}
		  }else if(strstr($data['Type'],'int')  &&  is_numeric($data['Default'])){
            $field_set[$data['Field']]['default'] = $data['Default'];	   			  
		  }else if( $data['Type'] == 'time' ){
            $field_set[$data['Field']]['default'] = $data['Default'];	   			  
		  }
		}
		
		$result['data']['field']   = $field_set;
        
		$result['action'] = true;
	   
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Area Page Data List 
	// [input] : NULL;
	public function ADArea_Get_Area_List(){
	  
	  $result_key = parent::Initial_Result('areas');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_GET_AREA_LIST()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		// 取得區域清單
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
		
		foreach($data_list as &$data){
		  $data['@list_filter'][]  =  $data['area_type'];
		  $data['@list_status'][]  =  $data['_open'] ? '' : 'mask';
		}
			
		$result['action'] = true;		
		$result['data']   = $data_list;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	//-- Admin Area Get Area Data 
	// [input] : DataCode  :  \w\d{8};
	
	public function ADArea_Get_Area_Data($DataCode=0){
		
	  $result_key = parent::Initial_Result('area');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\w\d]{8}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	   
		// 取得區域資料
		$area_data = NULL;
		$DB_GET	= $this->DBLink->prepare( parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_GET_AREA_VIEW_DATA()) );
		$DB_GET->bindParam(':area_code'   , $DataCode );	
		if( !$DB_GET->execute() || !$area_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
	    
		
		
		// 取得停止資料
		$area_stop = array();
		$DB_GET	= $this->DBLink->prepare( SQL_AdArea::ADMIN_AREA_GET_AREA_STOP_DATES() );
		$DB_GET->bindParam(':ano'   , $area_data['ano'] , PDO::PARAM_INT);	
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  $tmp['@data_valid'] = ( (int)date('Ymd') <= (int)str_replace('-','',$tmp['date_end']) ) ? 1 : 0;
		  $area_stop[] = $tmp;
		}
	    
		
		//取得區塊資料
		$area_block = array();
		$DB_GET	= $this->DBLink->prepare( SQL_AdArea::GET_AREA_BLOCKS() );
		$DB_GET->bindParam(':amid',$area_data['ano'] , PDO::PARAM_INT);	
		if( !$DB_GET->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		$area_block = $DB_GET->fetchAll(PDO::FETCH_ASSOC);
		
	    $area_data['stop_dates'] = $area_stop;
		$area_data['area_refer'] = json_decode($area_data['refer_json'],true);
		$area_data['area_block'] = $area_block;
		$area_data['area_forms'] = json_decode($area_data['form_json'],true);
		
		// final
		$result['action'] = true;
		$result['data']   =  $area_data;
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Area Save Area Data 
	// [input] : AreaCode    :  \w\d{8};  = DB.area_main.area_code;
	// [input] : DataModify  :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	// [input] : DataBlocks  :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADArea_Save_Area_Data( $AreaCode='' , $DataModify='', $DataBlocks=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataModify))),true); 
	  $data_blocks = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataBlocks))),true);
	 
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^[\w\d]{8}$/',$AreaCode)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_GET_AREA_EDIT_DATA()));
		$DB_GET->bindParam(':area_code'   , $AreaCode );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查更新欄位是否合法
		foreach($data_modify as $mf => $mv){
		  
		  if(!isset($orl_data[$mf])){
		    throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		  }
		  
		  if($mf == 'area_gates'){
			$data_modify[$mf] = preg_replace('/[；，,、:：\s]+/u',';',$mv);  
		  }
		  
		  
		  
		  
		}
		
		if($data_modify && count($data_modify)){
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array_keys($data_modify)));
		  $DB_SAVE->bindValue(':area_code' , $AreaCode);
		  foreach($data_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		}
		
		 
		// 更新區塊
		if(count($data_blocks)){
		  
		  foreach($data_blocks as $block_id => $block_meta){
            $DB_SAVE	= $this->DBLink->prepare(SQL_AdArea::UPDATE_BLOCK_DATA(array_keys($block_meta)));
		    $DB_SAVE->bindValue(':abid' , $block_id);
		    foreach($block_meta as $bf => $bv){
			  $DB_SAVE->bindValue(':'.$bf , $bv);
		    }
		    if( !$DB_SAVE->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		    }			
		  }
		}
		
		// final 
		$result['data']   = $AreaCode;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Area Create New Area 
	// [input] : DataCreate  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADArea_Newa_Area_Data($DataCreate='' ){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_newa   = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataCreate))),true);
	  
	  try{  
		
		// 檢查參數
		if(  !isset($data_newa['area_type']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(  !isset($data_newa['area_name']) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		 		
		$DB_NEW	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_INSERT_NEW_AREA_DATA());
		$DB_NEW->bindValue(':area_code'  	  , substr(md5(time()),0,8));
		$DB_NEW->bindValue(':area_type'  	  , $data_newa['area_type']);
		$DB_NEW->bindValue(':area_name'  	  , $data_newa['area_name']);
		$DB_NEW->bindValue(':area_descrip'    , isset($data_newa['area_descrip']) ? $data_newa['area_descrip'] :'' );
		$DB_NEW->bindValue(':area_link'       , isset($data_newa['area_link']) ? $data_newa['area_link'] :'' );
		$DB_NEW->bindValue(':area_gates'  	  , isset($data_newa['area_gates']) ? $data_newa['area_gates'] :'');
		$DB_NEW->bindValue(':area_load'  	  , isset($data_newa['area_load'])&&intval($data_newa['area_load']) ? intval($data_newa['area_load']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':accept_max_day'  , isset($data_newa['accept_max_day'])&&intval($data_newa['accept_max_day']) ? intval($data_newa['accept_max_day']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':accept_min_day'  , isset($data_newa['accept_min_day'])&&intval($data_newa['accept_min_day']) ? intval($data_newa['accept_min_day']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':revise_day'  	  , isset($data_newa['revise_day'])&&intval($data_newa['revise_day']) ? intval($data_newa['revise_day']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':cancel_day'  	  , isset($data_newa['cancel_day'])&&intval($data_newa['cancel_day']) ? intval($data_newa['cancel_day']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':filled_day'  	  , isset($data_newa['filled_day'])&&intval($data_newa['filled_day']) ? intval($data_newa['filled_day']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':wait_list'  	  , isset($data_newa['wait_list'])&&intval($data_newa['wait_list']) ? intval($data_newa['wait_list']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':auto_pass'  	  , isset($data_newa['auto_pass'])&&intval($data_newa['auto_pass']) ? intval($data_newa['auto_pass']) : NULL , PDO::PARAM_INT );
		$DB_NEW->bindValue(':time_open'  	  , isset($data_newa['time_open'])  ? date('H:i:s',strtotime(date('Y-m-d').' '.$data_newa['time_open'])) : NULL );
		$DB_NEW->bindValue(':time_close'  	  , isset($data_newa['time_close']) ? date('H:i:s',strtotime(date('Y-m-d').' '.$data_newa['time_close'])) : NULL );
		$DB_NEW->bindValue(':owner'  	      , $this->USER->PermissionNow['group_code'] );
		$DB_NEW->bindValue(':user'  	  	  , $this->USER->UserID);
		
		
		//area_code:
		//update area_main SET area_code = SUBSTRING(MD5(CONCAT(ano,'#',area_name)),1,8) WHERE 1;
		
		if( !$DB_NEW->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');
		}
		
		$new_data_no  = $this->DBLink->lastInsertId('area_main');
		
		$DB_reNEW	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_CODE());
		$DB_reNEW->execute(array('ano'=>$new_data_no));
		
		// final 
		$result['data']   = substr(md5($new_data_no.'#'.$data_newa['area_name']),0,8);
		$result['action'] = true;
    	
	  } catch (Exception $e) {
		$result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Area Delete Area Data 
	// [input] : DataCode  :  \w\d+;
	public function ADArea_Del_Area_Data($DataCode=0){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^[\w\d]{8}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// _keep => 0
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array('_keep'))));
		$DB_SAVE->bindParam(':area_code'      , $DataCode );
		$DB_SAVE->bindValue(':_keep' , 0 );
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $DataCode;
		$result['action'] = true;
		sleep(1);
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Area Switch Area Data 
	// [input] : DataCode  :  \w\d+;
	// [input] : Switch     => 0/1
	public function ADArea_Switch_Area_Open($DataCode=0,$Switch){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^[\w\d]{8}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$display = intval($Switch) ? 1 : 0;
		
		// _keep => 0
		$DB_SAVE	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array('_open'))));
		$DB_SAVE->bindParam(':area_code'      , $DataCode );
		$DB_SAVE->bindValue(':_open'          , $display );
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $DataCode;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	/*== [ Module - STOP DATE ] ==*/
	
	//-- Admin Area Save Stop Date record
	// [input] : AreaCode  :  \w\d{8};  = DB.area_main.area_code;
	// [input] : StopData  :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADArea_Stop_Date_Save( $AreaCode='' , $StopData=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $data_modify = json_decode(base64_decode(rawurldecode($StopData)),true);
	 
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^[\w\d]{8}$/',$AreaCode)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查參數結構  
	    if(count($data_modify) != 6 ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		$orl_data = NULL;
		if(intval($data_modify['no'])){
		  // update stop date	
		  // 取得資料
		  $DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::GET_AREA_TARGET_STOP_DATE()));
		  $DB_GET->bindParam(':asno'   , $data_modify['no'] , PDO::PARAM_INT);	
		  if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		  }
		}else{
		  // insert stop date	
		  // 取得資料
		  $DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_CHECK_AREA_KEEP()));
		  $DB_GET->bindParam(':area_code',$AreaCode);	
		  if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		    throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		  }	  
		}
		
		// 檢查更新欄位是否合法
		$date_start = isset($data_modify['date_start']) ? strtotime($data_modify['date_start']) : false;
		$date_end   = isset($data_modify['date_end'])   ? strtotime($data_modify['date_end']) : false;
		
		if( !$date_start ||   
		    !$date_end  ||      
			$date_start > $date_end   ||
            $date_end < strtotime('now') )
		{
           throw new Exception('_ADMIN_AREA_STOP_DATE_FAIL');
		}
		
		// 執行更新
		$DB_SAVE = $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_STOP_DATE());
		$DB_SAVE->bindValue(':asno'       ,  intval($data_modify['no']) ? intval($data_modify['no']) : NULL , PDO::PARAM_INT);
		$DB_SAVE->bindValue(':am_id'      , $orl_data['ano']);
		$DB_SAVE->bindValue(':date_start' , date('Y-m-d',$date_start));
		$DB_SAVE->bindValue(':date_end'   , date('Y-m-d',$date_end));
		$DB_SAVE->bindValue(':reason'     , $data_modify['reason']);
		$DB_SAVE->bindValue(':effect'     , $data_modify['effect']);
		$DB_SAVE->bindValue(':active'     , intval($data_modify['_active']), PDO::PARAM_INT);
		$DB_SAVE->bindValue(':user'       , $this->USER->UserID);
		 
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		$stop_no  = intval($data_modify['no']) ? intval($data_modify['no']) : $this->DBLink->lastInsertId('area_stop');
		
		// final 
		$result['data']   = $stop_no;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Area Delete Stop Date record
	// [input] : AreaCode  :  \w\d{8};  = DB.area_main.area_code;
	// [input] : StopNo    :  \d+
	
	public function ADArea_Stop_Date_Delete( $AreaCode='' , $StopNo=0){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^[\w\d]{8}$/',$AreaCode)  || !intval($StopNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::GET_AREA_TARGET_STOP_DATE()));
		$DB_GET->bindParam(':asno'   , $StopNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}	 
		
		// 執行更新
		$DB_SAVE = $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_DELETE_AREA_STOP_DATE());
		$DB_SAVE->bindValue(':asno'       , $StopNo);
		$DB_SAVE->bindValue(':user'       , $this->USER->UserID);
		 
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $StopNo;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Area Active Stop Date Booking
	// [input] : AreaCode  :  \w\d{8};  = DB.area_main.area_code;
	// [input] : StopNo    :  \d+
	
	public function ADArea_Stop_Date_Active( $AreaCode='' , $StopNo=0){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^[\w\d]{8}$/',$AreaCode)  || !intval($StopNo)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得停止日期資料
		$stop_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::GET_AREA_TARGET_STOP_DATE()));
		$DB_GET->bindParam(':asno'   , $StopNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$stop_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 確認禁申日期是否啟用
		if(!$stop_data['_active']){
		  throw new Exception('_AREA_STOP_DATE_NOT_ACTIVE');	 
		}
		
		// 確認禁申日期目前是否過時
		if( strtotime($stop_data['date_end'].' 23:59:59') < strtotime('now') ){
		  throw new Exception('_AREA_STOP_DATE_EXPIRED');	 
		}
		
		// 篩選日期範圍內申請資料
		$DB_BOOKING	= $this->DBLink->prepare( SQL_AdArea::SEARCH_STOP_DATE_RANGE_BOOKING() );
		$DB_BOOKING->bindParam(':am_id'      , $stop_data['ano'] , PDO::PARAM_INT);	
		$DB_BOOKING->bindValue(':stop_start' , $stop_data['date_start'] );	
		$DB_BOOKING->bindValue(':stop_end'   , $stop_data['date_end']   );	
		if( !$DB_BOOKING->execute()  ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		//-- 取得區域日期範圍內申請資料
		$apply_cancel_count = 0;
		
		while( $booking = $DB_BOOKING->fetch(PDO::FETCH_ASSOC)){
		  
		  $apply_stage = $booking['_stage'];
		  $apply_final = $booking['_final'];
		  $apply_process = json_decode($booking['_progres'],true);
		
          if( ($apply_stage==5 && $apply_final=='核准進入') ||  ($apply_stage > 0 && $apply_stage <5) ){   
			
			//-- 設定申請狀態
			$apply_new_stage = 5;
			$apply_new_status='申請註銷';
			
			$apply_process['review'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'區域關閉','note'=>$stop_data['reason'],'logs'=>'');
			$apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'區域關閉註銷申請','logs'=>'');
			
			// UPD Final Status
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('_stage','_progres','_status','_final'))); 
			$DB_UPD->bindValue(':apply_code', $booking['apply_code']);
			$DB_UPD->bindValue(':_stage'    , $apply_new_stage);
			$DB_UPD->bindValue(':_progres'  , json_encode($apply_process));
			$DB_UPD->bindValue(':_status'   , '區域關閉');
			$DB_UPD->bindValue(':_final'    , '申請註銷');
			$DB_UPD->execute();
			
			
			//-- 註冊發送信件
			
			// 設定信件內容
			$mail_title_type  = '區域關閉通知';
			$mail_to_sent     = $booking['applicant_mail'];
			$mail_status_info = $stop_data['date_start'].' ~ '.$stop_data['date_start'].' '.$stop_data['reason'].'，因此註銷當區日期內所有申請，不便之處敬請見諒.';
			
			$mail_title    = _SYSTEM_HTML_TITLE." / ".$mail_title_type." / 申請編號:".$booking['apply_code'];
			
			$mail_content  = "<div >申請人 您好：</div>";
			$mail_content .= "<div >台端於 <strong>".$booking['apply_date']."</strong> 申請進入『".$stop_data['area_name']."』 </div>";
			$mail_content .= "<div >訊息通知：<span style='color:red;font-weight:bold;'>".$mail_status_info."</span></div>";	
			$mail_content .= "<div >申請狀態：".$apply_new_status."</div>";
			
			$mail_content .= "<div> <br/> </div>";
			$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各審查管理機關(構)。</div>";
			$mail_content .= "<div> </div>";
			$mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
			$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";

							  
			// 註冊信件工作
			$mail_logs = [date('Y-m-d H:i:s')=>'Regist Alert Mail From ['.$booking['apply_code'].'].' ];
			
			$DB_MAILJOB	= $this->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
			$DB_MAILJOB->bindValue(':mail_type',$mail_title_type);
			$DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
			$DB_MAILJOB->bindValue(':mail_to',$mail_to_sent);
			$DB_MAILJOB->bindValue(':mail_title',$mail_title);
			$DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
			$DB_MAILJOB->bindValue(':creator',$this->USER->UserID);
			$DB_MAILJOB->bindValue(':editor',$this->USER->UserID);
			$DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
			$DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
			$DB_MAILJOB->execute();
	        
			$apply_cancel_count++;
		  }
		
		} //endof booking fetch
		
		
		// final 
		$result['data']   = $apply_cancel_count;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Upload Area Refer Image  
	// [input] : Area Code 
	// [input] : FILES : [array] - System _FILES Array;
	public function ADArea_Add_Area_Image( $AreaCode='' , $FILES = array()){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  // Allowed extentions.
      $allowedExts = array("jpg","png");
      
      // Get filename.
      $temp = explode(".", $FILES["file"]["name"]);

      // Get extension.
      $extension = end($temp);
      
	  // Validate uploaded files.
	  // Do not use $_FILES["file"]["type"] as it can be easily forged.
	  $finfo = finfo_open(FILEINFO_MIME_TYPE);
	  $mime  = finfo_file($finfo, $FILES["file"]["tmp_name"]);
	  
	  try{
		
		// 檢查參數
		if(!preg_match('/^[\w\d]{8}$/',$AreaCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$area_code = $AreaCode;
		
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_GET_AREA_VIEW_DATA());
		$DB_GET->bindParam(':area_code'   , $area_code );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$area_refer = array();
		$area_refer = $orl_data['refer_json'] && json_decode($orl_data['refer_json'],true) ? json_decode($orl_data['refer_json'],true) : array();
		
		
		
		// 檢查檔案
		if (!in_array(strtolower($extension), $allowedExts)) {
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }	
		
        if( $FILES["file"]["error"] ){
          throw new Exception('_SYSTEM_UPLOAD_ERROR:'.$FILES["file"]["error"]);  
        }
		
		if(intval($FILES["file"]['size']/1000000) > 2){
		  throw new Exception('_APPLY_UPLOAD_FILE_TOO_LARGE');  	
		}
		
		$upload_folder = _SYSTEM_UPLD_PATH.'AREAREFER/'.$area_code.'/';
		
		if(!is_dir($upload_folder)) mkdir($upload_folder,0777);
		
		$upload_file   = time().'.'.strtolower($extension);
		$upload_save   = $upload_folder.$upload_file;
		
		// 取得上傳資料
		move_uploaded_file($FILES["file"]["tmp_name"],$upload_save);
		
		// 回填檔案
		if( !isset($area_refer['image']) ) $area_refer['image'] = array();
		list($w,$l) = getimagesize($upload_save);
	    $area_refer['image'][$upload_file] = [  
		  'addr' => $upload_save,
		  'width'=> $w,
		  'height'=> $l,
		];
	
		// 執行更新
		$DB_SAVE= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array('refer_json')));
		$DB_SAVE->bindValue(':area_code' , $area_code);
		$DB_SAVE->bindValue(':refer_json' , json_encode( $area_refer));
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		$result['data']['name']   = $upload_file;
		$result['data']['path']   = $area_code.'/'.$upload_file;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	//-- Remove Area Refer Data  
	// [input] : Area Code 
	// [input] : Refer Link 
	public function ADArea_Del_Area_Refer( $AreaCode='', $ReferType, $ReferIndex){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	   
	  try{
		
		// 檢查參數
		if(!preg_match('/^[\w\d]{8}$/',$AreaCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$area_code = $AreaCode;
		$rtype = $ReferType;
		$rindex = $ReferIndex;
		
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_GET_AREA_VIEW_DATA());
		$DB_GET->bindParam(':area_code'   , $area_code );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		
		$area_refer = array();
		$area_refer = $orl_data['refer_json'] && json_decode($orl_data['refer_json'],true) ? json_decode($orl_data['refer_json'],true) : array();
		
		if(!isset($area_refer[$rtype][$rindex])){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		if(file_exists($area_refer[$rtype][$rindex]['addr'])){
		  unlink($area_refer[$rtype][$rindex]['addr']);	
		}
		unset($area_refer[$rtype][$rindex]);
		
		// 執行更新
		$DB_SAVE= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array('refer_json')));
		$DB_SAVE->bindValue(':area_code' , $area_code);
		$DB_SAVE->bindValue(':refer_json' , json_encode( $area_refer));
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		$result['data'] = $ReferIndex ;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	
	//-- Create Area Block  
	// [input] : Area Code 
	public function ADArea_Add_Area_Block( $AreaCode=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	   
	  try{
		
		// 檢查參數
		if(!preg_match('/^[\w\d]{8}$/',$AreaCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$area_code = $AreaCode;
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_GET_AREA_VIEW_DATA());
		$DB_GET->bindParam(':area_code'   , $area_code );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 註冊子區域
		$DB_REG	= $this->DBLink->prepare(SQL_AdArea::NEW_AREA_BLOCK());
		$DB_REG->bindParam(':am_id'   , $orl_data['ano'] );	
		$DB_REG->bindParam(':area_load'   , $orl_data['area_load'] );	
		$DB_REG->bindParam(':accept_max_day'   , $orl_data['accept_max_day'] );	
		$DB_REG->bindParam(':accept_min_day'   , $orl_data['accept_min_day'] );	
		$DB_REG->bindParam(':wait_list'   , $orl_data['wait_list'] );	
		$DB_REG->bindParam(':editor'  , $this->USER->UserID );	
		if( !$DB_REG->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
        $new_block_no  = $this->DBLink->lastInsertId('area_block');
		$new_block_id  = 'A'.str_pad($orl_data['ano'],2,'0',STR_PAD_LEFT).'-'.str_pad($new_block_no,3,'0',STR_PAD_LEFT);
		
		// 更新子區域ID
		$DB_UPD	= $this->DBLink->prepare(SQL_AdArea::RENEW_AREA_BLOCK());
		$DB_UPD->bindParam(':abid'   ,  $new_block_id );	
		$DB_UPD->bindParam(':abno'   ,  $new_block_no );	
		$DB_UPD->execute();
		if( !$new_block_no || !$DB_UPD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		$result['data']   = $new_block_id ;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	//-- Delete Area Block  
	// [input] : Block Code 
	public function ADArea_Del_Area_Block( $BlockCode=''){
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		// 檢查參數
		if(!preg_match('/^A\d{2}\-\d\d\d$/',$BlockCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$block_id = $BlockCode;
		
		// 刪除子區域
		$DB_DEL	= $this->DBLink->prepare(SQL_AdArea::DEL_AREA_BLOCK());
		$DB_DEL->bindParam(':ab_id'   , $block_id );	
		if( !$DB_DEL->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		$result['data']   = $block_id ;
		$result['action'] = true;
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	/*== [ Module - FORM CONFIG ] ==*/
	
	
	//-- Admin Area Save Form config 
	// [input] : AreaCode    :  \w\d{8};  = DB.area_main.area_code;
	// [input] : FormConfig  :  urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADArea_Save_Area_Form_Config( $AreaCode='' , $FormConfig='' ){
	  
	  $result_key = parent::Initial_Result('conf');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $form_config = json_decode(base64_decode(str_replace('*','/',rawurldecode($FormConfig))),true); 
	  
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^[\w\d]{8}$/',$AreaCode)  || !is_array($form_config)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$orl_data = NULL;
		$DB_GET	= $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArea::ADMIN_AREA_GET_AREA_EDIT_DATA()));
		$DB_GET->bindParam(':area_code'   , $AreaCode );	
		if( !$DB_GET->execute() || !$orl_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 解設定
		$area_apply_form = array();
		if($orl_data['form_json']){
		  $area_apply_form = json_decode($orl_data['form_json'],true);
		}
		
		// 覆寫設定
		foreach($form_config as $field_id => $field_config){
          $area_apply_form[$field_id] = $field_config;
		}
		
		
		$DB_SAVE	= $this->DBLink->prepare(SQL_AdArea::ADMIN_AREA_UPDATE_AREA_DATA(array('form_json')));
		$DB_SAVE->bindValue(':area_code' , $AreaCode);
		$DB_SAVE->bindValue(':form_json' , json_encode($area_apply_form));
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		
		// final 
		$result['data']   = $AreaCode;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
  }
?>