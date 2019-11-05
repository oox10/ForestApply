<?php
  /* 
  每日抽籤作業
  
  時間：每日早上 2:00
  頻率：1次/天
  對象：當日所有區域
  
  */
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdLotto.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdMailer.php');   
 
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'systemJobs/TASK.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] APPLIED BOLLET TASK START!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	
	//[ STEP 01 ]: 取得各區域主管單位
	$area_group = array();
	$DB_GET	= $db->DBLink->prepare( "SELECT ug_code,ug_name FROM user_group WHERE ug_pri='3';" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_GROUP_CODE_SQL_FAIL');
    }
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  $area_apply[$tmp['ug_code']] = array();	
	}
	
	//[ STEP 02 ]: 建立抽籤盒子
	$lotto_date = date('Y-m-d');  // 抽籤當日
	//$lotto_date = date('Y-m-d',strtotime('2019-10-17'));  // 抽籤當日
	
	
	// 查詢尚未結束的抽籤表   date_tolot <= :date_tolot AND _loted < 2  AND _filled=0
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_QUEUE());
	$DB_OBJ->bindValue(':date_tolot',$lotto_date);
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	$lottoboxs = array();  //要抽籤的盒子們
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  $lottoboxs[$dbraw['aid'].'@'.$dbraw['date_enter']] = json_decode($dbraw['lotto_pool'],true);
	}
	
	// 查詢新增抽籤資料
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_BOOKING());
	if(!$DB_OBJ->execute(array('lotto_date'=>$lotto_date))){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  $lotto_index = $dbraw['am_id'].'@'.$dbraw['date_enter'];
	  $lotto_boxid = 0;
	  $lotto_queue = array(); 
	  
	  if(!isset($lottoboxs[$lotto_index])){
		//create lotto box 
		$DB_Box = $db->DBLink->prepare( SQL_AdLotto::BUILT_LOTTO_BOX());
		$DB_Box->bindValue(':amid',$dbraw['am_id']);
		$DB_Box->bindValue(':lotto_date',$dbraw['_ballot_date']);
		$DB_Box->bindValue(':enter_date',$dbraw['date_enter']);
		$DB_Box->execute();
		$lotto_boxid  = $db->DBLink->lastInsertId('booking_lotto');
		$lottoboxs[$lotto_index] = array();
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
		  'review' => ''
		];
	  }else{
		$lottoboxs[$lotto_index][$ticket_id]['leader'] = $dbraw['applicant_name'];
		$lottoboxs[$lotto_index][$ticket_id]['reason'] = $dbraw['apply_reason'];
		$lottoboxs[$lotto_index][$ticket_id]['people'] = $dbraw['member_count'];  
	  }
	}
	
	// 更新樂透盒
	$DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX());
	foreach($lottoboxs as $lotto_index => $lotto_pool){
	  list($area_id,$enter_date) = explode('@',$lotto_index);	
	  $DB_UPD->bindParam(':aid',$area_id);
	  $DB_UPD->bindParam(':date_enter',$enter_date);
	  $DB_UPD->bindValue(':lotto_pool',json_encode($lotto_pool));
	  $DB_UPD->execute();
	}
	
	/*========================================== 以上更新抽籤資料 =================================================*/
	
	
	//[ STEP 03 ]: 執行抽籤 - 第一次抽籤
	
	// 取得要抽籤之列表             
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_TODAY());
	$DB_OBJ->bindValue(':lottodate',$lotto_date); 
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  $lotto_process = json_decode($dbraw['logs_process'],true);
	  $ticket_has 	 = json_decode($dbraw['lotto_pool'],true);  // 目前有的票
	  $ticket_box	 = array();                                 // 合法的票
	  $apply_meta 	 = array();
	  
	  
	  //get area data  取得區域資料
	  $area_main = array();
	  $area_form = array();
	  $area_load = array();
	  $DB_AREA = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_DATA());
	  $DB_AREA->bindParam(':ano',$dbraw['aid']); 
	  if( !$DB_AREA->execute() || !$area_main=$DB_AREA->fetch(PDO::FETCH_ASSOC) ){
		$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'查無區域資料或已關閉'];  
		$DB_LOGS = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_LOGS());
		$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
		$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
		$DB_LOGS->execute();
		continue;
	  }
	  $area_form		 = json_decode($area_main['form_json'],true);
	  $area_load['main'] = ['id'=>$area_main['ano'] ,'name'=>$area_main['area_name'],'load'=>$area_main['area_load'],'lotto'=>array() ];
	  
	  // 查詢子區域
	  $DB_BLOCK = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_BLOCK_DATA());
	  $DB_BLOCK->bindParam(':amid',$dbraw['aid']); 
	  $DB_BLOCK->execute();
	  while($area_block=$DB_BLOCK->fetch(PDO::FETCH_ASSOC)){
		$area_load[$area_block['block_name']] = ['id'=>$area_block['ab_id'] ,'name'=>$area_block['block_name'],'load'=>$area_block['area_load'],'lotto'=>array() ];	  
	  }
	  /*
	  $area_main['ano'] ['area_code'] ['area_type'] ['area_name'] ['area_load'] ['wait_list']
	  $area_block['area_load']
	  */
	  
	  
	  // check each eticket is allow 查詢待抽籤之申請
	  $DB_BOOK = $db->DBLink->prepare( SQL_AdLotto::GET_BOOKING_DATA());
	  foreach($ticket_has as $tid => $ticket ){
		
		//$ticket[$ticket_id] = [ 'abno' => $dbraw['abno'], 'reason' => $dbraw['apply_reason'], 'leader' => $dbraw['applicant_name'], 'people' => $dbraw['member_count'], 'insert' => date('c') ];
		$record = $ticket;
		
		$DB_BOOK->bindParam(':abno',$ticket['abno']); 
		$DB_BOOK->bindParam(':amid',$dbraw['aid']); 
		
		if( !$DB_BOOK->execute() || !$booking=$DB_BOOK->fetch(PDO::FETCH_ASSOC) ){ //查無資料
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
		if(!$booking['member_count'] || $booking['member_count'] < 3){
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
	  
	  
	  // 若無抽籤清單則結束
	  if(!count($ticket_box)){
		$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'無申請票可抽籤'];  
		$DB_LOGS = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('logs_process','lotto_pool','_loted')));
		$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
		$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
		$DB_LOGS->bindValue(':lotto_pool'  ,json_encode($ticket_has)); 
		$DB_LOGS->bindValue(':_loted'  	   ,1); 
		$DB_LOGS->execute();
		continue;			
	  }
	  
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
		/* 區塊容量檢測暫停 20191014
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
		*/
		
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
		  
		  // 首抽不再指定備取順序，改候補程序  20191014 
		  $ticket_que[] = $apply['abno'];
		  $reason .= ', 候補';
		  $ticket_has['b:'.$apply['abno']]['lotto']  = 2;
		  $ticket_has['b:'.$apply['abno']]['review'] = '候補';
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
	  $DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_BOOKING_DATA(array('_ballot_result','_progres','_stage','_status')));
	  foreach($ticket_got as $abid){
		
		$progress = json_decode($apply_meta[$abid]['_progres'],true);
		$lotto_result = '正取送審';
		
		$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>date('Y-m-d').'抽籤正取','logs'=>'');
		$DB_UPD->bindParam(':abno',$abid); 
		$DB_UPD->bindValue(':_ballot_result',1); 
		$DB_UPD->bindValue(':_progres',json_encode($progress)); 
		$DB_UPD->bindValue(':_stage',3); 
		$DB_UPD->bindValue(':_status',$lotto_result); 
		$DB_UPD->execute();
		
		bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result,$apply_meta[$abid]['date_enter']);
	  }
	  
	  
	  // 備取與未中  // 20191014 修改為候補程序
	  foreach($ticket_que as $i => $abid){
		$progress = json_decode($apply_meta[$abid]['_progres'],true);
		$DB_UPD->bindParam(':abno',$abid); 
		 
		$lotto_result = '抽籤未中，等待候補';		
		$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'抽籤未中','note'=>'抽籤未中等待候補','logs'=>'');	
		$DB_UPD->bindValue(':_stage',2);
		$DB_UPD->bindValue(':_ballot_result',2); 
		$DB_UPD->bindValue(':_status','收件待審');
		$DB_UPD->bindValue(':_progres',json_encode($progress)); 
		$DB_UPD->execute();
	    bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result,$apply_meta[$abid]['date_enter']);
	  }
	  
	  // 區域本日抽籤完成
	  $lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'區域首抽完畢']; 
	  $DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('time_lotto','lotto_num','logs_process','_loted','lotto_pool')));
	  $DB_UPD->bindParam(':blno',$dbraw['blno']); 
	  $DB_UPD->bindValue(':time_lotto',date('Y-m-d H:i:s')); 
	  $DB_UPD->bindValue(':lotto_num',array_sum(array_values($area_load['main']['lotto'])));
	  $DB_UPD->bindValue(':_loted',1); 
	  $DB_UPD->bindValue(':logs_process',json_encode($lotto_process)); 
	  $DB_UPD->bindValue(':lotto_pool',json_encode($ticket_has)); 
	  $DB_UPD->execute(); 
	}
	
	
	
	//[ STEP 04 ]: 執行抽籤 - 候補抽籤
	
	// 取得要抽籤之列表             
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_WAIT());	
	$DB_OBJ->bindValue(':lottodate',$lotto_date); 
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  // 只處理 2019-10-20 以後的單子
	  if( strtotime($dbraw['date_enter'].' 00:00:00') < strtotime('2019-10-20 01:00:00') ){
		  continue;  
	  }
	  
	  
	  $lotto_process = json_decode($dbraw['logs_process'],true);
	  $ticket_has = json_decode($dbraw['lotto_pool'],true);  // 目前有的票
	  $ticket_box = array();                                 // 合法的票
	  $apply_meta = array();
	  
	  //get area data  取得區域資料
	  $area_main = array();
	  $area_load = array();
	  $area_form = array();
	  $DB_AREA = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_DATA());
	  $DB_AREA->bindParam(':ano',$dbraw['aid']); 
	  if( !$DB_AREA->execute() || !$area_main=$DB_AREA->fetch(PDO::FETCH_ASSOC) ){
		$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'查無區域資料或已關閉'];  
		$DB_LOGS = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_LOGS());
		$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
		$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
		$DB_LOGS->execute();
		continue;
	  }
	  
	  $area_load['main'] = ['id'=>$area_main['ano'] ,'name'=>$area_main['area_name'],'load'=>$area_main['area_load'],'lotto'=>[] ];
	  
	  $DB_BLOCK = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_AREA_BLOCK_DATA());
	  $DB_BLOCK->bindParam(':amid',$dbraw['aid']); 
	  $DB_BLOCK->execute();
	  while($area_block=$DB_BLOCK->fetch(PDO::FETCH_ASSOC)){
		$area_load[$area_block['block_name']] = ['id'=>$area_block['ab_id'] ,'name'=>$area_block['block_name'],'load'=>$area_block['area_load'],'lotto'=>array() ];	  
	  }
	  
	  /*
	  $area_main['ano'] ['area_code'] ['area_type'] ['area_name'] ['area_load'] ['wait_list']
	  $area_block['area_load']
	  */
	  
	  // 是否還可繼續候補
	  $keep_to_wait = strtotime($lotto_date.' 03:00:00') < strtotime('-'.$area_main['accept_min_day'].' day',strtotime($dbraw['date_enter'].' 23:59:59')) ? true : false;
	  // 目前人數
	  $area_applied = 0;
	  
	  // check each eacket is allow 
	  $DB_BOOK = $db->DBLink->prepare( SQL_AdLotto::GET_BOOKING_DATA());  //SELECT * FROM area_booking WHERE am_id=:amid AND abno=:abno AND _keep=1;
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
		if(!$booking['member_count'] || $booking['member_count'] < 3){
		  $ticket_has[$tid]['accept'] = 0;	
		  $ticket_has[$tid]['review'] = '人數不足';
		  continue;	
		}
		
		// 確認是否正確階段
		if($booking['_stage']==5 && $booking['_final']=='核准進入'){  // 佔位
			$area_load['main']['load'] = $area_load['main']['load'] - $booking['member_count'];
			$area_applied+=$booking['member_count'];
			continue;
		}else if($booking['_stage']==3){  // 佔位，審查中
			$area_load['main']['load'] = $area_load['main']['load'] - $booking['member_count'];
			$area_applied+=$booking['member_count'];
			continue;
		}else if($booking['_stage']==5 && $booking['_final']!='核准進入'){
			$ticket_has[$tid]['accept'] = 0;
		    continue;	 
		}
		
		// 放入抽籤箱
		$apply_meta[$booking['abno']] = $booking;
		
		$apply_form = json_decode($booking['apply_form'],true);
		$record['inter'] = isset($apply_form['area']['inter']) ? $apply_form['area']['inter'] : array();;
		$ticket_box[] = $record;
	  }
	  
	  
	  // 檢查是否要抽籤
	  if(!count($ticket_box)){
		$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'無申請票可候補'];  
		$DB_LOGS = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('logs_process','lotto_pool','_filled')));
		$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
		$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
		$DB_LOGS->bindValue(':lotto_pool'  ,json_encode($ticket_has)); 
		$DB_LOGS->bindValue(':_filled'     ,$keep_to_wait ? 0 : 1); 
		$DB_LOGS->execute();
		continue;			
	  }
	  
	  
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
		
		/* 20191014 暫不處理子區域容量
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
		*/
		
		// 區塊進入合法，確認是否容許於總區域容量
		if($getout){
		  $area_load_count = intval($area_load['main']['load']);
		  $area_into_count = array_sum(array_values($area_load['main']['lotto']));
		  if( $apply['people'] > ($area_load_count-$area_into_count) ){
			$getout = false;
			$reason .= ', 超過可補人數'.'('.$area_load_count.')';
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
		  $reason .=  ', 候補備取';
		  $ticket_has['b:'.$apply['abno']]['lotto']  = 1;
		  $ticket_has['b:'.$apply['abno']]['review'] = '候補,備取';
		}else{
		  $ticket_que[] = $apply['abno'];
		  $reason .= ', 候補';
		  $ticket_has['b:'.$apply['abno']]['lotto']  = 2;
		  $ticket_has['b:'.$apply['abno']]['review'] = '候補';
		}
		
		// 設定
		$lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'LOTTO : '.$lot_descrip,'info'=>$reason];  
		
		// 重整抽籤箱
		unset($ticket_box[$lot_index]);
		$ticket_box = array_values($ticket_box);
		
		
		
	  }while(count($ticket_box));
	  
	  
	  // 更新申請資料
	  
	  // 正取
	  $DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_BOOKING_DATA(array('_ballot_result','_progres','_stage','_status')));
	  foreach($ticket_got as $abid){
		
		$progress = json_decode($apply_meta[$abid]['_progres'],true);
		$lotto_result = '備取送審';
		
		$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>date('Y-m-d').'抽籤備取','logs'=>'');
		$DB_UPD->bindParam(':abno',$abid); 
		$DB_UPD->bindValue(':_ballot_result',1); 
		$DB_UPD->bindValue(':_progres',json_encode($progress)); 
		$DB_UPD->bindValue(':_stage',3); 
		$DB_UPD->bindValue(':_status',$lotto_result); 
		$DB_UPD->execute();
		
		bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result,$apply_meta[$abid]['date_enter']);
	  }
	  
	  // 候補與未中
	  foreach($ticket_que as $i => $abid){
		
		if(!$keep_to_wait){  // 若還可以候補則不做任何更新，不再候補則處理候補失敗作業
		
			$lotto_result = '候補失敗';		
		    
			$progress = json_decode($apply_meta[$abid]['_progres'],true);
			$progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>'未候補到進入名額','logs'=>'');	
		    $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'','logs'=>'');	
		    $DB_UPD->bindParam(':abno',$abid);
			$DB_UPD->bindValue(':_stage',5);
		    $DB_UPD->bindValue(':_ballot_result',3); 
		    $ticket_has['b:'.$abid]['lotto']  = 3;	
		    $ticket_has['b:'.$abid]['review'] = $lotto_result;
		    $DB_UPD->bindValue(':_status',$lotto_result);
			$DB_UPD->bindValue(':_progres',json_encode($progress)); 
			$DB_UPD->execute();
			bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result,$apply_meta[$abid]['date_enter']);
		}
	  }
	  
	  // 區域本日抽籤完成
	  $lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'區域候補完畢']; 
	  $DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('time_lotto','lotto_num','logs_process','_loted','_filled','lotto_pool')));
	  $DB_UPD->bindParam(':blno',$dbraw['blno']); 
	  $DB_UPD->bindValue(':time_lotto'	,date('Y-m-d H:i:s')); 
	  $DB_UPD->bindValue(':lotto_num'	,$area_applied+array_sum(array_values($area_load['main']['lotto'])));
	  $DB_UPD->bindValue(':_loted'		,$keep_to_wait ? 1 : 2); 
	  $DB_UPD->bindValue(':_filled'		,$keep_to_wait ? 0 : 1);
	  $DB_UPD->bindValue(':logs_process',json_encode($lotto_process)); 
	  $DB_UPD->bindValue(':lotto_pool'	,json_encode($ticket_has)); 
	  $DB_UPD->execute(); 
	}
	
  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' APPLIED BOLLET TASK FINISH!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  
  // 抽籤通知信註冊
  function bollet_result_mail($DB,$ApplicantMail,$ApplyCode,$ApplyDate,$ApplyArea,$LottoResult,$EnterDate){
	  
	$mail_type    = '抽籤結果';
	$mail_logs = [date('Y-m-d H:i:s')=>'Regist Lotto Mail From [Job_Apply_Bollet_N_Lotto].' ];
	  
	$mail_title = _SYSTEM_HTML_TITLE." / 抽籤結果 / 申請編號 : ".$ApplyCode;    
	
	$mail_content  = "<div>申請人 您好：</div>";
    $mail_content .= "<div>台端於 <strong>".$ApplyDate."</strong> 申請 <strong>『".$EnterDate."』</strong> 進入『".$ApplyArea."』 </div>";
	$mail_content .= "<div>抽籤結果：<span style='font-weight:bold;color:blue;'>".$LottoResult."</span></div>";
	$mail_content .= "<div><br/></div>";
	$mail_content .= "<div>請詳閱下列注意事項：</div>";
	$mail_content .= "<div>一、請注意抽籤結果如為「正取/備取送審」，僅表示取得優先審查之排序，並不代表申請已獲核准，完成抽籤後審核機關將開始審核您的申請案，如因申請資料不全將通知補件修正，逾2天未補正或資格未符者應予駁退，空出名額由候補隊伍抽籤遞補。</div>";
	$mail_content .= "<div>二、完成抽籤後即不可更改進入成員名冊，如欲更改，必須取消申請後再重新提出申請。</div>";
	$mail_content .= "<div>三、正取或備取經候補成功者，通過審查後將收到核准進入通知，系統將於進入前4日開放下載許可證，於入園時必須隨身攜帶許可證及身份證件以備查驗。</div>";
	$mail_content .= "<div>四、第一次抽籤未中時將等待候補，如有申請案件取消並釋出名額時，每天可再抽籤遞補，建議盡量利用非假日申請入園以避開人潮。</div>";
	$mail_content .= "<div>五、詳細抽籤結果、最新審查進度及許可證下載，請利用申請編號登入申請系統查詢。</div>";
	$mail_content .= "<div><br/></div>";
	$mail_content .= "<div></div>";
	$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域管理機關(構)查詢。</div>";
    $mail_content .= "<div></div>";
	$mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
	$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";

	  
    $DB_MAILJOB	= $DB->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
	$DB_MAILJOB->bindValue(':mail_type',$mail_type);
	$DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
	$DB_MAILJOB->bindValue(':mail_to',$ApplicantMail);
	$DB_MAILJOB->bindValue(':mail_title',$mail_title);
	$DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
	$DB_MAILJOB->bindValue(':creator' , 'SystemJobs');
	$DB_MAILJOB->bindValue(':editor' , '');
	$DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
	$DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
	if(!$DB_MAILJOB->execute()){
	  file_put_contents($logs_file,'[ERROR]:Lotto Result Mail Regist Fail!'.PHP_EOL,FILE_APPEND);  
	}     
  }
  
  
  
  
  
  
  
  
  
  
  
  
  
?>