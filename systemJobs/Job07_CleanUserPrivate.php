<?php
  /* 
  每日申請外審通知
  
  時間：每日早上 7:00
  頻率：1次/天
  資料：area_booking _status = 收件待審`,正取送審
  對象：區域外審人員
  
  */
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdMailer.php');   
 
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'systemJobs/TASK.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' DAILY PRIVATE CLEAN:'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	//取得舊申請資料
	$oldapply_limit = date('Y-m-d',strtotime('-2 year')); 
	
	
	$clean_counter = 0;
	
	$DB_UPD	= $db->DBLink->prepare( "UPDATE area_booking SET applicant_info=:master,member=:member,_stage=:_stage,_progres=:_progres WHERE abno = :abno;" );
	
	$DB_OLD	= $db->DBLink->prepare( "SELECT * FROM area_booking WHERE apply_date < :apply_limit;" );
	$DB_OLD->bindValue(':apply_limit',$oldapply_limit);
	
	if( !$DB_OLD->execute() ){
	  throw new Exception('_DB_ERROR_GET_OLD_APPLY_FAIL');
    }
	while( $tmp = $DB_OLD->fetch(PDO::FETCH_ASSOC)){
	  
	  $master = json_decode($tmp['applicant_info'],true);
	  $member = json_decode($tmp['member'],true); 
	  
	  foreach($master as $mf => &$mv){
		switch($mf){
		  case 'applicant_name'		: $mv = mb_substr($mv,0,1).str_repeat('Ｏ', (mb_strlen($mv)-1)); break;
		  case 'applicant_userid'	: $mv = str_pad(substr($mv,0,2),strlen($mv),'X',STR_PAD_RIGHT); break;
		  default: $mv='-'; break;
		}  
	  }
	  
	  
	  foreach($member as $mi => $mbr){
		foreach($mbr as $mf=>&$mv){
		  switch($mf){
		    case 'member_role': break;
		    case 'member_name': $mv = mb_substr($mv,0,1).str_repeat('Ｏ', (mb_strlen($mv)-1)); break;
		    case 'member_id'  : $mv = str_pad(substr($mv,0,2),strlen($mv),'X',STR_PAD_RIGHT); break;
		    case 'member_addr': $mv = mb_substr($mv,0,6); break;
		    default: $mv='-'; break;
		  }
		}
		$member[$mi] = $mbr;
	  }
	  
	  $progres = json_decode($tmp['_progres'],true);
	  $progres['admin'][5][] = [
	    'time'=>date('Y-m-d H:i:s'),
        'status'=>'移除隱私',
		'note'=>'',
		'logs'=>''
	  ];
	  
	  // 更新無隱私資料
	  $DB_UPD->bindValue(':master',json_encode($master));
	  $DB_UPD->bindValue(':member',json_encode($member));
	  $DB_UPD->bindValue(':_stage',7);
	  $DB_UPD->bindValue(':_progres',json_encode($progres));
	  $DB_UPD->bindValue(':abno',$tmp['abno']);
	  
	  $DB_UPD->execute();
	  $clean_counter++;		
	  
	}
	 
	$logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' PRIVATE CLEAN BEFORE'.$oldapply_limit.' : '.$clean_counter.'\'s RECORD'.PHP_EOL;
    file_put_contents($logs_file,$logs_message,FILE_APPEND);
	echo $logs_message;
  
  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' DAILY PRIVATE CLEAN FINISH.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
?>