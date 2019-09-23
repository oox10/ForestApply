<?php
  /* 
  每日申請資料統整
  
  時間：每日早上 7:00
  頻率：1次/天
  資料：area_booking _stage = 3
  對象：區域管理者、審核者
  
  */
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdMailer.php');   
 
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'systemJobs/TASK.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' DAILY ALERT START:'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	
	//取得各區域主管單位
	$area_group = array();
	
	$DB_GET	= $db->DBLink->prepare( "SELECT ug_code,ug_name FROM user_group WHERE ug_pri='3';" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_GROUP_CODE_SQL_FAIL');
    }
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  $area_apply[$tmp['ug_code']] = array();	
	}
	
	//取得本日需處理之申請單
	$booking = array();
	$DB_GET	= $db->DBLink->prepare( "SELECT area_booking.*,area_name,owner FROM area_booking LEFT JOIN area_main ON am_id=ano WHERE _stage=3 AND area_booking._keep=1 ORDER BY am_id ASC,date_enter ASC;" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_APPLIED_SQL_FAIL');
    }
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  
	  if(!isset($area_apply[$tmp['owner']][$tmp['area_name']])) $area_apply[$tmp['owner']][$tmp['area_name']] = array();
	  
	  $area_apply[$tmp['owner']][$tmp['area_name']][] = [
	    'apply_code' => $tmp['apply_code'],
		'apply_date' => $tmp['apply_date'],
		'date_enter' => $tmp['date_enter'],
		'applicant'  => $tmp['applicant_name'].','.$tmp['member_count'].'人',
		'status'     => $tmp['_status'],
		'update'     => $tmp['_time_update'],
		'owner'      => $tmp['owner'],
	  ];  
	
	}
	
	// 依據群組將信件註冊後寄出
	foreach($area_apply as $gcode => $areatickets){
	  
	  if(!count($areatickets)){ // 沒有紀錄就跳過
		continue;
	  }
	  
	  // 取得區域管理者
	  $admin_mails = array();
	  $DB_GET	= $db->DBLink->prepare( "SELECT user_name,user_mail FROM (SELECT uid,gid, COLUMN_GET(rset,'R01' as char) AS R01, COLUMN_GET(rset,'R02' as char) AS R02 FROM permission_matrix WHERE master=1) AS PM LEFT JOIN user_info ON PM.uid=user_info.uid WHERE gid=:gid AND (R01=1 OR R02=1) AND user_pri>0;" );
	  if( !$DB_GET->execute(array('gid'=>$gcode)) ){
	    throw new Exception('_DB_ERROR_GET_GROUP_CODE_SQL_FAIL');
      }
	  while($tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		$admin_mails = array_merge($admin_mails, explode(';',$tmp['user_mail'])); 
	  }
	  
	  // 建立資料清單
	  $tickets_counter = 0;  //待審計數
	  
	  $applied_table   = "<table style='width:100%;'>";
	  foreach($areatickets as $area_name => $tickets){
		$applied_table .= "<tr><td colspan=6 style='font-weight:bold;border-bottom:2px #000000 solid;'>".$area_name."</th></tr>";
	    $applied_table .= "<tr><td>申請代號</td><td>申請日期</td><td>進入日期</td><td>申請人</td><td>目前狀態</td><td>最後更新</td></tr>";
		foreach( $tickets as $apply){
		  $applied_table  .= "<tr><td>".$apply['apply_code']."</td><td>".$apply['apply_date']."</td><td>".$apply['date_enter']."</td><td>".$apply['applicant']."</td><td>".$apply['status']."</td><td>".$apply['update']."</td></tr>";  
	    }
		$tickets_counter+=count($tickets);
		
	  }
	  $applied_table .= "</table>";  
	  
      $mail_type    = '系統排程';
	  $mail_logs 	= [date('Y-m-d H:i:s')=>'Regist Alert Mail From [Job_Apply_Record_Daily_Alert].' ];
	  
	  $mail_title   = _SYSTEM_HTML_TITLE.' / 每日審查通知 / '.date('ymd').' : '.$tickets_counter.'筆';
	  
	  if(!count($admin_mails)){ // 沒有管理者則寄給系統管理員
		$admin_mails[] = _SYSTEM_CONTACT_MAIL;
		$mail_title .= ' / 本區目前無管理者';
	  }
	  
	  $mail_content  = "<div>管理者您好</div>";
	  $mail_content .= "<div>轄區今日仍處於審查階段申請單如下：</div>";
	  $mail_content .= "<div>勞煩撥空處理</div>";
	  $mail_content .= "<div><br/></div>";
	  $mail_content .= "<div>".$applied_table."</div>";
	  $mail_content .= "<div></div>";
	  $mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題請洽系統管理者。</div>";
	  $mail_content .= "<div> </div>";
	  $mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." - ".$mail_type."</div>";
	  $mail_content .= "<div>".date('Y-m-d H:i:s').". </div>";
	  
      $DB_MAILJOB	= $db->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
	  $DB_MAILJOB->bindValue(':mail_type',$mail_type);
	  $DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
	  $DB_MAILJOB->bindValue(':mail_to',join(';',$admin_mails));
	  $DB_MAILJOB->bindValue(':mail_title',$mail_title);
	  $DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
	  $DB_MAILJOB->bindValue(':creator' , 'SystemJobs');
	  $DB_MAILJOB->bindValue(':editor' , '');
	  $DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
	  $DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
	  if(!$DB_MAILJOB->execute()){
		throw new Exception('_APPLY_MAIL_REGIST_FAIL');	
	  }   
	
	  // logs 
	  $logs_message = date("Y-m-d H:i:s").' [TASK] GROUP:'.$gcode.' '.date('Y-m-d').' DAILY ALERT MAIL REGIST!.'.PHP_EOL;
      echo $logs_message;
	  file_put_contents($logs_file,$logs_message,FILE_APPEND);
	  
	}
	
	
  
  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' DAILY ALERT FINISH.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  //執行備份
  pclose(popen("start /b "._SYSTEM_PHP_ROOT.ROOT."systemJobs/Task_DBBACKUP.php","r")); 
  
?>