<?php
  /* 
  自動通過審查
  
  時間：每日早上 2:30
  頻率：1次/天
  對象：當日所有審查中資料
  
  狀況：
  a.未進行審查資料＝>自動通過審查
  b.已審查資料
    -> 有補件 自動通過審查
	-> 未補件 檢查是否超過補件期限
	----> 超過期限  申請註銷
	----> 未過期限  跳過不處理
	
  */
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdMailer.php');   
 
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'systemJobs/TASK.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] BOOKING AUTO PASS START!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
		
	//掃描所有區域，取出遞補日
	$DB_GET	= $db->DBLink->prepare( "SELECT ano,area_code,area_name,area_load,auto_pass,revise_day FROM area_main WHERE _keep=1 AND auto_pass >= 0 ORDER BY ano ASC;" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_AREA_SQL_FAIL');
    }
	
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  
	  //各區域應處理之進入日期
	  echo 'CHECK AREA: '.$tmp['ano'].PHP_EOL;
	  
	  $DB_BOOKING = $db->DBLink->prepare( "SELECT * FROM area_booking WHERE am_id=:am_id AND _stage=3 AND _keep=1;" );
	  $DB_BOOKING->bindValue(':am_id',$tmp['ano']);
	  if( !$DB_BOOKING->execute()){
		file_put_contents($logs_file,'[ERROR]:SQL GET AREA:'.$tmp['ano'].' BOOKING [status=3] Fail!'.PHP_EOL,FILE_APPEND); 
		continue;
      }
	  
	  $pass_count = 0;
	  
	  while($booking = $DB_BOOKING->fetch(PDO::FETCH_ASSOC)){
		
		$check_date_time = strtotime(substr($booking['_time_update'],0,10).' 00:00:00');
	    $application     = json_decode($booking['apply_form'],true);
		
		
		if( strtotime('now') < strtotime( '+'.$tmp['auto_pass'].' day',$check_date_time)){
		  continue;	
		}
		
		echo 'BOOKING:'.$booking['abno'].PHP_EOL;
		
		$progress = json_decode($booking['_progres'],true);  //處理歷程
		
		$process_result = '限期通過';
		$apply_new_status='';
		$apply_new_stage = 0;
		$apply_new_final = '';
		
		// 今天已經超過自動通過日期
		switch($booking['_status']){
		  case '正取送審':  // 直接通過
		  case '急件送審':  // 直接通過
		    $progress['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審核通過','note'=>'','logs'=>date('Y-m-d H:i:s'));	
			$progress['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取核准','note'=>'','logs'=>'');				
			$progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
			$progress['admin'][4][]  = array('time'=>date('Y-m-d H:i:s'),'status'=>'自動通過','note'=>'區域設定：審查限期通知後 '.$tmp['auto_pass'].' 天前自動通過','logs'=>'');	
		    $apply_new_status='核准進入';
			$apply_new_final ='核准進入'; 
		    $apply_new_stage = 5;
			break;
			
		  case '備取送審':  // 進入等待階段 
		    $progress['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審核通過','note'=>'','logs'=>date('Y-m-d H:i:s'));		
			$progress['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取核准','note'=>'','logs'=>'');
			$progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
			$progress['admin'][4][]  = array('time'=>date('Y-m-d H:i:s'),'status'=>'自動通過','note'=>'區域設定：審查限期通知後 '.$tmp['auto_pass'].' 天前自動通過','logs'=>'');	
		    $apply_new_status='核准進入';
			$apply_new_final ='核准進入'; 
		    $apply_new_stage = 5;
		    break;
			
		  case '申請補件':	
		  case '資料補充':
		  case '資料更新':
			$progress['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審核通過','note'=>'','logs'=>date('Y-m-d H:i:s'));	
			$progress['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取核准','note'=>'','logs'=>'');				
			$progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>'');
			$progress['admin'][4][]  = array('time'=>date('Y-m-d H:i:s'),'status'=>'自動通過','note'=>'區域設定：審查限期通知後 '.$tmp['auto_pass'].' 天前自動通過','logs'=>'');	
			$apply_new_status='核准進入';
			$apply_new_final ='核准進入';
			$apply_new_stage = 5;
			break;
					
			/*	
			  case 2:  //備取送審   20191016 以後僅有候補，沒有備取等待，2為候補 候補表示沒抽中
				$progress['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審核通過','note'=>'','logs'=>date('Y-m-d H:i:s'));		
				$progress['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取等待','note'=>'','logs'=>'');
				$progress['admin'][4][]  = array('time'=>date('Y-m-d H:i:s'),'status'=>'自動通過','note'=>'區域設定：審查限期通知後 '.$tmp['auto_pass'].' 天前自動通過','logs'=>'');	
				$apply_new_status='備取等待';
				$apply_new_stage = 4;
				break;
			*/			
			
		  case '資料不全':
		    
			//補件期限
			$additional_dateline = strtotime('+'.$tmp['revise_day'].' day',strtotime($booking['_time_update']));
		    
			//if( strtotime('now') > $additional_dateline  ){  
			  $progress['client'][3][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'資格不符','note'=>'超過補件日期:'.date('Y-m-d',$additional_dateline),'logs'=>'');	
			  $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'審查未過','note'=>'','logs'=>'');
			  $progress['admin'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'自動註銷','note'=>'排程系統註銷未補件申請','logs'=>'');	
			  
			  $apply_new_status='審查未過';
			  $apply_new_final ='申請註銷';
			  $apply_new_stage = 5;	
			  break; 
			//}
			// 如果在補件日期內：不管
			   
		    
		  // 其他狀況不處理
		  default: continue; break;
			
		}
		
		
		// 更新申請狀態
		// update booking  
		$DB_UPD = $db->DBLink->prepare( "UPDATE area_booking SET _stage=:_stage,_status=:_status,_progres=:_progres,_final=:_final WHERE abno=:abno;" );  
		$DB_UPD->bindParam(':abno',$booking['abno']); 
		$DB_UPD->bindValue(':_progres',json_encode($progress)); 
		$DB_UPD->bindValue(':_stage'  ,$apply_new_stage); 
		$DB_UPD->bindValue(':_status' ,$apply_new_status); 
		$DB_UPD->bindValue(':_final' ,$apply_new_final); 
		
		if(!$DB_UPD->execute()){
		  file_put_contents($logs_file,'[ERROR]: '.$booking['abno'].' UPDATE area_booking auto pass FAIL'.PHP_EOL,FILE_APPEND);
	    }
		
		
		// regist mail
		// 註冊存取序號
		$license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);
		
		// 設定信件內容
		$mail_title_type = '狀態通知';
        $to_sent = $booking['applicant_mail'];
        $mail_title = _SYSTEM_HTML_TITLE." / 審核結果通知 / 申請編號:".$booking['apply_code'];        
		
        $mail_content  = "<div>申請人 您好：</div>";
		$mail_content .= "<div>台端於 <strong>".$booking['apply_date']."</strong> 申請進入『".$tmp['area_name']."』 </div>";
		$mail_content .= "<div>申請狀態：".$apply_new_status."</div>";
		$mail_content .= "<div>申請連結："._SYSTEM_SERVER_ADDRESS.'index.php?act=Landing/direct/'.$booking['apply_code'].'/'.$license_access_key."</div>";
		$mail_content .= "<div><br/></div>";
		
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
		$mail_content .= "<div><br/></div>";
		$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域管理機關(構)查詢。</div>";
		$mail_content .= "<div><br/></div>";
	    $mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
		$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";
		
		$mail_logs = [date('Y-m-d H:i:s')=>'Regist Checked Mail From [Job_Apply_Auto_Pass_Booking].' ];
	  
        $DB_MAILJOB	= $db->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB_V2());
	    $DB_MAILJOB->bindValue(':mail_type',$mail_title_type);
	    $DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
	    $DB_MAILJOB->bindValue(':mail_to'	,$to_sent);
	    $DB_MAILJOB->bindValue(':mail_title',$mail_title);
	    $DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
		$DB_MAILJOB->bindValue(':mail_method', $booking['_checker']=='hike.mountain' ? 'hike':'self' );
	    $DB_MAILJOB->bindValue(':creator' , 'SystemJobs');
	    $DB_MAILJOB->bindValue(':editor' , '');
	    $DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
	    $DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
	    if(!$DB_MAILJOB->execute()){
		  file_put_contents($logs_file,'[ERROR]: '.$tconf['code'].' REGIST alert mail FAIL'.PHP_EOL,FILE_APPEND);	
	    }
		
		file_put_contents($logs_file,date('Y-m-d H:i:s').' BOOKING: '.$booking['abno'].' auto pass complete.'.PHP_EOL,FILE_APPEND); 
		$pass_count++;
		
	  }
	  file_put_contents($logs_file,date('Y-m-d H:i:s').' : AREA: '.$tmp['ano'].' '.date('Y-m-d').' auto pass [ '.$pass_count.' ] books finished.'.PHP_EOL,FILE_APPEND);
	}
	
	
	//掃描未申請完成之清單
	$logs_message = date("Y-m-d H:i:s").' [TASK] BOOKING AUTO CANCEL START!.'.PHP_EOL;
    file_put_contents($logs_file,$logs_message,FILE_APPEND);
    echo $logs_message;
	
	$DB_BOOK = $db->DBLink->prepare( "SELECT area_booking.*,area_name FROM area_booking LEFT JOIN  area_main ON am_id=ano WHERE area_booking._keep=1 AND _status='申請進入';" );
	if( !$DB_BOOK->execute() ){
	  throw new Exception('_DB_ERROR_GET_AREA_SQL_FAIL');
    }
	
	while( $book = $DB_BOOK->fetch(PDO::FETCH_ASSOC)){
	  
	  if(strtotime('-1 day') < strtotime($book['apply_date'])){
		continue;  
	  }
	  
	  echo 'UNCOMPLETE: '.$book['abno'].PHP_EOL;
	  
	  $progress = json_decode($book['_progres'],true);  //處理歷程
	  $application = json_decode($book['apply_form'],true);
	  
	  $progress['client'][1][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請未完成','note'=>'系統自動註銷，並通知申請人','logs'=>date('Y-m-d H:i:s'));	
	  $progress['client'][5][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'申請註銷','note'=>'','logs'=>date('Y-m-d H:i:s'));	
	  
	  $apply_new_status='申請註銷';
	  $apply_new_final ='申請註銷'; 
	  $apply_new_stage = 5;
		  
	  // 更新申請狀態
	  // update booking  
	  $DB_UPD = $db->DBLink->prepare( "UPDATE area_booking SET _stage=:_stage,_status=:_status,_progres=:_progres,_final=:_final WHERE abno=:abno;" );  
	  $DB_UPD->bindParam(':abno',$book['abno']); 
	  $DB_UPD->bindValue(':_progres',json_encode($progress)); 
	  $DB_UPD->bindValue(':_stage'  ,$apply_new_stage); 
	  $DB_UPD->bindValue(':_status' ,$apply_new_status); 
	  $DB_UPD->bindValue(':_final' ,$apply_new_final); 
		
	  if(!$DB_UPD->execute()){
		file_put_contents($logs_file,'[ERROR]: '.$book['abno'].' UPDATE area_booking auto cancel FAIL'.PHP_EOL,FILE_APPEND);
	  }
	  	
	  // 設定信件內容
	  $mail_title_type = '狀態通知';
      $to_sent = $book['applicant_mail'];
      $mail_title = _SYSTEM_HTML_TITLE." / 申請註銷通知 / 申請編號:".$book['apply_code'];        
		
      $mail_content  = "<div>申請人 您好：</div>";
	  $mail_content .= "<div>台端於 <strong>".$book['apply_date']."</strong> 申請進入『".$book['area_name']."』 </div>";
	  $mail_content .= "<div>申請狀態：".$apply_new_status."</div>";
	  $mail_content .= "<div>註銷原因：申請步驟未完成，系統通知註銷";
	  $mail_content .= "<div><br/></div>";
		
	  $mail_content .= "<div>一、本案申請資料如下：</div>";
	  $mail_content .= "<table><tr><td>(一)進入期間</td><td>：".$book['date_enter']." ~ ".$book['date_exit']."</td></tr>";
	  $mail_content .= "<tr><td>(二)進入區域/入口/出口</td><td>：".join(';',$application['area']['inter']).' / '.$application['area']['gate']['entr'].' / '.$application['area']['gate']['exit']."</td></tr>";
	  $mail_content .= "<tr><td>(三)申請代表人或領隊</td><td>：".$book['applicant_name']."</td></tr>";
	  $mail_content .= "<tr><td>(四)人數</td><td>：共 ".$book['member_count']." 人</td></tr>";
	  $mail_content .= "<tr><td>(五)申請編號</td><td>：- </td></tr></table>";
	  $mail_content .= "<div><br/><br/></div>";
	  $mail_content .= "<div>二、請妥善保管申請編號及隨時注意電子信箱訊息或登入「申請單查詢」頁面，掌握申請進度狀態、補發編號、申請資料補充或修改及取消申請等事宜，並以查詢之內容為準。</div>";
	  $mail_content .= "<div> </div>";
	  $mail_content .= "<div>三、審查管理機關(構)依保護(留)區相關法規、經營管理計畫等，保有核准及後續進入之管制權利(例如：為災害防救或重大疫病蟲害及其他原因必須緊急處理之必要時，得逕行關閉或限制人員進出等措施)，並以系統最新消息公告為準 。</div>";
	  $mail_content .= "<div> </div>";
	  $mail_content .= "<div>四、為維護自然生態，各保護區設有進入人數之承載量管制，若申請截止日(依據各區設定)總人數逾越承載量，系統將進行隨機抽籤，並發給審查通知或結果通知等狀態之電子郵件，請隨時留意通知內容，有資料不全通知補件時，應盡速補件，未於期限內補件者將予以駁退。已通知核准進入者，請登入申請單查詢頁面下載許可證，未出示許可證者禁止進入自然保護區域。</div>";
	  $mail_content .= "<div><br/></div>";
	  $mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域管理機關(構)查詢。</div>";
	  $mail_content .= "<div><br/></div>";
	  $mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
	  $mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";
		
	  $mail_logs = [date('Y-m-d H:i:s')=>'Regist Checked Mail From [Job_Apply_Auto_Pass_Booking].' ];
	  
	  $DB_MAILJOB	= $db->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB_V2());
	  $DB_MAILJOB->bindValue(':mail_type',$mail_title_type);
	  $DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
	  $DB_MAILJOB->bindValue(':mail_to'	,$to_sent);
	  $DB_MAILJOB->bindValue(':mail_title',$mail_title);
	  $DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
	  $DB_MAILJOB->bindValue(':mail_method', $book['_checker']=='hike.mountain' ? 'hike':'self' );
	  $DB_MAILJOB->bindValue(':creator' , 'SystemJobs');
	  $DB_MAILJOB->bindValue(':editor' , '');
	  $DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
	  $DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
	  if(!$DB_MAILJOB->execute()){
		  file_put_contents($logs_file,'[ERROR]: '.$tconf['code'].' REGIST alert mail FAIL'.PHP_EOL,FILE_APPEND);	
	  }
	  file_put_contents($logs_file,date('Y-m-d H:i:s').' BOOKING: '.$booking['abno'].' auto calcel complete.'.PHP_EOL,FILE_APPEND);  
	}
	 
	

  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
	echo $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' BOOKING AUTO PASS  FINISH.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
?>