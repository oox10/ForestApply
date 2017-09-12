<?php

  class Lotto_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Lotto Model Function Set ]*/ 
    
	
	//-- Admin Area Page Data List  !! copy from area admin
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
	
	
	//-- Admin Lotto Page Data List 
	// [input] : $TargetArea  string : area_code;
	// [input] : $TargetDate  string : lotto_date;
	
	public function ADLotto_Get_Lotto_List($TargetArea,$TargetDate ){
	  
	  $result_key = parent::Initial_Result('records');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdLotto::GET_LOTTO_AREA_LIST()));
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		$area_filter = trim($TargetArea) && $TargetArea!='_all' ? trim($TargetArea) : false; 
		$date_filter = trim($TargetDate) && strtotime($TargetDate) ? date('Y-m-d',strtotime($TargetDate)) : false; 
		
		
		// 取得區域清單
		$data_list = array();
		while($dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  
		  // 區域篩選
		  if($area_filter && $dbraw['area_code']!=$area_filter ){
			continue;  
		  }
		  
		  // 日期篩選
		  if($date_filter && $dbraw['date_tolot']!=$date_filter ){
			continue;  
		  }
		  
		  $record = $dbraw;
          
		  $apply_queue  = json_decode($dbraw['lotto_pool'],true);
		  $apply_group  = 0;
		  $apply_person = 0;
		  foreach($apply_queue as $abno => $apply){
			$apply_group++;  
			$apply_person+=intval($apply['people']); 
		  }
		  $record['applied'] = $apply_group.'團'.$apply_person.'人';
		  $data_list[] = $record; 
		
		}
		
		$result['action'] = true;		
		$result['data']['list']   = $data_list;	
        $result['data']['filter'] = array('area'=>'','date'=>'');
		if($area_filter) $result['data']['filter']['area'] = $area_filter;
		if($area_filter) $result['data']['filter']['date'] = $date_filter;
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Admin Lotto Data Read 
	// [input] : NULL;
	public function ADLotto_Read_Lotto_Data($DataNo){
	  
	  $result_key = parent::Initial_Result('record');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		// 檢查序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdLotto::GET_LOTTO_TARGET_DATA()));
		if(!$DB_OBJ->execute(array('blno'=>intval($DataNo))) || !$dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		// 取得區域抽籤資料
		$record = array();
		$record['blno']    = $dbraw['blno'];
		$record['booking'] = json_decode($dbraw['lotto_pool'],true);
		$record['process'] = json_decode($dbraw['logs_process'],true);
		
		$result['action'] = true;		
		$result['data']   = $record;		
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	
	/*== [ Module - Lotto Jobs ] ==*/
	
	
	//-- Admin Lotto Renew Lotto Box // 更新各抽籤盒子資料
	// [input] : NULL;
	public function ADLotto_Built_Lotto_Data($LottoDate){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  
	  $lotto_date = strtotime($LottoDate) ? date('Y-m-d',strtotime($LottoDate)) : date('Y-m-d');
	  
	  try{
	    
		// 查詢當天抽籤列表
		$DB_OBJ = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_QUEUE());
		if(!$DB_OBJ->execute(array('date_tolot'=>$lotto_date))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
        		
		$lottoboxs = array();  //要抽籤的盒子們
		$lottoinfo = array();
		while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  $lottoboxs[$dbraw['aid'].'@'.$dbraw['date_tolot']] = json_decode($dbraw['lotto_pool'],true);
		  $lottoinfo[$dbraw['aid'].'@'.$dbraw['date_tolot']] = $dbraw;
		}
		
		// 查詢新增抽籤資料
		$DB_OBJ = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_BOOKING());
		if(!$DB_OBJ->execute(array('lotto_date'=>$lotto_date))){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  
		  $lotto_index = $dbraw['am_id'].'@'.$dbraw['_ballot_date'];
		  $lotto_boxid = 0;
		  $lotto_queue = array(); 
		  
		  if(!isset($lottoboxs[$lotto_index])){
			//create lotto box 
			$DB_Box = $this->DBLink->prepare( SQL_AdLotto::BUILT_LOTTO_BOX());
			$DB_Box->bindValue(':amid',$dbraw['am_id']);
			$DB_Box->bindValue(':lotto_date',$dbraw['_ballot_date']);
			$DB_Box->bindValue(':enter_date',$dbraw['date_enter']);
			$DB_Box->execute();
			$lotto_boxid  = $this->DBLink->lastInsertId('booking_lotto');
			$lottoboxs[$lotto_index] = array();
		  
		  }else if($lottoinfo[$lotto_index]['_loted']){
            
			// 已經抽過了 ~~
            // 針對申請單補充資料
            //-- 設定申請狀態
			$progress = json_decode($dbraw['_progres'],true);
			$progress['review'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'錯過抽籤','note'=>'當日抽籤已結束','logs'=>'系統偵測到申請單錯過區域當日抽籤時間:'.$lottoinfo[$lotto_index]['time_lotto']);
			$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'抽籤失敗','note'=>date('Y-m-d').'錯過抽籤時間','logs'=>'');	
			$progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'','logs'=>'');	
			
			// UPD Booking Status
			$DB_UPD = $this->DBLink->prepare(SQL_AdBook::UPDATE_BOOK_DATA(array('_stage','_progres','_status','_final'))); 
			$DB_UPD->bindValue(':apply_code', $dbraw['apply_code']);
			$DB_UPD->bindValue(':_stage'    , 5);
			$DB_UPD->bindValue(':_progres'  , json_encode($progress));
			$DB_UPD->bindValue(':_status'   , '抽籤未中');
			$DB_UPD->bindValue(':_final'    , '申請註銷');
			$DB_UPD->execute();			
			
			continue;
		  }
		  
		  // 確認抽籤資料是否已放入箱中
          $ticket_id = 'b:'.$dbraw['abno'];
		  if(!isset($lottoboxs[$lotto_index][$ticket_id])){
			$lottoboxs[$lotto_index][$ticket_id] = [
			  'abno'   => $dbraw['abno'],
			  'code'   => $dbraw['apply_code'], 
			  'date'   => $dbraw['apply_date'],
			  'reason' => $dbraw['apply_reason'],
			  'leader' => $dbraw['applicant_name'],
			  'people' => $dbraw['member_count'],
			  'insert' => date('c'),
			  'accept' => 1,
			  'lotto'  => 0,
			  'queue'  => 0,
			  'review' => ''
			];
		  }else{
			  
			// 檢查資料是否需要抽籤
			if($dbraw['_ballot']){
			  $lottoboxs[$lotto_index][$ticket_id]['leader'] = $dbraw['applicant_name'];
			  $lottoboxs[$lotto_index][$ticket_id]['reason'] = $dbraw['apply_reason'];
			  $lottoboxs[$lotto_index][$ticket_id]['people'] = $dbraw['member_count'];  	
			}else{
			  unset($lottoboxs[$lotto_index][$ticket_id]);
			}
			
		  }
		}
		
		// 更新樂透盒
		$DB_UPD = $this->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX());
		foreach($lottoboxs as $lotto_index => $lotto_pool){
		  list($area_id,$lotto_date) = explode('@',$lotto_index);	
		  $DB_UPD->bindParam(':aid',$area_id);
		  $DB_UPD->bindParam(':date_tolot',$lotto_date);
		  $DB_UPD->bindValue(':lotto_pool',json_encode($lotto_pool));
		  $DB_UPD->execute();
		}
		
		$result['action'] = true;
        $result['data']   = count($lottoboxs);
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Go To Lotto 執行抽籤
	// [input] : NULL;
	public function ADLotto_Active_Booking_Lotto($TargetLotto,$LottoDate){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $lotto_date = strtotime($LottoDate) ? date('Y-m-d',strtotime($LottoDate)) : date('Y-m-d');
	  
	  try{
	    
		// 取得要抽籤之列表
		if(intval($TargetLotto)){  // 指定抽籤序號
		  $DB_OBJ = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_TARGET());
		  $DB_OBJ->bindValue(':lottono',intval($TargetLotto)); 
		}else{                     //未指定抽籤序號，則抽當天資料 
		  $DB_OBJ = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_TODAY());	
		  $DB_OBJ->bindValue(':lottodate',$lotto_date); 
		}
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		if(!$lotto_queue = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC)){
		  throw new Exception('_ADMIN_LOTTO_NO_BOX_CAN_LOTTO');  	
		}
		
		
		foreach($lotto_queue as  $dbraw ){
			
		  if(strtotime('now') < strtotime($dbraw['date_tolot'].' 08:00:00')){
			throw new Exception('_ADMIN_LOTTO_DATE_NOT_ARRIVE');   
		  }	
		  
		  $lotto_process = json_decode($dbraw['logs_process'],true);
		  $ticket_has = json_decode($dbraw['lotto_pool'],true);  // 目前有的票
		  $ticket_box = array();                                 // 合法的票
		  $apply_meta = array();
		  
		  // check each eacket is allow 
		  $DB_BOOK = $this->DBLink->prepare( SQL_AdLotto::GET_BOOKING_DATA());
		  foreach($ticket_has as $tid => $ticket ){
			
			//$ticket[$ticket_id] = [ 'abno' => $dbraw['abno'], 'reason' => $dbraw['apply_reason'], 'leader' => $dbraw['applicant_name'], 'people' => $dbraw['member_count'], 'insert' => date('c') ];
		    $record = $ticket;
			
			$DB_BOOK->bindParam(':abno',$ticket['abno']); 
		    $DB_BOOK->bindParam(':amid',$dbraw['aid']); 
		    
			if( !$DB_BOOK->execute() || !$booking=$DB_BOOK->fetch(PDO::FETCH_ASSOC) ){
			  $ticket_has[$tid]['accept'] = 0;
			  $ticket_has[$tid]['review'] = '條件不合';
			  continue;	
			}
			
			// 確認是否要抽籤
			if(!$booking['_ballot']){
			  $ticket_has[$tid]['accept'] = 0;	
			  $ticket_has[$tid]['review'] = '不須抽籤';
			  continue;
		    }
		    
			// 確認是否人數正確
			if(!$booking['member_count']){
			  $ticket_has[$tid]['accept'] = 0;	
			  $ticket_has[$tid]['review'] = '人數不足';
			  continue;	
			}
			
			// 確認是否正確階段
			//if($booking['_stage']!=1){
			//  $ticket_has[$tid]['accept'] = 0;
			//  continue;	
			//}
			
			// 放入抽籤箱
			$apply_meta[$booking['abno']] = $booking;
			
			$apply_form = json_decode($booking['apply_form'],true);
			$record['inter'] = isset($apply_form['area']['inter']) ? $apply_form['area']['inter'] : array();;
			$ticket_box[] = $record;
		  }
		  
		  if(!count($ticket_box)){
			$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'無申請票可抽籤'];  
            $DB_LOGS = $this->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('logs_process','lotto_pool')));
			$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
			$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
			$DB_LOGS->bindValue(':lotto_pool'  ,json_encode($ticket_has)); 
			$DB_LOGS->execute();
			continue;			
		  }
		  
		  //get area data  取得區域資料
		  $area_load = array();
		  $DB_AREA = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_DATA());
		  $DB_AREA->bindParam(':ano',$dbraw['aid']); 
		  if( !$DB_AREA->execute() || !$area_main=$DB_AREA->fetch(PDO::FETCH_ASSOC) ){
			$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'查無區域資料或已關閉'];  
			$DB_LOGS = $this->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_LOGS());
			$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
			$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
			$DB_LOGS->execute();
			continue;
		  }
		  
		  $area_load['main'] = ['id'=>$area_main['ano'] ,'name'=>$area_main['area_name'],'load'=>$area_main['area_load'],'lotto'=>array() ];
		  
		  $DB_BLOCK = $this->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_BLOCK_DATA());
		  $DB_BLOCK->bindParam(':amid',$dbraw['aid']); 
		  $DB_BLOCK->execute();
		  while($area_block=$DB_BLOCK->fetch(PDO::FETCH_ASSOC)){
		    $area_load[$area_block['block_name']] = ['id'=>$area_block['ab_id'] ,'name'=>$area_block['block_name'],'load'=>$area_block['area_load'],'lotto'=>array() ];	  
		  }
		  
		  /*
		  $area_main['ano'] ['area_code'] ['area_type'] ['area_name'] ['area_load'] ['wait_list']
		  $area_block['area_load']
		  */
		  
		  // 開始抽籤
		  $ticket_box;   // 抽籤的列表
		  $ticket_got=array();   // 被抽到的申請
		  $ticket_que=array();   // 備取申請
		  
		  do{
            
			//抽籤
			$lot_index   = rand(0,(count($ticket_box)-1));
			$lot_descrip = ($lot_index+1).'/'.count($ticket_box);
			
			$apply = $ticket_box[$lot_index];
			$apply['people'];
			$apply['inter'];
			
			$getout = true; // 是否中選
			$reason = $apply['code'].' : '.$apply['people'].'人 ';
			
			// 確認申請進入區塊是否允許
			foreach($apply['inter'] as $inter_block){  
			
			  if(!array_key_exists($inter_block,$area_load)){
				continue;  
			  }
			  
			  // 計算抽到的群組人數是否在允許容量內
			  $block_load = intval($area_load[$inter_block]['load']);
			  $block_into = array_sum(array_values($area_load[$inter_block]['lotto']));
              
			  if( $apply['people'] > ($block_load-$block_into) ){
				$getout = false;
			    $reason .= ', 進入'.$inter_block.'超過上限';
			  }
			}
			
			
			// 區塊進入合法，確認是否容許於總區域容量
			if($getout){
			  $area_load_count = intval($area_load['main']['load']);
			  $area_into_count = array_sum(array_values($area_load['main']['lotto']));
			  if( $apply['people'] > ($area_load_count-$area_into_count) ){
				$getout = false;
			    $reason .= ', 進入'.$area_main['area_name'].'超過上限';
			  }
			}
			
			// 抽籤結果設定
			if($getout){
			  $area_load['main']['lotto'][ $apply['abno'] ] = $apply['people'];
			  foreach($apply['inter'] as $inter_block){ 
			    if(array_key_exists($inter_block,$area_load)){
				  $area_load[$inter_block]['lotto'][$apply['abno']] = $apply['people'];
			    }
			  }
			  $ticket_got[] = $apply['abno'];
			  $reason .=  ', 正取';
			  $ticket_has['b:'.$apply['abno']]['lotto']  = 1;
			  $ticket_has['b:'.$apply['abno']]['review'] = '正取';
			  
			}else{
			  $ticket_que[] = $apply['abno'];
			  $reason .= ', 備取 '.(count($ticket_que));
			  $ticket_has['b:'.$apply['abno']]['lotto']  = 2;
			  $ticket_has['b:'.$apply['abno']]['review'] = '備取';
			  $ticket_has['b:'.$apply['abno']]['queue']  = count($ticket_que);
			  
			}
			
			// 設定
			$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'LOTTO : '.$lot_descrip,'info'=>$reason];  
			
			// 重整抽籤箱
			unset($ticket_box[$lot_index]);
			$ticket_box = array_values($ticket_box);
			
			
			
		  }while(count($ticket_box));
		  
		  
		  // 更新申請資料
		  
		  // 正取
		  $DB_UPD = $this->DBLink->prepare( SQL_AdLotto::UPDATE_BOOKING_DATA(array('_ballot_result','_progres','_stage','_status')));
		  foreach($ticket_got as $abid){
            $progress = json_decode($apply_meta[$abid]['_progres'],true);
			$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取送審','note'=>date('Y-m-d').'抽籤正取','logs'=>'');
			$DB_UPD->bindParam(':abno',$abid); 
		    $DB_UPD->bindValue(':_ballot_result',1); 
		    $DB_UPD->bindValue(':_progres',json_encode($progress)); 
			$DB_UPD->bindValue(':_stage',3); 
			$DB_UPD->bindValue(':_status','正取送審'); 
			$DB_UPD->execute();   			
		  }
		  
		  // 備取與未中
		  foreach($ticket_que as $i => $abid){
			$progress = json_decode($apply_meta[$abid]['_progres'],true);
			$DB_UPD->bindParam(':abno',$abid); 
		    if($i < $area_main['wait_list']){
			  $progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取送審','note'=>date('Y-m-d').'抽籤備取','logs'=>'');	
			  $DB_UPD->bindValue(':_stage',3); 
			  $DB_UPD->bindValue(':_ballot_result',2); 
			  $DB_UPD->bindValue(':_status','備取送審'); 
			}else{
			  $progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'抽籤未中','note'=>date('Y-m-d').'備取超過數量','logs'=>'');	
			  $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'抽籤未中','note'=>'','logs'=>'');	
			  $DB_UPD->bindValue(':_stage',5);
			  $DB_UPD->bindValue(':_ballot_result',3); 
              $ticket_has['b:'.$abid]['lotto'] = 3;	
			  $ticket_has['b:'.$abid]['review'] = '抽籤未中';
			  $DB_UPD->bindValue(':_status','抽籤未中');
			}
			$DB_UPD->bindValue(':_progres',json_encode($progress)); 
			$DB_UPD->execute();
		  }
		  
		  // 區域本日抽籤完成
		  $lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'區域抽籤完畢']; 
		  $DB_UPD = $this->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('time_lotto','lotto_num','logs_process','_loted','lotto_pool')));
		  $DB_UPD->bindParam(':blno',$dbraw['blno']); 
		  $DB_UPD->bindValue(':time_lotto',date('Y-m-d H:i:s')); 
		  $DB_UPD->bindValue(':lotto_num',array_sum(array_values($area_load['main']['lotto'])));
		  $DB_UPD->bindValue(':_loted',1); 
		  $DB_UPD->bindValue(':logs_process',json_encode($lotto_process)); 
		  $DB_UPD->bindValue(':lotto_pool',json_encode($ticket_has)); 
		  $DB_UPD->execute();
		  
		}
		
		$result['action'] = true;
        
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
  }
  
  
?>