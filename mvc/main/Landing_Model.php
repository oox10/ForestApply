<?php

  class Landing_Model extends Admin_Model{
    
    
    /*[ System Function Set ]*/ 
	
	
	//-- Get Client Page Post List
	// [input] : NULL / group code 
	public function Access_Get_Client_Post_List( ){
	  $result_key = parent::Initial_Result('post');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
	  
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::INDEX_GET_POST_LIST());	
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
        $result['action'] = true;		
		
		$result['data']   = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	//-- Get Client System Page Contents // 頁面內容設定
	// [input] : NULL 
	public function Access_Get_System_Page_Content(){
	  $result_key = parent::Initial_Result('page');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::INDEX_GET_PAGE_CONTENT());
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$pages = [];
        while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $pages[$tmp['page_title']] = htmlspecialchars_decode($tmp['page_content'],ENT_QUOTES);
		}
		$result['data']   = $pages;	
		$result['action'] = true;		
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	
	
	
	
	//-- Get Client Page Area List
	// [input] : NULL 
	public function Access_Get_Active_Area_List($GroupCode=''){
	  $result_key = parent::Initial_Result('area');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		
		// 取得區域聯繫人
		$contect = array();
		
		// 查詢資料庫
		
		if(!$GroupCode){
		  $DB_OBJ = $this->DBLink->prepare(SQL_Client::INDEX_CONTECT_ORGAN());	
		}else{
		  $DB_OBJ = $this->DBLink->prepare(SQL_Client::GROUP_CONTECT_ORGAN());	
          $DB_OBJ->bindValue(':ug_code',strtolower($GroupCode));		  
		}
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
        while( $tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  $contect[$tmp['ug_code']] = array();
		  $DB_USER = $this->DBLink->prepare(SQL_Client::GET_ORGAN_CONTECT_INFO());
		  $DB_USER->bindValue(':gid',$tmp['ug_code']);
		  $DB_USER->execute();
		  $contect[$tmp['ug_code']]['organ'] = $tmp['ug_name'];
		  $contect[$tmp['ug_code']]['areas'] = array();
		  $contect[$tmp['ug_code']]['contect'] = $DB_USER->fetchAll(PDO::FETCH_ASSOC);
		}
		
		// 查詢資料庫
		if(!$GroupCode){
		  $DB_OBJ = $this->DBLink->prepare(SQL_Client::INDEX_GET_AREA_LIST());
		}else{
		  $DB_OBJ = $this->DBLink->prepare(SQL_Client::INDEX_GET_GROUP_AREA());	
		  $DB_OBJ->bindValue(':owner',strtolower($GroupCode));	
		}
		
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		$area_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		$area_type = array();
		
		foreach($area_list as $area){
		  $area_type[] = $area['area_type'];
		  if(!isset($contect[$area['owner']])) $contect[$area['owner']] = array();
		  $contect[$area['owner']]['areas'][] = $area['area_name'];
		}
		
		$result['data']['alone']   = $GroupCode ? $GroupCode : false;
		$result['data']['type']    = array_unique($area_type);
		$result['data']['list']    = $area_list ;
		$result['data']['contect'] = $contect;
		
		$result['action'] = true;		
	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	
	//-- Get Client Page Area List
	// [input] : AreaCode 
	public function Access_Get_Select_Area_Info($AreaCode){
	  $result_key = parent::Initial_Result('info');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
	    
		
		// 確認區域代號
		if(!preg_match('/^[\w\d]{8}$/',$AreaCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');  	
		}
		
		// 查詢基本資料
		$area = false;
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_TARGET_AREA_DATA());
		if(!$DB_OBJ->execute(array('area_code'=>$AreaCode))  || !$area = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$area['refer'] = json_decode($area['refer_json'],true);
		$area['forms'] = array();
		$area_form_config = json_decode($area['form_json'],true);
		foreach($area_form_config as $field_name => $field_config){
		  if($field_name=='application_reason'){
			$area['forms']['application_reason'] = $field_config; 
		  }else{
			if(!isset($area['forms'][$field_config['config']['class']])){
			  $area['forms'][$field_config['config']['class']] = array();	
			}
			$area['forms'][$field_config['config']['class']][$field_name] = $field_config['config'];
		  }
		}
		
		// 查詢子區域
		$area['sub_block'] = array();	
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_TARGET_AREA_BLOCK());
		if(!$DB_OBJ->execute(array('amid'=>$area['ano']))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($block = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $area['sub_block'][$block['ab_id']] = [
		    'name' => $block['block_name'],
		    'desc' => $block['block_descrip'],
            'gate' => array_filter(explode(';',$block['block_gates'])),
		    'load' => $block['area_load']
		  ];
		}
		
		// 查詢停用時間
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_TARGET_AREA_STOP());
		if(!$DB_OBJ->execute(array('date_now'=>date('Y-m-d'),'amid'=>$area['ano']))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		$stops = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		
		// 查詢可申請範圍內已申請人數
		$applied = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_TARGET_AREA_APPLIED());
		$DB_OBJ->bindValue(':apply_d_start',date('Y-m-d',strtotime('+'.$area['accept_min_day'].' day')));
		$DB_OBJ->bindValue(':apply_d_end',date('Y-m-d',strtotime('+'.$area['accept_max_day'].' day')));
		$DB_OBJ->bindValue(':amid',$area['ano']);
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $ads = $tmp['date_enter'];
		  $ade = $tmp['date_exit'];
		  do{
			$applied_index = preg_replace('/\//','-',$ads);	
		    if(!isset($applied[$applied_index])) $applied[$applied_index] = 0;   	
		    $applied[$applied_index] += $tmp['member_count'];
            $ads = date('Ymd',strtotime('+1 day',strtotime($ads)));
		  }while(strtotime($ads.' 00:00:00') <= strtotime($ade.' 23:59:59'));
		}
		
		// search area concat
		$area['master_group']   = '';
		$area['master_contect'] = '';
		$area['master_email']   = '';
		$DB_ADM = $this->DBLink->prepare(SQL_Client::GET_AREA_OWNER_GROUP_AND_CONCATER());
		$DB_ADM->bindValue(':owner',$area['owner']);
		if(!$DB_ADM->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		while($tmp = $DB_ADM->fetch(PDO::FETCH_ASSOC)){
		  $area['master_group'] = $tmp['ug_name'];
		  $role_set = json_decode($tmp['role_conf'],true);
		  if(intval($role_set['R01'])){
			$area['master_contect'] = $tmp['user_tel'];
		    $area['master_email']   = $tmp['user_mail'];  
		  }
		}
		
		$result['data']['area']    = $area ;
		$result['data']['stops']   = $stops ;
		$result['data']['applied'] = $applied ;
		$result['data']['start']   = date('Y-m-d',strtotime('+'.$area['accept_min_day'].' day'));
		
		
		$result['action'] = true;		
	    
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	
	
	//-- Get Client Page Area Date Config
	// [input] : AreaCode
	// [input] : MonthArray
	public function Access_Get_Select_Area_Date($AreaCode,$MonthStart,$MonthLength=2){
	  $result_key = parent::Initial_Result('date');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
	    
		// 檢查登入參數
		if( !preg_match('/^[\w\d]{8}$/',$AreaCode)  ){
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }
		
		// 取得區域基本資料
		if(!isset($this->ModelResult['info'])){
		  $result = self::Access_Get_Select_Area_Info($AreaCode);
		  $areainfo = $result['data'];
		}else{
		  $areainfo['area'] = $this->ModelResult['info']['data']['area'];
          $areainfo['stops']= $this->ModelResult['info']['data']['stops']; 		  
		  $areainfo['applied'] = $this->ModelResult['info']['data']['applied'];
		}
		
		// 處理日期範圍參數  基準月份不可大於或小於一年
		$month_start = ($MonthStart=='_now') ? date('Y-m',strtotime('+'.$areainfo['area']['accept_min_day'].' day')) : $MonthStart;	
		if( !strtotime($month_start.'-01') || strtotime($month_start.'-01')  <  strtotime('-12 month') || strtotime($month_start.'-01')  >  strtotime('+3 month') ){
		  throw new Exception('_APPLY_MONTH_SCHEDULE_OVER_RANGE');
		}
		
		$point_month_time = strtotime($month_start.'-01');
		$month_start = date('Y-m', $point_month_time );   	
		$month_array = array($month_start);
		
		// 取得資料月份  數量不可超過四個 
		while(count($month_array) < abs($MonthLength) && abs($MonthLength)<4 ){
		  $point_month_time = ($MonthLength > 0 ) ? strtotime('+1 month',$point_month_time) : strtotime('-1 month',$point_month_time);
		  $month_array[] = date('Y-m',$point_month_time);
		}
		sort($month_array);		
        
		// 填入月曆
		$todaytime = strtotime(date('Y-m-d 00:00:01'));
		$min_apply_time = strtotime('+'.$areainfo['area']['accept_min_day'].' day',$todaytime);
		$max_apply_time = strtotime('+'.$areainfo['area']['accept_max_day'].' day',$todaytime);
		$apply_calendar = array();  //月曆陣列
		foreach($month_array as $ynm_string){
      
		  $apply_calendar[$ynm_string] = array();
		  
          $m_start_time = strtotime($ynm_string.'-01');
		  
		  // count sloct 
          $empty_slot = date('w',$m_start_time);  //before first day  // 取得開始日期前的空白區
		  $dates_slot = date('t',$m_start_time);  //month slot // 本月的所有天數 
		 
		  $calendar_slots = $empty_slot + $dates_slot;
		  $month_date     = 0;
		  for( $d=1 ; $d<=$calendar_slots ; $d++ ){
			$dat_sloct = [
			  'type'=>'',
			  'quota'=>0,
			  'booked'=>0,
			  'info'=>'',
			  'date'=>''
			];
			
			if( $d <= $empty_slot){
			  $dat_sloct['type'] = 'empty';	
			  $apply_calendar[$ynm_string][] = $dat_sloct;
			  continue;
			}
			
			$month_date++;
			$this_date_string = $ynm_string.'-'.str_pad($month_date,2,'0',STR_PAD_LEFT);
			$this_date_time = strtotime($this_date_string.' 12:01:01');
			$dat_sloct['date'] = $this_date_string;
			
			foreach($areainfo['stops'] as $stop){
			  $stop_s = strtotime($stop['date_start'].' 00:00:00');
			  $stop_e = strtotime($stop['date_end'].' 23:59:59');
			  if($this_date_time >= $stop_s && $this_date_time <= $stop_e){
				$dat_sloct['type']='stop';
				$dat_sloct['info']=$stop['reason'];
			    break;
			  }
			}
			
			if($dat_sloct['type']=='stop'){
			  $apply_calendar[$ynm_string][] = $dat_sloct;
			  continue;
			}
			
			// 確認日期是否超過申請日
		    if( $this_date_time > $max_apply_time ){  
			  $dat_sloct['type']='over';
			  $dat_sloct['info']='-';	
              $apply_calendar[$ynm_string][] = $dat_sloct;
			  continue;	
			}
			
			// 確認申請時間是否已超出負荷
			if(isset($areainfo['applied'][$this_date_string])){
			  $accept = $areainfo['area']['area_load'] - $areainfo['applied'][$this_date_string];
			  $dat_sloct['booked'] = $areainfo['applied'][$this_date_string];
			  $dat_sloct['quota']  = ( $accept <= 0 ) ? 0 : $accept;
			}else{
			  $dat_sloct['quota']=$areainfo['area']['area_load'];	
			}
			$dat_sloct['type']= ($this_date_time <= $min_apply_time) ? 'over' : 'apply';
			$dat_sloct['info']= '';
			$apply_calendar[$ynm_string][] = $dat_sloct;
		  
		  }
		}
		
		$result['data']   = $apply_calendar;
		$result['action'] = true;		
	    
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  
	  
	  return $result;   
	}
	
	
	//-- Check User Submit Data To Search Application // 檢驗使用者查詢資料
	// [input] : SubmitString   = (String) js encode string 
	
	public function Check_User_Applied_Data($SubmitString=''){
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $user_data = json_decode(base64_decode(str_replace('*','/',rawurldecode($SubmitString))),true); 
	  
	  try{    
		
		// 檢查申請序號
		if( !isset($user_data['code']) ||  !preg_match('/^[\w\d]{8,10}$/',$user_data['code'])  ){
	      throw new Exception('_CLIENT_ERROR_APPLICATION_CODE_FAIL');
	    }
		
		// check email
		if(!isset($user_data['mail']) || !filter_var($user_data['mail'], FILTER_VALIDATE_EMAIL)){ 
		  throw new Exception('_CLIENT_ERROR_APPLICANT_MAIL_FAIL');
		}
		
		$applied_code = strtoupper($user_data['code']);
		$applier_mail = trim($user_data['mail']);
		
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::SEARCH_USER_APPLICATION());
		$DB_OBJ->bindValue(':apply_code',$applied_code);
		$DB_OBJ->bindValue(':applicant_mail',$applier_mail);
		if(!$DB_OBJ->execute() || !$booking = $DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');  
		}
        $result['code']   = $applied_code;
		$result['data']   = ['CODE'=>$applied_code,'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  
	  
	  
	  return $result;   
	}
	
	
	//-- Check User Submit Link(from mail) To Search Application // 檢驗使用者查詢資料
	// [input] : ApplyCode   = (String) apply code
	// [input] : LinkAccessKey   = (String) apply access key  sha1(n+)
	public function Check_User_Applied_Link($ApplyCode='',$LinkAccessKey=''){
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		
		// 檢查申請序號
		if( !strlen($ApplyCode) ||  !preg_match('/^[\w\d]{8,10}$/',$ApplyCode)  ){
	      throw new Exception('_CLIENT_ERROR_APPLICATION_CODE_FAIL');
	    }
		
		// check access key
		if( strlen($LinkAccessKey)!=40 ){ 
		  throw new Exception('_CLIENT_ERROR_APPLY_ACCESS_LINK_FAIL');
		}
		
		$applied_code = strtoupper($ApplyCode);
		
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA());
		$DB_OBJ->bindValue(':apply_code',$applied_code);
		if(!$DB_OBJ->execute() || !$booking = $DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');  
		}
        
		$license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);

		if($license_access_key != $LinkAccessKey){
		  throw new Exception('_CLIENT_ERROR_APPLY_ACCESS_LINK_FAIL');	
		}
		
		$result['data']   = ['CODE'=>$applied_code,'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  
	  return $result;   
	}
	
	
	
	//-- Get Client Page Area Date Config For dateRangePicker
	// [input] : AreaCode
	public function Access_Get_Area_DatePicker_Config($AreaCode){
	  $result_key = parent::Initial_Result('picker');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
	    
		// 檢查登入參數
		if( !preg_match('/^[\w\d]{8}$/',$AreaCode)  ){
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }
		
		// 處理日期範圍參數
		if(!isset($this->ModelResult['info'])){
		  $result = self::Access_Get_Select_Area_Info($AreaCode);
		  $areainfo = $result['data'];
		}else{
		  $areainfo['area'] = $this->ModelResult['info']['data']['area'];
          $areainfo['stops']= $this->ModelResult['info']['data']['stops']; 		  
		  $areainfo['applied'] = $this->ModelResult['info']['data']['applied'];
		}
		
		$todaytime = strtotime(date('Y-m-d 00:00:01'));
		
		$min_apply_time = strtotime('+'.$areainfo['area']['accept_min_day'].' day',$todaytime);
		$max_apply_time = strtotime('+'.$areainfo['area']['accept_max_day'].' day',$todaytime);
		
		$picker_first_time = strtotime(date('Y-m-01 12:00:01 ',$min_apply_time));
		$picker_last_time  = strtotime(date('Y-m-t 23:59:59' ,$max_apply_time));
		
		$picker_config = array();
		
		$apply_date_time  = $picker_first_time;
		
		do{
          
		  $apply_date_index = date('Y-m-d',$apply_date_time);   		  
          
		  $d_config = [
			'type'		=>'',
			'apply'		=>1,  // 是否可申請
			'quota'		=>$areainfo['area']['area_load'],
			'booked'	=>0,  // 已申請人次
			'info' 		=>'',
			'date' 		=>$apply_date_index
	      ];
		  
		  // 確認日期是否超過申請日
		  if($apply_date_time <= $min_apply_time || $apply_date_time > $max_apply_time ){
			$d_config['type']  = 'over';
			$d_config['apply'] = 0;
			$d_config['info']  = '';		
		  }
		  
		  // 確認日期是否為停申日期
		  foreach($areainfo['stops'] as $stop){
			$stop_s = strtotime($stop['date_start'].' 00:00:00');
			$stop_e = strtotime($stop['date_end'].' 23:59:59');
			if($apply_date_time >= $stop_s && $apply_date_time <= $stop_e){
			  $d_config['type'] = 'stop';
			  $d_config['apply']= 0;
			  $d_config['info'] = $stop['reason'];
			  break;
			}
	      }
			
          // 確認已申請人數
	      if(isset($areainfo['applied'][$apply_date_index])){
			$d_config['booked']=$areainfo['applied'][$apply_date_index];
		  }
		  $picker_config[$apply_date_index] = $d_config;
		  
		  $apply_date_time = strtotime('+1 day',$apply_date_time);
		  
		}while( $apply_date_time <= $picker_last_time );
		
		$result['data']   = $picker_config;
		$result['action'] = true;		
	    
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;   
	}
	
	
	//-- Applica Record // 使用者資料查詢
	public function Applicant_Record_Search($UserData=array()){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		$user_data = json_decode(base64_decode(str_replace('*','/',rawurldecode($UserData))),true); 
		
		// check User Name
		if(!isset($user_data['applicant_name']) ){ 
		  throw new Exception('_APPLY_APPLICANT_NAME_FAIL');
		}
		
		// check email
		if(!isset($user_data['applicant_mail']) || !filter_var($user_data['applicant_mail'], FILTER_VALIDATE_EMAIL)){ 
		  throw new Exception('_APPLY_APPLICANT_EMAIL_FAIL');
		}
		
		$DB_GET = $this->DBLink->prepare(SQL_Client::SEARCH_APPLY_RECORD()); 
		$DB_GET->bindValue(':applicant_name'	, $user_data['applicant_name']);
		$DB_GET->bindValue(':applicant_mail'	, $user_data['applicant_mail']);
		$DB_GET->bindValue(':applicant_id'		, $user_data['applicant_userid']);
		if( !$DB_GET->execute() ){
		  throw new Exception('_APPLY_SEARCH_FAIL');  
		}
		
		$records = array();
		while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC) ){
		  
		  $record = array();
		  $record['apply_date']  = $tmp['apply_date'];
		  $record['area_name']   = $tmp['area_name'];	
		  $record['applicant']   = json_decode($tmp['applicant_info'],true);	
		  $record['application'] = json_decode($tmp['apply_form'],true);
		  $records[] = $record;
		
		}
		
		$result['data']   = array_slice($records,0,7);
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  
	  return $result;  
	}
	
	
	//-- User Apply Record Sign Up  // 新申請註冊
	// [input] : Applicant STR base64M hash string
	// [input] : ApplyCode code
	public function Apply_Record_Initial($Applicant='',$ApplyCode=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
		$user_data = json_decode(base64_decode(str_replace('*','/',rawurldecode($Applicant))),true); 
		$apply_code = false;
		// check User Name
		if(!isset($user_data['applicant_name']) || strlen($user_data['applicant_name']) < 4 ){ 
		  $result['data']['applicant_name']='';
		  throw new Exception('_APPLY_APPLICANT_NAME_FAIL');
		}
		
		// check email
		if(!isset($user_data['applicant_mail']) || !filter_var($user_data['applicant_mail'], FILTER_VALIDATE_EMAIL)){ 
		  $result['data']['applicant_mail']='';
		  throw new Exception('_APPLY_APPLICANT_EMAIL_FAIL');
		}
		
		// check id 
		if(!isset($user_data['applicant_userid']) ){
		   $result['data']['applicant_userid']='';
		   throw new Exception('_APPLY_APPLICANT_ID_FAIL');
		}else if( preg_match('/^\w\d{9}$/',$user_data['applicant_userid']) && !System_Helper::check_twid($user_data['applicant_userid'])){
		   $result['data']['applicant_userid']='';
		   throw new Exception('_APPLY_APPLICANT_ID_FAIL');
		}else if(strlen($user_data['applicant_userid']) < 6){
		   $result['data']['applicant_userid']='';
		   throw new Exception('_APPLY_APPLICANT_ID_FAIL');	
		}
		
		// 檢查申請序號
		if($ApplyCode && preg_match('/^[\w\d]{8,10}$/',$ApplyCode) ){
		  // 取得申請資料
		  $booking = array();
		  $DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA());
		  if($DB_OBJ->execute(array('apply_code'=>$ApplyCode)) &&  $booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		    
		    if($booking['applicant_name'] == $user_data['applicant_name'] &&
			   $booking['applicant_mail'] == $user_data['applicant_mail'] &&
			   $booking['applicant_id'] == $user_data['applicant_userid']
			){
			  // 確認申請人為同一個人
              $apply_code = $ApplyCode;
			}	
		  }
		}
		
		// prepare new application value
        $apply_code  = $apply_code ? $apply_code : strtoupper(hash('crc32',$user_data['applicant_name'].$user_data['applicant_mail'].time()._SYSTEM_NAME_SHORT));
		
		$application = json_encode(array_filter($user_data),JSON_UNESCAPED_UNICODE );
		
		$member = array();
		$member[0] = [
		  'member_role'		=> '領隊' , 
		  'member_name'		=> $user_data['applicant_name'],
          'member_id'		=> $user_data['applicant_userid'],
          'member_birth'	=> isset($user_data['applicant_birthday']) ? $user_data['applicant_birthday'] :'' ,
          'member_sex'		=> ''	,
          'member_org'		=> isset($user_data['applicant_serviceto']) ? $user_data['applicant_serviceto'] :'' ,
          'member_addr'		=> isset($user_data['applicant_mailaddress']) ? $user_data['applicant_mailaddress'] :'' ,
          'member_tel'		=> isset($user_data['applicant_phonenumber']) ? $user_data['applicant_phonenumber'] :'' ,
          'member_cell'		=> isset($user_data['applicant_cellphone']) ? $user_data['applicant_cellphone'] :'' ,
          'member_contacter'=> '' ,
          'member_contactto'=> '' ,
		];
		
		/*
		$chief[1] = isset($applicant['applicant_name']) ? $applicant['applicant_name'] : ( isset($mbr['member_name'])&&$mbr['member_name'] ? $mbr['member_name'] : ''); 
		    $chief[2] = isset($applicant['applicant_userid']) ? $applicant['applicant_userid'] : ( isset($mbr['member_id'])&&$mbr['member_id'] ? $mbr['member_id'] : ''); 
		    $chief[3] = isset($mbr['member_birth'])&&$mbr['member_birth'] ? $mbr['member_birth'] : ( isset($applicant['applicant_birthday'])&&$applicant['applicant_birthday'] ? $applicant['applicant_birthday'] : ''); 
		    $chief[4] = isset($mbr['member_sex'])&&$mbr['member_sex'] ? $mbr['member_sex'] : '';
		    $chief[5] = isset($mbr['member_org'])&&$mbr['member_org'] ? $mbr['member_org'] : ( isset($applicant['applicant_serviceto'])&&$applicant['applicant_serviceto'] ? $applicant['applicant_serviceto'] : '');
		    $chief[6] = isset($mbr['member_addr'])&&$mbr['member_addr'] ? $mbr['member_addr'] : ( isset($applicant['applicant_mailaddress'])&&$applicant['applicant_mailaddress'] ? $applicant['applicant_mailaddress'] : '');
		    $chief[7] = isset($mbr['member_tel'])&&$mbr['member_tel'] ? $mbr['member_tel'] : ( isset($applicant['applicant_phonenumber'])&&$applicant['applicant_phonenumber'] ? $applicant['applicant_phonenumber'] : '');;
		    $chief[8] = isset($mbr['member_cell'])&&$mbr['member_cell'] ? $mbr['member_cell'] : ( isset($applicant['applicant_cellphone'])&&$applicant['applicant_cellphone'] ? $applicant['applicant_cellphone'] : '');;
		    $chief[9] = isset($mbr['member_contacter'])&&$mbr['member_contacter'] ? $mbr['member_contacter'] : '';
		    $chief[10] = isset($mbr['member_contactto'])&&$mbr['member_contactto'] ? $mbr['member_contactto'] : '';
		*/
		
		$DB_NEW = $this->DBLink->prepare(SQL_Client::INITIAL_APPLY_ACCOUNT()); 
	    $DB_NEW->bindValue(':am_id','');
		$DB_NEW->bindValue(':apply_code'	, $apply_code);
		$DB_NEW->bindValue(':apply_date'	, date('Y-m-d'));
		$DB_NEW->bindValue(':applicant_name', $user_data['applicant_name'] );
		$DB_NEW->bindValue(':applicant_mail', $user_data['applicant_mail'] );
		$DB_NEW->bindValue(':applicant_id'	, isset($user_data['applicant_userid'] ) ? $user_data['applicant_userid'] : '');
		$DB_NEW->bindValue(':applicant_info', $application );
		$DB_NEW->bindValue(':member_list'	, json_encode($member,JSON_UNESCAPED_UNICODE ) );
		
		if( !$DB_NEW->execute() ){
		  throw new Exception('_APPLY_INITIAL_FAIL');  
		}

		// 帳號資料夾
        if(!is_dir(_SYSTEM_CLIENT_PATH.$apply_code)){
		  mkdir(_SYSTEM_CLIENT_PATH.$apply_code, 0777, true);	  
	    }
        
		$result['session']['APPLYTOKEN'] = ['CODE'=>$apply_code,'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
		$result['session']['APPLICANT']  = $user_data;
		$result['data']   = $apply_code;
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  
	  return $result;  
	}
	
	
	
	
	//-- Upload Apply Attachment Files  
	// [input] : Apply Code 
	// [input] : FILES : [array] - System _FILES Array;
	// [input] : ApplyToken : [array] - array('CODE'=> 'KEY'=> );
	
	public function Apply_Upload_Attachment( $ApplyCode='' , $FILES = array() ,$ApplyToken=array()){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  
	  
	  // Allowed file type.
	  $allowedMime = array('application/pdf','image/png','image/jpeg');
      
	  
      // Get filename.
      $temp = explode(".", $FILES["file"]["name"]);

      // Get extension.
      $extension = strtolower(end($temp));
      
	  // Validate uploaded files.
	  // Do not use $_FILES["file"]["type"] as it can be easily forged.
	  $finfo = finfo_open(FILEINFO_MIME_TYPE);
	  $mime  = finfo_file($finfo, $FILES["file"]["tmp_name"]);
	  
	  try{
		// 檢查參數
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$apply_code = $ApplyCode;
		
		// 確認申請資料合法(本人資料)
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCode){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_CHECK_FAIL');		
		}
		 
		// 檢查檔案
		if (!in_array(strtolower($mime), $allowedMime)) {
	      throw new Exception('_APPLY_UPLOAD_FILE_NOT_ALLOW');
	    }	
		
        if( $FILES["file"]["error"] ){
          throw new Exception('_SYSTEM_UPLOAD_ERROR:'.$FILES["file"]["error"]);  
        }
		
		if(intval($FILES["file"]['size']/1000000) > 30){
		  throw new Exception('_APPLY_UPLOAD_FILE_TOO_LARGE');  	
		}
		
		// 歸檔
		$upload_folder = _SYSTEM_CLIENT_PATH.$apply_code.'/';
		$upload_file   = strtoupper(hash('crc32',time())).'.'.$extension;
		$upload_save   = $upload_folder.$upload_file ;
		
		// 取得上傳資料
		move_uploaded_file($FILES["file"]["tmp_name"],$upload_save);
		$result['data']   = array('time'=>date('Y-m-d H:i:s'),'code'=>$upload_file,'file'=>$temp[0]);
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	
	//-- Client Apply Input Apply Forms 
	// [input] : ApplyCode    :  \w\d{8};
	// [input] : Application  :  base64M string: \d+;
	// [input] : ApplyToken   :  array('CODE'=> apply_code , KEY:SYSSHOT:time()string );
	
	public function Apply_Input_ApplyForm($ApplyCode,$Application=array(),$ApplyToken=array()){
	  
	  $result_key = parent::Initial_Result('submit');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		$apply_data = json_decode(base64_decode(str_replace('*','/',rawurldecode($Application))),true); 
		$apply_data = array_filter($apply_data);
		
		// sample
		// 1. $apply_data['area']  申請區域資料       array(code:string , inter:array , gate:array(entr entr_time exit exit_time) )
		// 2. $apply_data['reason'] 申請項目或理由    array( 0:array('item','attach'), ...     )
		// 3. $apply_data['dates'] 申請日期           array( 0:array( 0:start_date 1:?:end_date ) )
		// 4. $apply_data['fields'] 其他申請欄位      array( field_id => array(field=>name , value=>content ) )
		// 4. $apply_data['attach'] 附件              array(  )
		
		// 檢查申請序號
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 確認申請資料合法(本人資料)
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCode){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_CHECK_FAIL');		
		}
		
		// 確認申請SESSION未過期
		if(!isset($ApplyToken['KEY']) || (strtotime('now') - intval(str_replace(_SYSTEM_NAME_SHORT.':','',$ApplyToken['KEY']))) > 600 ){
		  throw new Exception('_SYSTEM_ERROR_SESSION_EXPIRED');		
		}
		
		// 取得申請資料
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCode))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		$applicant = json_decode($booking['applicant_info'],true);
		
		// 確認申請資料正確性  區域資訊、申請項目、申請日其
		if(!isset($apply_data['area'])) throw new Exception('_APPLY_SUBMIT_AREA_EMPTY');  	
		if(!isset($apply_data['reason']) && count($apply_data['reason']))  throw new Exception('_APPLY_SUBMIT_REASON_EMPTY'); 
		if(!isset($apply_data['dates']) )  throw new Exception('_APPLY_SUBMIT_DATES_EMPTY');
		
		
		// 查詢區域基本資料
		$area_code = isset($apply_data['area']['code']) ? $apply_data['area']['code'] : '';
		
		// 取得申請區域資訊
		$getarea = self::Access_Get_Select_Area_Info($area_code);
		if(!$getarea['action']){
		  return $getarea;
          exit(1);		  
		}
		$areainfo = $getarea['data'];
		
		// 儲存申請理由或項目
		$apply_date    = array('enter'=>'0000-00-00','exit'=>'0000-00-00');
		$submit_reason = array_map( function($reason) { return isset($reason['item']) ? $reason['item']:'null';},$apply_data['reason']);
		$apply_reason  = join(';',$submit_reason); 
		$apply_bollet  = 0;  //是否需抽籤
		$apply_attach  = 0;  //是否要附件
		$apply_crossd  = 0;  //是否可跨日
		
		
		// 檢驗申請理由是否需要抽籤
		$area_form_config = $areainfo['area']['forms'];
		$reason_conf  = isset($area_form_config['application_reason']['elements']) ? $area_form_config['application_reason']['elements'] : array();
		foreach($reason_conf as $reason_set ){
		  if(in_array($reason_set['name'],$submit_reason) && strstr($reason_set['conf'],'limit') ){
			$apply_bollet = 1;  
		  }
		  
		  if(in_array($reason_set['name'],$submit_reason) && strstr($reason_set['conf'],'attach') ){
			$apply_attach = 1;  
		  }
		  
		  if(in_array($reason_set['name'],$submit_reason) && strstr($reason_set['conf'],'crossday') ){
			$apply_crossd = 1;  
		  } 
		}
		
		//查驗附件
		if($apply_attach && !count($apply_data['attach'])){
		  throw new Exception('_APPLY_SUBMIT_ATTACHMENT_EMPTY');     	
		}
		
		// 檢查申請日期合法
		// -1 built accept date slot
		$todaytime = strtotime(date('Y-m-d 00:00:01'));
		$min_apply_time = strtotime('+'.$areainfo['area']['accept_min_day'].' day',$todaytime);
		$max_apply_time = strtotime('+'.($areainfo['area']['accept_max_day']+1).' day',$todaytime);
		
		foreach($apply_data['dates'] as $s=> $apply_dates){
          if( !isset($apply_dates[0]) || !strtotime($apply_dates[0]) ){
			throw new Exception('_APPLY_SUBMIT_DATES_FAILS');    
		  }
		  
		  // 檢查申請日期是否超過申請範圍
		  $inter_date_time = strtotime($apply_dates[0].' 12:00:00');
		  if( $min_apply_time >= $inter_date_time || $inter_date_time >= $max_apply_time ){
			throw new Exception('_APPLY_SUBMIT_DATES_OVERFLOW');  
		  }
		  
		  // 檢查申請日期是否在停止日期內
		  foreach($areainfo['stops'] as $stop){
			$stop_s = strtotime($stop['date_start'].' 00:00:00');
			$stop_e = strtotime($stop['date_end'].' 23:59:59');
			if($inter_date_time >= $stop_s && $inter_date_time <= $stop_e){
			  throw new Exception('_APPLY_SUBMIT_DATES_IS_STOP');
			  break;
			}
		  }
		  
		  $apply_date['enter'] = $apply_dates[0];
		  $apply_date['exit']  = (isset($apply_dates[1])&&strtotime($apply_dates[1])) ? $apply_dates[1] : $apply_date['enter'];
		  
		  if(!$apply_crossd && $apply_date['enter'] != $apply_date['exit'] ){
			$apply_date['exit'] = $apply_date['enter'];  
			$apply_data['dates'][$s][1] = $apply_date['enter'];
		  }
		}
		
		// 檢查進入日期是否重複申請
		$DB_CKD = $this->DBLink->prepare(SQL_Client::CHECK_APPLY_DATE_IS_ALONE()); 
		$DB_CKD->bindValue(':am_id'			  , $areainfo['area']['ano']);
		$DB_CKD->bindValue(':apply_code'	  , $booking['apply_code']);
		$DB_CKD->bindValue(':applicant_name'  , $booking['applicant_name']  );
		$DB_CKD->bindValue(':applicant_mail'  , $booking['applicant_mail']  );
		$DB_CKD->bindValue(':applicant_id'	  , $booking['applicant_id']  );
		$DB_CKD->bindValue(':date_enter'      , $apply_date['enter'] );
		$DB_CKD->bindValue(':date_exit'       , $apply_date['exit'] );
		if( !$DB_CKD->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		$applied_num = $DB_CKD->fetchColumn();
		if($applied_num){
		  throw new Exception('_APPLY_TARGET_DATE_DOUBLED');		
		}
		
		// 確認是否抽籤
		$bollet_date = $apply_bollet ? date('Y-m-d',strtotime('-'.($areainfo['area']['accept_min_day']-1).' day',strtotime($apply_date['enter']))) : '0000-00-00';
		
		// 檢查目前狀態，補件狀態不可調整部分欄位
		if($booking['_stage'] > 1){
          if($booking['apply_reason'] !=$apply_reason || $booking['date_enter']!=$apply_date['enter'] ){
			throw new Exception('_APPLY_SUBMIT_STABLE_FIELD_CHANGE');  
		  }
		}
		
		// restore to db  / 存入資料庫
		$application = json_encode($apply_data,JSON_UNESCAPED_UNICODE);
		
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_FORM()); 
	    $DB_UPD->bindValue(':apply_code', $ApplyCode);
		$DB_UPD->bindValue(':areaid'    , $areainfo['area']['ano']);
		$DB_UPD->bindValue(':reason'    , $apply_reason );
		$DB_UPD->bindValue(':date_enter', $apply_date['enter'] );
		$DB_UPD->bindValue(':date_exit' , $apply_date['exit'] );
		$DB_UPD->bindValue(':application',$application);
		$DB_UPD->bindValue(':ballot'	,$apply_bollet);
		$DB_UPD->bindValue(':ballot_date',$bollet_date);
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		// 更新與紀錄狀態
		$apply_process = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		$apply_process['client'][0][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'更新資料','note'=>'','logs'=>'');
		$apply_process['review'][2]   = [];
		
		if( $apply_bollet ){
		  
		  $apply_process['client'][1]   = [array('time'=>date('Y-m-d H:i:s'),'status'=>'收件待審','note'=>'申請項目需抽籤','logs'=>'')];
		  $apply_process['review'][2][] = array('time'=>$bollet_date,'status'=>'系統抽籤','note'=>'','logs'=>'');	
		
		  $DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STAGE()); 
	      $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		  $DB_UPD->bindValue(':stage'     , 1 );
		  
		  if( !$DB_UPD->execute() ){
		    throw new Exception('_APPLY_SUBMIT_FAIL');  
		  }
		}
		
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STATUS()); 
	    $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		$DB_UPD->bindValue(':status'    ,$booking['_status']);
		$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		$result['data'] = $ApplyCode;
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  
	  return $result;  
	}
	
	
	
	
	//-- Get Member List Data By Apply Code
	// [input] : ApplyCode    :  \w\d{8};
	// [input] : ApplyToken   :  check user priority  from session array( 'CODE'=>applycode,'KEY'=>name_shot:strtotime )
	public function Apply_Get_Member_Record($ApplyCode='', $ApplyToken=false ){
	  
	  $result_key = parent::Initial_Result('members');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 檢查參數
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查使用者是否為本人
		if( !is_array($ApplyToken) || !isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCode  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// search db
		$DB_GET = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA()); 
		$DB_GET->bindValue(':apply_code'	, $ApplyCode);
		if( !$DB_GET->execute() || !$record = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');  
		}
		
		$result['data']['applycode']  = $ApplyCode;
		$result['data']['applicant']  = json_decode($record['applicant_info'],true);
		$result['data']['memberlist'] = json_decode($record['member'],true);
		$result['action'] = true;
	    
	  }catch(Exception $e){
		//$result['message'][] = $e->getMessage();
	  }
	   
	  return $result;  
	}
	
	//-- Admin Apply Get Apply Data 
	// [input] : uano  :  \d+;
	// [input] : list  :  string: \d+,\d+,...;
	public function Apply_Built_Member_List_File($ExcelTemplate=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		if(!$ExcelTemplate || !file_exists(_SYSTEM_FILE_PATH.$ExcelTemplate)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');  	
		}
		
		$template   = _SYSTEM_FILE_PATH.$ExcelTemplate;
		$applycode  = false;
		$applicant  = array();
		$memberlist = array();
		
		if( isset($this->ModelResult['members']) && $this->ModelResult['members']['action'] ){
		  $applycode  = $this->ModelResult['members']['data']['applycode'];
		  $applicant  = $this->ModelResult['members']['data']['applicant'];
		  $memberlist = $this->ModelResult['members']['data']['memberlist'];
		}
		
		$file_info = pathinfo($template);
	    
		switch($file_info['extension']){
		  case 'ods': 		  
			$objReader = new PHPExcel_Reader_OOCalc();
		    //$objReader = PHPExcel_IOFactory::createReaderForFile($template);
		    break;
		  case 'xls': default:
		    $objReader = PHPExcel_IOFactory::createReaderForFile($template);
			break;
		}		
		
		$objPHPExcel = $objReader->load($template);
		
		
		//0角色	1姓名 2身分證號	3生日	4性別	5服務單位	6通訊地址	7聯絡電話	8聯絡手機	9緊急連絡人姓名	10緊急連絡人電話
        
		// 輸入總表單
		$objPHPExcel->setActiveSheetIndex(0); 
		$objPHPExcel->getActiveSheet()->setTitle('報名成員清單');
		
		// 處理隊員資料
		$chief = array('領隊');
		$team  = array();
		foreach($memberlist as $mbr){
          
		  // 確認是否為領隊
		  if( isset($applicant['applicant_name']) && isset($applicant['applicant_userid']) && isset($mbr['member_name']) && isset($mbr['member_id']) &&
		      ($mbr['member_name']==$applicant['applicant_name']) && 
		      ($mbr["member_id"]==$applicant['applicant_userid']) )
		  {
			
		    $chief[1] = isset($applicant['applicant_name']) ? $applicant['applicant_name'] : ( isset($mbr['member_name'])&&$mbr['member_name'] ? $mbr['member_name'] : ''); 
		    $chief[2] = isset($applicant['applicant_userid']) ? $applicant['applicant_userid'] : ( isset($mbr['member_id'])&&$mbr['member_id'] ? $mbr['member_id'] : ''); 
		    $chief[3] = isset($mbr['member_birth'])&&$mbr['member_birth'] ? $mbr['member_birth'] : ( isset($applicant['applicant_birthday'])&&$applicant['applicant_birthday'] ? $applicant['applicant_birthday'] : ''); 
		    $chief[4] = isset($mbr['member_sex'])&&$mbr['member_sex'] ? $mbr['member_sex'] : '';
		    $chief[5] = isset($mbr['member_org'])&&$mbr['member_org'] ? $mbr['member_org'] : ( isset($applicant['applicant_serviceto'])&&$applicant['applicant_serviceto'] ? $applicant['applicant_serviceto'] : '');
		    $chief[6] = isset($mbr['member_addr'])&&$mbr['member_addr'] ? $mbr['member_addr'] : ( isset($applicant['applicant_mailaddress'])&&$applicant['applicant_mailaddress'] ? $applicant['applicant_mailaddress'] : '');
		    $chief[7] = isset($mbr['member_tel'])&&$mbr['member_tel'] ? $mbr['member_tel'] : ( isset($applicant['applicant_phonenumber'])&&$applicant['applicant_phonenumber'] ? $applicant['applicant_phonenumber'] : '');;
		    $chief[8] = isset($mbr['member_cell'])&&$mbr['member_cell'] ? $mbr['member_cell'] : ( isset($applicant['applicant_cellphone'])&&$applicant['applicant_cellphone'] ? $applicant['applicant_cellphone'] : '');;
		    $chief[9] = isset($mbr['member_contacter'])&&$mbr['member_contacter'] ? $mbr['member_contacter'] : '';
		    $chief[10] = isset($mbr['member_contactto'])&&$mbr['member_contactto'] ? $mbr['member_contactto'] : '';
          }else{
			$member = array();
			$member[0] = '成員';
			$member[1] = isset($mbr['member_name'])&&$mbr['member_name'] ? $mbr['member_name'] : '';
			$member[2] = isset($mbr['member_id'])&&$mbr['member_id'] ? $mbr['member_id'] : '';
			$member[3] = isset($mbr['member_birth'])&&$mbr['member_birth'] ? $mbr['member_birth'] : '';
			$member[4] = isset($mbr['member_sex'])&&$mbr['member_sex'] ? $mbr['member_sex'] : '';
			$member[5] = isset($mbr['member_org'])&&$mbr['member_org'] ? $mbr['member_org'] : '';
			$member[6] = isset($mbr['member_addr'])&&$mbr['member_addr'] ? $mbr['member_addr'] : '';
			$member[7] = isset($mbr['member_tel'])&&$mbr['member_tel'] ? $mbr['member_tel'] : '';
			$member[8] = isset($mbr['member_cell'])&&$mbr['member_cell'] ? $mbr['member_cell'] : '';
			$member[9] = isset($mbr['member_contacter'])&&$mbr['member_contacter'] ? $mbr['member_contacter'] : '';
			$member[10] = isset($mbr['member_contactto'])&&$mbr['member_contactto'] ? $mbr['member_contactto'] : '';
		    
			$team[] = $member;
		  }
		  
		}
		  /* data source sample
		  $applicant['applicant_name','applicant_userid','applicant_mail'
		            ,'applicant_birthday','applicant_serviceto','applicant_phonenumber'
		            ,'applicant_cellphone','applicant_faxnumber','applicant_mailaddress','applicant_regaddress'];
		  
		  $member["member_role","member_name","member_id","member_birth","member_sex","member_org","member_addr"
		          ,"member_tel","member_cell","member_contacter","member_contactto"]
		  */

		
		foreach($chief as $col=>$mbrdata){
		  $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, 2)->setValueExplicit($mbrdata, PHPExcel_Cell_DataType::TYPE_STRING);    
		}	
		
		foreach($team as $row=>$member){
		  foreach($member as $col=>$mbrdata){
		    $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, ($row+3))->setValueExplicit($mbrdata, PHPExcel_Cell_DataType::TYPE_STRING);    
		  }
		}	
		
		// 匯出欄位
		$export_file = _SYSTEM_FILE_PATH.'member/tmp_'.time();
		$export_file_name = 'ReservedMember';
		
		if($applycode){
		  if(!is_dir(_SYSTEM_CLIENT_PATH.$applycode)){
		    mkdir(_SYSTEM_CLIENT_PATH.$applycode, 0777, true);	  
	      }	
		  $export_file = _SYSTEM_CLIENT_PATH.$applycode.'/tmp_'.time();
		  $export_file_name = 'ReservedMember-'.$applycode.'-'.date('Ymd');
		}
		
		switch($file_info['extension']){
		  case 'ods': 		  
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'OpenDocument');
		    break;
		  case 'xls': default:
		    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			break;
		}
		
		$objWriter->save( $export_file.'.'.$file_info['extension'] ); 
		$objPHPExcel->disconnectWorksheets();
		
		// final
		$result['action'] = true;
		$this->ModelResult['members']['action'] = true; // 要把存取資料錯誤移除，以免跳轉至錯誤頁面
		$result['data']['name']     = $export_file_name.'.'.$file_info['extension'];
		$result['data']['size']     = filesize($export_file.'.'.$file_info['extension']);
    	$result['data']['location'] = $export_file.'.'.$file_info['extension'];
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
	//-- Upload Apply Member Excel  
	// [input] : Apply Code 
	// [input] : FILES : [array] - System _FILES Array;
	public function Apply_Upload_MbrFile( $ApplyCode='' , $FILES = array()){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  // Allowed extentions.
      $allowedExts = array("xls","xlsx",'ods');
      
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
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$apply_code = $ApplyCode;
		
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
		
		$upload_folder = _SYSTEM_CLIENT_PATH.$apply_code.'/';
		$upload_file   = time();
		$upload_save   = $upload_folder.$upload_file ;
		
		// 取得上傳資料
		move_uploaded_file($FILES["file"]["tmp_name"],$upload_save);
		$result['data']   = $upload_file;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	
	//-- Admin Apply Get Apply Data 
	// [input] : ApplyCode :  \w\d{8};
	// [input] : FileName  :  string: \d+;
	public function Apply_Process_MbrFile($ApplyCode=0,$FileName=''){
	  $result_key = parent::Initial_Result('process');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		
		// 檢查檔案名稱與存在
		if(!preg_match('/^\d+$/',$FileName)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		if(!file_exists(_SYSTEM_CLIENT_PATH.$ApplyCode.'/'.$FileName)){
		  throw new Exception('_APPLY_UPLOAD_FILE_NOT_EXIST');  	
		}
		
		$mbr_file = _SYSTEM_CLIENT_PATH.$ApplyCode.'/'.$FileName;
		
		$excelReader = PHPExcel_IOFactory::createReaderForFile($mbr_file);
		//$excelReader = new PHPExcel_Reader_OOCalc();
		
	    $excelReader->setReadDataOnly(true);
		$objPHPExcel = $excelReader->load($mbr_file);
		$objPHPExcel->setActiveSheetIndex(0);
		$apply_code = $objPHPExcel->getActiveSheet()->getTitle();
		$objSheet=$objPHPExcel->getActiveSheet();
		
		/*
		member_role	
		member_name
		姓名	"身分證字號/護照號碼(外籍人士)"	出生年月日	性別	服務單位	通訊地址	聯絡電話	聯絡手機	緊急連絡人姓名	緊急連絡人電話
		王小明	F123456789	066-05-01			台北市杭州南路1段2號	(02)23515441		王大明	0900-123456
		Larry Schumacher		060-01-03			新竹市中山路2號	(03)5224163		陳聰明	0900-111789
        */
		
		$row = 2;
		$counter = 0;
		$members = array();
		do{
		  $member = array();
		  $member['member_role']  = trim($objSheet->getCellByColumnAndRow(0,$row)->getValue());
		  $member['member_name']  = trim($objSheet->getCellByColumnAndRow(1,$row)->getValue());
		  $member['member_id']    = trim($objSheet->getCellByColumnAndRow(2,$row)->getValue());  // 身分證字號
		  $member['member_birth'] = trim($objSheet->getCellByColumnAndRow(3,$row)->getValue());  // 生日
		  $member['member_sex']   = trim($objSheet->getCellByColumnAndRow(4,$row)->getValue());  // 性別
		  $member['member_org']   = trim($objSheet->getCellByColumnAndRow(5,$row)->getValue());  // 服務單位
		  $member['member_addr']  = trim($objSheet->getCellByColumnAndRow(6,$row)->getValue()); // 地址
		  $member['member_tel']    = trim($objSheet->getCellByColumnAndRow(7,$row)->getValue()); // 聯絡電話
		  $member['member_cell']    = trim($objSheet->getCellByColumnAndRow(8,$row)->getValue()); // 聯絡電話
		  $member['member_contacter']  = trim($objSheet->getCellByColumnAndRow(9,$row)->getValue()); // 緊急聯繫親友
		  $member['member_contactto'] = trim($objSheet->getCellByColumnAndRow(10,$row)->getValue()); // 緊急聯繫電話
		  
		  $filter = count(array_filter($member));
		  
		  if(!$filter ){
			break;  
		  }
		  
		  $members[] = $member;
		  $counter++;
		  $row++;
		  
		}while($filter  && $counter < 100 );
		
		// update apply members
		$DB_UPDMETA	= $this->DBLink->prepare( SQL_Client::UPDATE_APPLY_MEMBER() );
		$DB_UPDMETA->bindValue(':apply_code',$ApplyCode);
		$DB_UPDMETA->bindValue(':member',json_encode($members,JSON_UNESCAPED_UNICODE));
		$DB_UPDMETA->bindValue(':countmbr',count($members));
		$DB_UPDMETA->execute();
		
		unset($objSheet);
		unset($objPHPExcel);
		unset($excelReader);
		
		// final
		$result['data'] = $members;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Apply Save Member By editor
	// [input] : ApplyCode :  \w\d{8};
	// [input] : MemberPass  :  string: \d+;
	// [input] : ApplyToken  :  array: CODE KEY from session ;
	// [input] : Applicant   :  array:  \d+  from initial session;
	public function Apply_Save_MbrEdit($ApplyCode=0,$MemberPass='',$ApplyToken=array(),$Applicant=false){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  
	  try{
		
        // 檢查參數
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		$apply_code = $ApplyCode;
		
		// 檢查使用者是否為本人
		if( !is_array($ApplyToken) || !isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCode  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		
		// check applicant session is exist
		if( !is_array($Applicant) || !isset($Applicant['applicant_userid'])){
		  throw new Exception('_APPLY_APPLICANT_DATA_FAIL');
		}
		
		// check member data list 
		$apply_members = json_decode(base64_decode(str_replace('*','/',rawurldecode($MemberPass))),true); 
		if( !is_array($apply_members) || !count($apply_members) ){
		  throw new Exception('_APPLY_MEMBER_PASSER_FAIL'); 	
		}
		
		
		// get apply data
		// SELECT area_booking.*,area_code,area_type,area_name FROM area_booking LEFT JOIN area_main ON ano=am_id WHERE apply_code=:apply_code AND area_booking._keep=1;
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_META());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCode))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		// get area applied member list  
		$member_checker = array();
		$DB_MBR = $this->DBLink->prepare(SQL_Client::GET_AREA_APPLIED_MEMBER());
		$DB_MBR->bindParam(':am_id',$booking['am_id']);
		$DB_MBR->bindValue(':apply_code',$booking['apply_code']);
		$DB_MBR->bindValue(':check_date',$booking['date_enter']);
		if(!$DB_MBR->execute()){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		while($tmp = $DB_MBR->fetch(PDO::FETCH_ASSOC)){
		  $member_list = json_decode($tmp['member'],true); 	
          foreach($member_list as $mbr){			
			if(!isset($member_checker[$mbr['member_id']])) $member_checker[$mbr['member_id']] = array();
			$applicant_name = $tmp['applicant_name'];
			$member_checker[$mbr['member_id']][] = array('apply_code'=>$tmp['apply_code'],'apply_date'=>$tmp['apply_date'],'applicant'=>str_pad(mb_substr($applicant_name,0,1),mb_strlen($applicant_name),'ｏ',STR_PAD_RIGHT));
		  } 		  
		}
		
		// check each member && applicant exist
		$check = array();
		$idset = array();  
		$fail = 0; 
		$chif = 0;
		foreach($apply_members as $no => $member){
		  // check member data right
		  $check[$no] = array(); 
		  foreach($member as $mf => &$mv){
			
			if($mf != 'member_role' AND $mf != 'member_org' && trim($mv)==''){
			  $check[$no][$mf] = 'empty';      	
			}
		    
			if( $mf=='member_id' ){
			  if(preg_match('/^\w\d{9}$/',$mv)  && !System_Helper::check_twid($mv)){
				$check[$no][$mf] = 'fail';   
			  }
			  if(isset($idset[$mv])){
				$check[$no][$mf] = 'double';    
			  }else if(isset($member_checker[$mv])){
				$check[$no][$mf] = 'applied'; 
			  }else{
				$idset[$mv] = 1;  
			  }
			} 
		    
			if( $mf=='member_tel' && !isset($check[$no][$mf]) &&  !preg_match('/^0/',$mv) ){
			  $check[$no][$mf] = 'lose';  	
			} 
		  
		  }
		  
		  if(count($check[$no])){
			$fail++;  
		  }
		  
		  // check chif data exist
		  if( $member['member_id']==$Applicant['applicant_userid'] && $member['member_name']==$Applicant['applicant_name']   ){
			$chif = 1;  
		  }
		
		}
		
		if($fail){
		  $result['data'] = $check;
		  throw new Exception('_APPLY_MEMBER_CHECKED_FAIL'); 		
		}
		
		if(!$chif){
		  throw new Exception('_APPLY_MEMBER_NO_APPLICANT'); 	
		}
		
		// update apply members
		$DB_UPDMETA	= $this->DBLink->prepare( SQL_Client::UPDATE_APPLY_MEMBER() );
		$DB_UPDMETA->bindValue(':apply_code',$ApplyCode);
		$DB_UPDMETA->bindValue(':member',json_encode($apply_members,JSON_UNESCAPED_UNICODE));
		$DB_UPDMETA->bindValue(':countmbr',count($apply_members));
		$DB_UPDMETA->execute();
		
		// final
		$result['data']   = $apply_members;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	
	}
	
	
	//-- Client Apply Record Data Read 
	// [input] : ApplyCodeSubmit  : from submit;
	// [input] : ApplyToken   : from session array('CODE'=>apply_code,'KEY'=> SH:time() );
	public function Apply_Record_Read( $ApplyCodeSubmit='' , $ApplyToken=array() ){
	  $result_key = parent::Initial_Result('applied');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 檢查申請序號
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCodeSubmit)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查讀取序號是否有權限
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCodeSubmit){
		  throw new Exception('_APPLY_RECOVER_DENIAL');
		}
		
		// 取得申請資料
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCodeSubmit))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		// 取得區域資料
		$applicant = json_decode($booking['applicant_info'],true);
		$joinmember= json_decode($booking['member'],true);
		$application = json_decode($booking['apply_form'],true);
		
		$result['data']['applicant']	= $applicant;
		$result['data']['joinmember']	= $joinmember;
		$result['data']['application']	= $application;
		$result['data']['status'] = $booking['_status'];
		$result['data']['stage']  = $booking['_stage'];
		$result['data']['final']  = $booking['_final'];
		
		$result['action'] = true;
	    
		// 更新session時限
		$result['session']['APPLYTOKEN'] = ['CODE'=>$ApplyToken['CODE'],'KEY'=>_SYSTEM_NAME_SHORT.':'.strtotime('now')];
		
		
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  
	  return $result;  
	}
	
	
	//-- Client Apply Record Data Read 
	public function Apply_Download_Check( ){
	  $result_key = parent::Initial_Result('download');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 確認申請資料是否已取得
		if(!isset($this->ModelResult['applied'])){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND'); 
		}
		
		$booking_final_status = isset($this->ModelResult['applied']['data']['final']) ? $this->ModelResult['applied']['data']['final'] : '';
		
		// 檢查申請狀態是否符合
		if( $booking_final_status!='核准進入'){
		  throw new Exception('_APPLY_DOWNLOAD_DENIA');
		}
		
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  return $result;  
	}
	
	
	
	//-- Client Apply Record Data Check 
	// [input] : ApplyCodeSubmit  : from submit;
	// [input] : ApplyToken   : from session array('CODE'=>apply_code,'KEY'=> SH:time() );
	public function Apply_Record_Check( $ApplyCodeSubmit='',$ApplyToken=array()){
	  $result_key = parent::Initial_Result('check');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 檢查申請序號
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCodeSubmit)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查讀取序號是否有權限
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCodeSubmit){
		  throw new Exception('_APPLY_RECOVER_DENIAL');
		}
		
		// 取得申請資料
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_DATA());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCodeSubmit))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		
		// 檢查申請人資料
		$applicant = json_decode($booking['applicant_info'],true);
		if(!count(array_filter($applicant))){
		  $result['data']['status'] = '_INITIAL';
		  throw new Exception('_APPLY_CHECK_INITIAL_FAILS');  	   
		}
		
		// 檢查申請資料
		$application  = json_decode($booking['apply_form'],true);
		$apply_reason = $booking['apply_reason'];
		$apply_date_enter = $booking['date_enter'];
		$apply_date_exit  = $booking['date_exit'];
		if(!count($application)){
		  $result['data']['status'] = '_FORM';
		  throw new Exception('_APPLY_CHECK_FORM_FAILS');  	    
		}
		
		// 檢查申請日期
		if(!strtotime($apply_date_enter) || !strtotime($apply_date_exit )){
		  $result['data']['status'] = '_FORM';
		  $result['data']['focus']  = 'apply_date_1s';
          throw new Exception('_APPLY_CHECK_DATES_FAILS');  		  
		}
		
		// 檢查成員名單
		$member = json_decode($booking['member'],true);
		if(!count(array_filter($member))){
		  $result['data']['status'] = '_MEMBER';
		  throw new Exception('_APPLY_CHECK_MEMBER_FAILS');  	   
		}
		
		// 更新與紀錄狀態
		$apply_process = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		  
		if($booking['_stage']<2){  // 若 stage 為 0 : 起始狀態
		  
		  $apply_status  = '收件待審';
		  $apply_process['client'][1][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'收件待審','note'=>'','logs'=>'');
			
		  // 確認是否抽籤，不用抽籤直接跳至審查狀態
		  $apply_stage = $booking['_stage'];
		  if( !$booking['_ballot']){
		    $apply_process['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取送審','note'=>'申請項目不須抽籤','logs'=>'');
		    $apply_stage = $apply_stage < 3 ? 3 : $apply_stage;
		    $apply_status= '正取送審';
		  }else{
		    $apply_process['client'][2] = [];
		    $apply_stage = $apply_stage==0 ?  1 : $apply_stage;
		  }
		
		  $DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STAGE()); 
	      $DB_UPD->bindValue(':apply_code',$booking['apply_code']);
		  $DB_UPD->bindValue(':stage'     , $apply_stage );
		  if( !$DB_UPD->execute() ){
		    throw new Exception('_APPLY_SUBMIT_FAIL');  
		  }
		
		}else{
		  
		  $apply_process['client'][0][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'更新資料','note'=>'','logs'=>'');
		  $apply_status = '更新資料';
		  if($booking['_stage']==3){
			$apply_process['client'][$booking['_stage']][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'資料補充','note'=>'','logs'=>'');  
		    $apply_status = '資料補充';
		  }
		
		}
		
		$DB_UPD = $this->DBLink->prepare(SQL_Client::UPDATE_APPLY_STATUS()); 
	    $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		$DB_UPD->bindValue(':status'    , $apply_status);
		$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		$result['data']   = $booking['apply_code'];
		$result['action'] = true;
	    
		
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  return $result;  
	}
	
	
	//-- Client Apply Record Cancel
	// [input] : ApplyCodeSubmit  : from submit;
	// [input] : ApplyToken   : array(CODE KEY )from session ;
	public function Apply_Record_Cancel($ApplyCodeSubmit='',$ApplyToken=array()){
	  $result_key = parent::Initial_Result('check');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 檢查申請序號
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCodeSubmit)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查讀取序號是否有權限
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCodeSubmit){
		  throw new Exception('_APPLY_RECOVER_DENIAL');
		}
		
		// 取得申請資料
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_META());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCodeSubmit))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		// 確認是否於時限內
		$cancel_deline = strtotime('-'.$booking['cancel_day'].' day',strtotime($booking['date_enter'].' 23:59:59'));
		if( intval($booking['_stage']) && strtotime('now') > $cancel_deline){
		  throw new Exception('_APPLY_CANCEL_EXPIRED');	
		}
		
		// 更新與紀錄狀態
		$apply_process = $booking['_progres'] ? json_decode($booking['_progres'],true) : ['client'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[] ],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		$apply_process['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'取消申請','note'=>'使用者取消申請','logs'=>'');
		$apply_status  = '取消申請';
		$apply_stage   = 5;
		
		$DB_UPD = $this->DBLink->prepare(SQL_Client::CANCEL_AREA_BOOKING()); 
	    $DB_UPD->bindValue(':apply_code', $booking['apply_code']);
		$DB_UPD->bindValue(':stage'     , $apply_stage );
		$DB_UPD->bindValue(':progres'   , json_encode($apply_process));
		$DB_UPD->bindValue(':status'    , $apply_status);
		$DB_UPD->bindValue(':final'     , $apply_status );
		if( !$DB_UPD->execute() ){
		  throw new Exception('_APPLY_SUBMIT_FAIL');  
		}
		
		$result['data']   = $booking['apply_code'];
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  return $result;  
	}
	
	
	
	
	//-- Client Apply License Progres
	// [input] : ApplyCodeSubmit  : from submit;
	// [input] : ApplyToken   : array(CODE KEY )from session ;
	public function Apply_License_Status_Read( $ApplyCodeSubmit='' ,$ApplyToken=array()){
	  $result_key = parent::Initial_Result('preview');
	  $result  = &$this->ModelResult[$result_key];
	  try{
		
		// 檢查申請序號
		if(!preg_match('/^[\w\d]{8,10}$/',$ApplyCodeSubmit)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查讀取序號是否有權限
		if(!isset($ApplyToken['CODE']) || $ApplyToken['CODE']!=$ApplyCodeSubmit){
		  throw new Exception('_APPLY_RECOVER_DENIAL');
		}
		
		// 取得申請資料
		$booking = array();
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_APPLICATION_META());
		if(!$DB_OBJ->execute(array('apply_code'=>$ApplyCodeSubmit))  ||  !$booking=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_APPLY_RECORD_NOT_FOUND');  
		}
		
		$applicant = json_decode($booking['applicant_info'],true);
		$joinmember= json_decode($booking['member'],true);
		$application = json_decode($booking['apply_form'],true);
		
		$result['data']['apply_code'] = $booking['apply_code'];
		$result['data']['applicant'] = $applicant;
		$result['data']['joinmember'] = $joinmember;
		$result['data']['application'] = $application;
		$result['data']['_ballot'] = $booking['_ballot'];
		$result['data']['_ballot_date'] = $booking['_ballot_date'];
		$result['data']['_stage'] = $booking['_stage'];
		$result['data']['_progres'] = json_decode($booking['_progres'],true);
		$result['data']['_final']   = $booking['_final'];
		$result['data']['_status']  = $booking['_status'];
		
		// check applied is freeze
		$cancel_deline = strtotime('-'.$booking['cancel_day'].' day',strtotime($booking['date_enter'].' 23:59:59'));
		 
		$result['data']['_isdone']  = strtotime('now') > $cancel_deline || ($booking['_final']=='取消申請') ? true : false;
		
		
		$result['action'] = true;
	    
	  }catch(Exception $e){
		$result['message'][] = $e->getMessage();    
	  }
	  
	  return $result;  
	}
	
	
	//-- Client Form Application Page 
	// [input] : ApplyCode  : from submit;
	// [input] : ShowType 顯示模式 / preview / license / ...
	public function Apply_Feform_Application_Page($ApplyCode='',$ShowType='preview'){
      
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
		$license[0] .= "    <tr ><th> 申請編號 </th><td>".$booking['apply_code']."</td><th> 申請人與進入人數 </th><td>".$booking['applicant_name']." , 等 " .$booking['member_count']." 人 </td></tr>";
		$license[0] .= "    <tr ><th> 申請目的/項目 </th><td colspan=4 >".$booking['apply_reason']."</td></tr>";
		$license[0] .= "    <tr ><th> 進入期間 </th><td colspan=4 >".$booking['date_enter']." ~ ".$booking['date_exit']."，共 ".$enter_dates." 日</td></tr>";
		$license[0] .= "    <tr ><th> 進入範圍 </th><td colspan=4 >".join('、',$application['area']['inter'])."</td></tr>";
		$license[0] .= "    <tr ><th> 預計進入入口 </th><td>".$application['area']['gate']['entr']."</td><th> 預計離開出口 </th><td>".$application['area']['gate']['exit']."</td>";
		$license[0] .= "    <tr ><th> 當日抵達入口時間 </th><td class='handwriting'>(請自行填寫)</td><th> 當日實際進入人數 </th><td class='handwriting'>(請自行填寫)</td>";
		$license[0] .= "  </table >";
		$license[0] .= "  <hr></hr>";
		
		// apply license part 2 // 收執聯
		$field_group = '申請資料';
		
		// $license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);  使用者直通key
		$admin_check_key    = rawurlencode(str_replace('/','*',base64_encode(json_encode( array('apply_code'=>$booking['apply_code'])))));
		$admin_check_link   = _SYSTEM_MANAGE_ADDRESS.'index.php?act=Booking/R3check/'.$admin_check_key ;
		
		$license[0] .= "  <div style='position:relative;'>"; 
		$license[0] .= "  <span style='position:absolute; top:-10px;right:0;'><img src='".(new chillerlan\QRCode\QRCode( $admin_check_link , new chillerlan\QRCode\Output\QRImage))->output()."' style='height:110px;'/></span>";
		$license[0] .= "  </div>"; 
		$license[0] .= "  <h1>".$booking['area_type']."進入許可證</h1>"; 
		$license[0] .= "  <div class='note'> 收執聯<br/>（請保留本聯，進入時隨身攜帶以備查驗） </div>"; 
		
		$license[1]  = "  <h2>申請事項：</h2> ";
		$license[1] .= "  <table class='application table-unbreak-container'>";
		$license[1] .= "    <tr ><th> 申請區域名稱 </th><td colspan=4 >".$booking['area_name']."</td></tr>";
		$license[1] .= "    <tr ><th> 申請編號 </th><td>".$booking['apply_code']."</td><th> 申請人與進入人數 </th><td>".$booking['applicant_name']." , 等 " .$booking['member_count']." 人 </td></tr>";
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
		$license[1] .= "  <hr style='visibility:hidden;'></hr>";
		
		
		// apply license part 3 // 隊員名單
		$license[2] = "  <h2>隊員名單：</h2> ";
		$license[2] .= "  <table class='joing_member table-unbreak-container' >";
		$license[2] .= "    <tr ><th>NO.</th><th>角色</th><th>姓名</th><th>基本資料</th><th>緊急聯繫人</th></tr>";
		foreach($joinmember as $mbrno => $member){
          $license[2] .= "  <tbody class=''><tr class='member_detail'>";	
		  $license[2] .= "    <td>".($mbrno+1)."</td>"; 
		  $license[2] .= "    <td>".$member['member_role']."</td>"; 
		  $license[2] .= "    <td>".$member['member_name']."</td>";
		  $license[2] .= "    <td class='member_info'>";
		  $license[2] .= "      <div><label>證件號碼</label><span>".substr($member['member_id'],0,2).str_pad('',(strlen($member['member_id'])-5),'*',STR_PAD_LEFT).substr($member['member_id'],-3,3)."</span><label>出生日期</label><span>".$member['member_birth']."</span></div>";
		  $license[2] .= "      <div><label>聯絡電話</label><span>".$member['member_tel']." / ".$member['member_cell']."</span></div>";
		  $license[2] .= "      <div><label>聯絡地址</label><span>".$member['member_addr']."</span></div>";
		  $license[2] .= "    </td>";
		  $license[2] .= "    <td>".$member['member_contacter']."<br/>".$member['member_contactto']."</td>";
		  $license[2] .= "  </tr></tbody>"; 
		}
		$license[2] .= "  </table >";
		$license[2] .= "  <hr class='break' style='visibility:hidden;'></hr>";
		
		
		// apply license part 4 // 注意事項與聯繫電話
		$license[3]  = "  <h2>進入規定與注意事項：</h2> ";
		$license[3] .= "  <div class='regulation'>";
		$license[3] .= "    <p>一、進入保護(留)區人員應隨身攜帶許可證收執聯及身分證明文件，並隨時接受管理機關（構）查驗。</p>";
		$license[3] .= "    <p>二、確實遵守保護(留)區各項有關法令(文化資產保存法、野生動物保育法及森林法等)，若有違反即廢止本許可。</p>";
		$license[3] .= "    <p>三、經許可進入自然保留區之人員，禁止為下列行為：";
		$license[3] .= "    <ul>";
		$license[3] .= "      <li>（一）改變或破壞其原有自然狀態。</li>";
		$license[3] .= "      <li>（二）攜入非本自然保留區原有之動植物。</li>";
		$license[3] .= "      <li>（三）採集標本。</li>";
		$license[3] .= "      <li>（四）在自然保留區內喧鬧或干擾野生物。</li>";
		$license[3] .= "      <li>（五）於植物、岩石及標示牌上另加文字、圖形或色帶等標示。</li>";
		$license[3] .= "      <li>（六）進入指定地點以外之區域。</li>";
		$license[3] .= "      <li>（七）污染環境或丟棄廢棄物。</li>";
		$license[3] .= "      <li>（八）露營、野炊、燃火、搭設棚帳、駕駛機動車輛、船舶及其他載具或操作空拍機。</li>";
		$license[3] .= "      <li>（九）游泳、騎乘自行車、越野路跑或舉辦競賽活動。</li>";
		$license[3] .= "      <li>（十）其他經主管機關認屬破壞或改變原有自然狀態之行為。</li>";
		$license[3] .= "      <li>前項第二款、第三款、第五款、第六款及第八款之行為，因保育目的或學術研究所需，經主管機關許可者，始得為之。</li>";
		$license[3] .= "    </ul>";
		$license[3] .= "    <p>四、保護(留)區因災害、重大疫病蟲害或其他原因須緊急處理之必要時，得逕行關閉或限制人員進出，或採取其他必要措施。許可進入日，如遇有逕行關閉或限制人員進出之情形，原許可失其效力，應重新申請。</p>";
		$license[3] .= "    <p>五、已申請核准進入者如因故取消行程不再進入，最晚可於進入前1日登入系統取消申請，但為顧及備取等待者權益，進入日期6日以前確實無法進入，請務必提早取消，以利系統釋出名額供備取人員遞補。</p>";
		$license[3] .= "    <p>六、若經查1年內有3次以上惡意申請之情形(例如：未註銷無故不進入、申請團體3人以上卻進入人數未達3人、未申請進入等)，將予以登錄為禁止申請名單並嚴格管制；違反第三點所列行為之一者，主管機關於該違規事實發生之日起3年內，不得許可其進入自然保留區。</p>";
		$license[3] .= "    <p>七、保護(留)區內部分地區常有毒蛇、毒蜂、蟲獸出沒及天候易變、地形險峻， 常有落石崩塌等危險，進入者應自行注意安全。</p>";
		$license[3] .= "  </div>";
		$license[3] .= "  <hr class='break' style='visibility:hidden;'></hr>";
		
		// 由系統帶出聯繫資訊
		if(isset($this->ModelResult['area']['data']['contect'])){
		  $area_contect = $this->ModelResult['area']['data']['contect'];	
		  $license[3] .= "  <h2>各管理機關(構)聯繫電話：</h2> ";
		  $license[3] .= "  <table class='contect' >";
		  $license[3] .= "    <tr ><th>管理單位</th><th>聯絡電話</th><th>轄管自然保留區名稱</th></tr>";	
		  foreach($area_contect as $gcode=>$ginfo){
			if(count($ginfo['areas']) && isset($ginfo['contect']) && count($ginfo['contect']) ){
			   $license[3] .= "    <tr ><td>".$ginfo['organ']." - ".$ginfo['contect'][0]['user_organ']."</td><td>".$ginfo['contect'][0]['user_tel']."</td><td>".join('<br/>',$ginfo['areas'])."</td></tr>";
			}   
		  }
		  $license[3] .= "    <tr ><td></td><td></td><td></td></tr>";
		  $license[3] .= "  </table >";
		}
		$license[3] .= "  <footer>林務局自然保護區域進入申請系統 敬啟</footer> ";
		
		// 設定輸出模式
		$license_display = $ShowType=='preview' ? $license[1].$license[2] : join('',$license);
		
		$result['data']['BOOKED_DATE']   = $booking['apply_date'];
		$result['data']['BOOKED_CODE']   = $booking['apply_code'];
		$result['data']['PAGE_CONTENT']   = "<div class='license_page'>". $license_display ."</div>";
		
		$result['action'] = true;
		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  return $result; 	
	}
	
	
	//-- Landing regist mail 
	// [input] : DataCode  :  string;
	public function Landing_Regist_Mail($DataCode=0 ){
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
		
		// 註冊存取序號
		$license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);
		
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
		    if(isset($progress['client'][$booking['_stage']])){
			  $message_conf = array_pop($progress['client'][$booking['_stage']]);
			  $mail_status_type = $message_conf['status'];
			  $mail_status_info = $message_conf['note'];	
			}else{
			  $mail_status_type = $booking['_status'];
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
		
		// 設定信件內容
        $to_sent = $booking['applicant_mail'];
        $mail_title = _SYSTEM_HTML_TITLE." / ".$mail_title_type." / 申請編號 : ".$booking['apply_code'];        
		
        $mail_content  = "<div>".$booking['applicant_name']." 您好：</div>";
		$mail_content .= "<div>台端於 <strong>".$booking['apply_date']."</strong> 申請進入『".$area_meta['area_name']."』 </div>";
		$mail_content .= "<div>申請狀態：".$mail_status_type."</div>";
		if($mail_status_info){
		  $mail_content .= "<div >訊息通知：<span style='color:red;font-weight:bold;'>".$mail_status_info."</span></div>";	
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
		$mail_content .= "<div>四、為維護自然生態，各保護區設有進入人數之承載量管制，若申請截止日(依據各區設定)總人數逾越承載量，系統將進行隨機抽籤，並發給審查通知或結果通知等狀態之電子郵件，請隨時留意通知內容，有資料不全通知補件時，應盡速補件，未於期限內補件者將予以駁退。已通知核准進入者，請登入申請單查詢頁面下載許可證，未出示許可證者禁止進入自然保護區域。。</div>";
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
		$DB_MAILJOB->bindValue(':creator' , $booking['applicant_name']);
		$DB_MAILJOB->bindValue(':editor' , '');
		$DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
		$DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
		if(!$DB_MAILJOB->execute()){
		  throw new Exception('_APPLY_MAIL_REGIST_FAIL');	
		}
		
		$mail_id = $this->DBLink->lastInsertId('system_mailer');
		self::Landing_Mail_Sent_Now($mail_id);
		
		// final 
		$result['data']['maildate']   = date('Y-m-d');
		$result['data']['mailuser']   = $booking['applicant_name'].'('.$booking['applicant_mail'].')';
		$result['action'] = true;
		
		
		
		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Mailer Sent Mail Now 
	// [input] : DataNo  :  \d+;
	protected function Landing_Mail_Sent_Now($DataNo=0){
	  $result_key = parent::Initial_Result('sent');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$mail_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdMailer::GET_MAIL_DATA());
		$DB_GET->bindParam(':smno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$mail_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 設定信件內容
        $to_sent 		= $mail_data['mail_to'];
        $mail_error 	= '';
		$mail_content	= htmlspecialchars_decode($mail_data['mail_content'],ENT_QUOTES);
        $mail_title		= $mail_data['mail_title'];
        
		$mail_logs      = json_decode($mail_data['_active_logs'],true);
		$mail_logs[date('Y-m-d H:i:s')] = 'Send mail by system' ;
		
        $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
        $mail->IsSMTP(); // telling the class to use SMTP 
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
		$mail->SMTPDebug  = 0;
		
		try {  
		  
          $mail->SMTPAuth   = _SYSTEM_MAIL_SMTPAuth;   // enable SMTP authentication      
		  if(_SYSTEM_MAIL_SSL_ACTIVE){
		    $mail->SMTPSecure = _SYSTEM_MAIL_SECURE;   // sets the prefix to the servie
		  }
		  $mail->Port       = _SYSTEM_MAIL_PORT;     // set the SMTP port for the GMAIL server
		  $mail->Host       = _SYSTEM_MAIL_HOST; 	   // SMTP server
		  $mail->CharSet 	= "utf-8";
		  $mail->Username   = _SYSTEM_MAIL_ACCOUNT_USER;  // MAIL username
		  $mail->Password   = _SYSTEM_MAIL_ACCOUNT_PASS;  // MAIL password
		  //$mail->AddAddress('','');
          
		  $mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$to_sent,$mail_paser)) ? trim($mail_paser[1]) : trim($to_sent);
		  if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
		    throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
		  }
		  
		  $mail->AddAddress($mail_to_sent,'');
		  $mail->SetFrom( $mail_data['mail_from'] , _SYSTEM_MAIL_FROM_NAME);
		  $mail->AddReplyTo($mail_data['mail_from'] , _SYSTEM_MAIL_FROM_NAME); // 回信位址
		  $mail->Subject = $mail_title;
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($mail_content);
		  
		  //$mail->AddCC(); 
		  //$mail->AddAttachment('images/phpmailer.gif');      // attachment
	      if(!$mail->Send()) {
			throw new Exception($mail->ErrorInfo);  
		  }  
		  sleep(3);
		  
		  // final 
		  $result['data']   = $mail_data['mail_to'];
		  $result['action'] = true;
		
		} catch (phpmailerException $e) {
		  $mail_error = $e->errorMessage();
		  $result['message'][] = $e->errorMessage();  //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  $mail_error = $e->errorMessage();
		  $result['message'][] = $e->errorMessage();  //echo $e->getMessage(); //Boring error messages from anything else!
		}
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  // final 
	  $mail_logs[date('Y-m-d H:i:s')] = $mail_error ?  'SENT MAIL Fails:'.$mail_error : 'SENT MAIL SUCCESS.';
	  $fail_array = array_map(function($logs){ return preg_match('/SENT MAIL Fails/',$logs)?1:0; },array_values($mail_logs));
	  $status = $mail_error=='' ? 1 : -1;  
	  $DB_UPD = $this->DBLink->prepare(SQL_AdMailer::UPDATE_MAIL_DATA(array('_active_logs','_status_code','_active_time','_result')));
	  $DB_UPD->bindValue(':_status_code' 	, $status , PDO::PARAM_INT);	
	  $DB_UPD->bindValue(':_active_time' 	, date('Y-m-d H:i:s'));
	  $DB_UPD->bindValue(':_result'  		, $mail_error ? $mail_error:'Mail Sent By System');
	  $DB_UPD->bindValue(':_active_logs'  	, json_encode($mail_logs));
	  $DB_UPD->bindValue(':smno'    		, intval($DataNo));
	  $DB_UPD->execute();
	  
	}
	
	
	
	/*[ Lotto Function Set ]*/ 
	
	//-- Admin Lotto Data Read 
	// [input] : NULL;
	
	public function Get_Area_Lotto_Data($AreaCode,$LottoDate){
	  
	  $result_key = parent::Initial_Result('lotto');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 檢查登入參數
		if( !preg_match('/^[\w\d]{8}$/',$AreaCode)  ){
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }
		
		$area = array();
		$DB_OBJ = $this->DBLink->prepare( SQL_Client::GET_TARGET_AREA_DATA() );
		$DB_OBJ->bindValue(':area_code',$AreaCode);
		if(!$DB_OBJ->execute() || !$area = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$lotto_date = '';
		if( !strtotime($LottoDate) ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}else{
		  $lotto_date = date('Y-m-d',strtotime($LottoDate));	
		}
		
		
		if(strtotime($lotto_date) < strtotime('2017-12-31 23:59:59')){
		  throw new Exception('_APPLY_LOTTO_UNRECORDED'); 	
		}
		
		
		// 查詢資料庫
		//aid,date_tolot,time_lotto,lotto_pool,lotto_num,logs_process,_loted,area_type,area_name,area_load,accept_max_day,accept_min_day,wait_list
		$DB_OBJ = $this->DBLink->prepare( SQL_Client::GET_LOTTO_TARGET_DATA() );
		$DB_OBJ->bindValue(':aid',$area['ano']);
		$DB_OBJ->bindValue(':date_tolot',$lotto_date);
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$dbraw=$DB_OBJ->fetch(PDO::FETCH_ASSOC);
		
		$booking = isset($dbraw['lotto_pool']) ? json_decode($dbraw['lotto_pool'],true) : array();
		foreach($booking as $i => $b){
		  $leader_length = mb_strlen($b['leader']);
		  $leader_first  = mb_substr($b['leader'],0,1);
		  $booking[$i]['leader'] = $leader_first.str_pad('',($leader_length-1) ,'o', STR_PAD_RIGHT);
		}
		
		// 取得區域抽籤資料
		$record = array();
		$record['booking'] = $booking ;
		//$record['process'] = json_decode($dbraw['logs_process'],true);
		
		$result['action'] = true;		
		$result['data']   = $record;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	/*[ Post Function Set ]*/ 
	
	//-- Get Client Post List
	// [input] : DataNo = system_post.pno
	public function Get_Client_Post_Target($DataNo){
	  
      $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{    
		
		//:確認申請序號
		if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}
		$post = array();
		
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::GET_CLIENT_POST_TARGET());
		if(!$DB_OBJ->execute(array('pno'=>intval($DataNo))) || !$post=$DB_OBJ->fetch(PDO::FETCH_ASSOC) ){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$DB_OBJ = $this->DBLink->prepare(SQL_Client::CLIENT_POST_HITS());
		$DB_OBJ->execute(array('pno'=>intval($DataNo)));
		
		$post['post_content'] = base64_encode(htmlspecialchars_decode($post['post_content']));
		$post['post_hits'] += 1;
		
		$result['action'] 	= true;		
		$result['data'] 	= $post;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      } 
	  return $result;
	}
	
	
  
  }
?>