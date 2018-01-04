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
	
	// 查詢抽籤列表(尚未抽籤)
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_QUEUE());
	$DB_OBJ->bindValue(':date_tolot',$lotto_date);
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	$lottoboxs = array();  //要抽籤的盒子們
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  $lottoboxs[$dbraw['aid'].'@'.$dbraw['date_tolot']] = json_decode($dbraw['lotto_pool'],true);
	}
	
	// 查詢新增抽籤資料
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_BOOKING());
	if(!$DB_OBJ->execute(array('lotto_date'=>$lotto_date))){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  $lotto_index = $dbraw['am_id'].'@'.$dbraw['_ballot_date'];
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
	  list($area_id,$lotto_date) = explode('@',$lotto_index);	
	  $DB_UPD->bindParam(':aid',$area_id);
	  $DB_UPD->bindParam(':date_tolot',$lotto_date);
	  $DB_UPD->bindValue(':lotto_pool',json_encode($lotto_pool));
	  $DB_UPD->execute();
	}
	
	
	//[ STEP 03 ]: 執行抽籤
	
	// 取得要抽籤之列表             
	$DB_OBJ = $db->DBLink->prepare( SQL_AdLotto::GET_LOTTO_TODAY());	
	$DB_OBJ->bindValue(':lottodate',$lotto_date); 
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  $lotto_process = json_decode($dbraw['logs_process'],true);
	  $ticket_has = json_decode($dbraw['lotto_pool'],true);  // 目前有的票
	  $ticket_box = array();                                 // 合法的票
	  $apply_meta = array();
	  
	  // check each eacket is allow 
	  $DB_BOOK = $db->DBLink->prepare( SQL_AdLotto::GET_BOOKING_DATA());
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
		$DB_LOGS = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('logs_process','lotto_pool')));
		$DB_LOGS->bindParam(':blno',$dbraw['blno']); 
		$DB_LOGS->bindValue(':logs_process',json_encode($lotto_process)); 
		$DB_LOGS->bindValue(':lotto_pool'  ,json_encode($ticket_has)); 
		$DB_LOGS->execute();
		continue;			
	  }
	  
	  //get area data  取得區域資料
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
	  
	  $area_load['main'] = ['id'=>$area_main['ano'] ,'name'=>$area_main['area_name'],'load'=>$area_main['area_load'],'lotto'=>array() ];
	  
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
		  $ticket_has['b:'.$apply['abno']]['lotto'] = 2;
		  $ticket_has['b:'.$apply['abno']]['review'] = '備取';
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
		
		bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result);
	  }
	  
	  // 備取與未中
	  foreach($ticket_que as $i => $abid){
		$progress = json_decode($apply_meta[$abid]['_progres'],true);
		$DB_UPD->bindParam(':abno',$abid); 
		if($i < $area_main['wait_list']){
		  
		  $lotto_result = '備取送審';
		  $progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>date('Y-m-d').'抽籤備取','logs'=>'');	
		  $DB_UPD->bindValue(':_stage',3); 
		  $DB_UPD->bindValue(':_ballot_result',2); 
		  $DB_UPD->bindValue(':_status',$lotto_result); 
		
		}else{	
	      
		  $lotto_result = '抽籤未中';		
		  $progress['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>date('Y-m-d').'備取超過數量','logs'=>'');	
		  $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'','logs'=>'');	
		  $DB_UPD->bindValue(':_stage',5);
		  $DB_UPD->bindValue(':_ballot_result',3); 
		  $ticket_has['b:'.$abid]['lotto'] = 3;	
		  $ticket_has['b:'.$abid]['review'] = $lotto_result;
		  $DB_UPD->bindValue(':_status',$lotto_result);
		
		}
		$DB_UPD->bindValue(':_progres',json_encode($progress)); 
		$DB_UPD->execute();
	  
	    bollet_result_mail($db,$apply_meta[$abid]['applicant_mail'],$apply_meta[$abid]['apply_code'],$apply_meta[$abid]['apply_date'], $area_main['area_name'] ,$lotto_result);
	
	  }
	  
	  // 區域本日抽籤完成
	  $lotto_process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'PROCESS','info'=>'區域抽籤完畢']; 
	  $DB_UPD = $db->DBLink->prepare( SQL_AdLotto::UPDATE_LOTTO_BOX_DATA(array('time_lotto','lotto_num','logs_process','_loted','lotto_pool')));
	  $DB_UPD->bindParam(':blno',$dbraw['blno']); 
	  $DB_UPD->bindValue(':time_lotto',date('Y-m-d H:i:s')); 
	  $DB_UPD->bindValue(':lotto_num',array_sum(array_values($area_load['main']['lotto'])));
	  $DB_UPD->bindValue(':_loted',1); 
	  $DB_UPD->bindValue(':logs_process',json_encode($lotto_process)); 
	  $DB_UPD->bindValue(':lotto_pool',json_encode($ticket_has)); 
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
  function bollet_result_mail($DB,$ApplicantMail,$ApplyCode,$ApplyDate,$ApplyArea,$LottoResult){
	  
	$mail_type    = '抽籤結果';
	$mail_logs = [date('Y-m-d H:i:s')=>'Regist Lotto Mail From [Job_Apply_Bollet_N_Lotto].' ];
	  
	$mail_title = _SYSTEM_HTML_TITLE." / 抽籤結果 / 申請編號 : ".$ApplyCode;    
	
	$mail_content  = "<div>申請人 您好：</div>";
    $mail_content .= "<div>台端於 <strong>".$ApplyDate."</strong> 申請進入『".$ApplyArea."』 </div>";
	$mail_content .= "<div>抽籤結果：<span style='font-weight:bold;color:blue;'>".$LottoResult."</span></div>";
	$mail_content .= "<div>詳細抽籤結果請至申請系統查詢</div>";
	$mail_content .= "<div><br/></div>";
	$mail_content .= "<div></div>";
	$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域審查管理單位。</div>";
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