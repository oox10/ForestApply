<?php
  /* 
  每區檢查遞補隊伍
  
  時間：每日早上 3:00
  頻率：1次/天
  對象：當日所有區域抽籤列表
  
  
  */
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdMailer.php');   
 
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'systemJobs/TASK.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] Wait List Fill START!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	
	//掃描所有區域，取出遞補日
	$DB_GET	= $db->DBLink->prepare( "SELECT ano,area_code,area_name,area_load,filled_day FROM area_main WHERE _keep=1 ORDER BY ano ASC;" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_AREA_SQL_FAIL');
    }
	
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  
	  //各區域應處理之進入日期
	  $date_enter = date('Y-m-d',strtotime('+'.$tmp['filled_day'].' day'));
	  
	  $DB_LOTTO = $db->DBLink->prepare( "SELECT * FROM booking_lotto WHERE aid=:aid AND date_enter=:date_enter;" );
	  $DB_LOTTO->bindValue(':aid',$tmp['ano']);
	  $DB_LOTTO->bindValue(':date_enter',$date_enter);
	  if( !$DB_LOTTO->execute()){
		file_put_contents($logs_file,'[ERROR]:SQL GET LOTTO: '.$tmp['ano'].'/'.$date_enter.' Fail!'.PHP_EOL,FILE_APPEND); 
		continue;
      }
	  
	  if(!$lotto = $DB_LOTTO->fetch(PDO::FETCH_ASSOC)){
		continue;
	  }
	  
	  $area_enter_limit = intval($tmp['area_load']);    // 區域容量限制
	  $area_accept_now  = intval($lotto['lotto_num']);  // 目前抽出的人數
	  
	  $ticktes = json_decode($lotto['lotto_pool'],true);  //所有票
	  $process = json_decode($lotto['logs_process'],true);  //處理歷程
	  $waitlist = array();
	  
	  // 檢查申請單
	  foreach($ticktes as $tkey => $tconf){
		
		$DB_BOOKING = $db->DBLink->prepare( "SELECT * FROM area_booking WHERE abno=:abno AND _keep=1;" );
	    $DB_BOOKING->bindValue(':abno',$tconf['abno']);
	    if( !$DB_BOOKING->execute()){
		  file_put_contents($logs_file,'[ERROR]:SQL GET BOOKING: '.$tconf['abno'].' Fail!'.PHP_EOL,FILE_APPEND); 
		  continue;
        }
		
		$booking = $DB_BOOKING->fetch(PDO::FETCH_ASSOC);
		$booking_check = true;
		$cnacel_reason = '';
		
		
		if(!intval($tconf['accept']) || intval($tconf['lotto'])=='3' ){  // 當初已失效或抽籤未中
		  continue;	 
		}
		
		if(!$booking){
		  // 已查無資料
		  $booking_check = false;
		  $cnacel_reason = '註銷－申請單已失效';
		
		}else if($booking['am_id'] != $tmp['ano']){
		  // 預約單已修改，資格取消
          $booking_check = false;
		  $cnacel_reason = '註銷－申請區域變更';
		
		}else if($booking['date_enter'] != $date_enter){
		  // 日期已修改，資格取消
		  $booking_check = false;
		  $cnacel_reason = '註銷－進入日期改變';
		
		}else if($booking['_stage']==5 AND $booking['_final']!=''){
		  // 申請單已結案
		  $booking_check = false;
		  $cnacel_reason = '註銷－申請單已結案';
		}
		
		if(!$booking_check){
		  $ticktes[$tkey]['accept'] = 0;
          $ticktes[$tkey]['reason'].= $cnacel_reason;
		  $process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'REVIEW : '.$tconf['code'],'info'=>$cnacel_reason];  
		  if(intval($tconf['lotto'])==1){
			$area_accept_now-=intval($ticktes['people']);  
		  }
		  file_put_contents($logs_file,date('Y-m-d H:i:s').' : '.$tconf['code'].' '.$cnacel_reason.PHP_EOL,FILE_APPEND);  
		  
		  continue;	
		}
		
		if( 2== intval($tconf['lotto'])  ){
		  $waitlist[$tconf['queue']] = $tconf;
		  $waitlist[$tconf['queue']]['booking'] =  $booking;
		  file_put_contents($logs_file,date('Y-m-d H:i:s').' : '.$tconf['code'].' add to filled queue.'.PHP_EOL,FILE_APPEND); 
		}
	    
	  }
	  
	  // 排序備取順序
	  ksort($waitlist);
	  	
      foreach($waitlist as $tconf){
		  
		$progress = json_decode($tconf['booking']['_progres'],true);
		
		
		if( ( $area_accept_now+intval($tconf['people']) ) <= $area_enter_limit ){
			
		    $ticktes['b:'.$tconf['abno']]['review'].=',成功';
			$area_accept_now+=intval($tconf['people']);
		  
		    // 註記progress
			$process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'FILLED : '.$tconf['code'],'info'=>'備取成功'];  
		    
			
			// 註記 booking
			$lotto_result = '備取成功';	
			$lotto_final  = '核准進入';	
			
		    $progress['review'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>'','logs'=>'');	
			$progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
		
		}else{
			
			$ticktes['b:'.$tconf['abno']]['review'].=',失敗';
			
			// 註記progress
			$process[] = ['time'=>date('Y-m-d H:i:s'),'type'=>'FILLED : '.$tconf['code'],'info'=>'備取失敗'];  
			
			// 註記 booking
			$lotto_result = '備取失敗';
			$lotto_final  = '申請註銷';	
			
		    $progress['review'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$lotto_result,'note'=>'','logs'=>'');	
		    $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'','logs'=>'');
			
		}
        
		// update booking  
		$DB_UPD = $db->DBLink->prepare( "UPDATE area_booking SET _stage:_stage,_status=:_status,_final=:_final,_progres=:_progres WHERE abno=:abno;" );  
		$DB_UPD->bindValue(':abno',$tconf['abno']);
		$DB_UPD->bindValue(':_stage',5);
		$DB_UPD->bindValue(':_status',$lotto_result);
		$DB_UPD->bindValue(':_final',$lotto_final);
		$DB_UPD->bindValue(':_progres',json_encode($progress));
		if(!$DB_UPD->execute()){
		  file_put_contents($logs_file,'[ERROR]: '.$tconf['code'].' UPDATE area_booking FAIL'.PHP_EOL,FILE_APPEND);
	    }
		
		// 註冊存取序號
		$license_access_key = hash('sha1',$tconf['booking']['applicant_name'].'雜'.$tconf['booking']['apply_code'].'湊'.$tconf['booking']['applicant_id']);
		
		// regist mail
		$mail_type    = '狀態通知';
	    $mail_logs = [date('Y-m-d H:i:s')=>'Regist Filled Mail From [Job_Apply_Wait_List_Checkout].' ];
	  
	    $mail_title = _SYSTEM_HTML_TITLE."/ ".$lotto_result."通知 / 申請編號:".$tconf['code'];  
	    
		$mail_content  = "<div>申請人 您好：</div>";
        $mail_content .= "<div>台端於 <strong>".$tconf['date']."</strong> 申請進入『".$tmp['area_name']."』 </div>";
	    $mail_content .= "<div>備取結果：<span style='font-weight:bold;color:blue;'>".$lotto_result."</span></div>";
	    
		if( $lotto_result == '備取成功' ){
		  $mail_content .= "<div>請依循申請連結進入系統並下載進入許可證</div>";	
		  $mail_content .= "<div>申請連結："._SYSTEM_SERVER_ADDRESS.'index.php?act=Landing/direct/'.$tconf['code'].'/'.$license_access_key."</div>";
		}
		
		$mail_content .= "<div><br/></div>";
	    $mail_content .= "<div></div>";
	    $mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域審查管理單位。</div>";
        $mail_content .= "<div></div>";
	    $mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
		$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";
	    
        $DB_MAILJOB	= $db->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
	    $DB_MAILJOB->bindValue(':mail_type',$mail_type);
	    $DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
	    $DB_MAILJOB->bindValue(':mail_to',$tconf['booking']['applicant_mail']);
	    $DB_MAILJOB->bindValue(':mail_title',$mail_title);
	    $DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
	    $DB_MAILJOB->bindValue(':creator' , 'SystemJobs');
	    $DB_MAILJOB->bindValue(':editor' , '');
	    $DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
	    $DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
	    if(!$DB_MAILJOB->execute()){
		  file_put_contents($logs_file,'[ERROR]: '.$tconf['code'].' REGIST alert mail FAIL'.PHP_EOL,FILE_APPEND);	
	    }
		
		file_put_contents($logs_file,date('Y-m-d H:i:s').' : '.$tconf['code'].' final filled ['.$lotto_result.'].'.PHP_EOL,FILE_APPEND);
	  }
      
	  // update lotto  
	  $DB_UPDBL = $db->DBLink->prepare( "UPDATE booking_lotto SET lotto_pool=:lotto_pool,logs_process=:logs_process,lotto_num=:lotto_num,_filled=1 WHERE blno=:blno;" );  
	  $DB_UPDBL->bindValue(':blno',$lotto['blno']);
	  $DB_UPDBL->bindValue(':lotto_pool',json_encode($ticktes));
	  $DB_UPDBL->bindValue(':logs_process',json_encode($process));
	  $DB_UPDBL->bindValue(':lotto_num',$area_accept_now);
	  if(!$DB_UPDBL->execute()){
		file_put_contents($logs_file,'[ERROR]: '.$lotto['blno'].' UPDATE Final Result FAIL'.PHP_EOL,FILE_APPEND);	
	  }
	  
	  file_put_contents($logs_file,date('Y-m-d H:i:s').' : booking_lotto.'.$lotto['blno'].' finished.'.PHP_EOL,FILE_APPEND);
	}

  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' Whit List Fill FINISH.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  
?>