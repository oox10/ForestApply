<?php
  /* 
  重建抽籤日期
  
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
  $logs_message = date("Y-m-d H:i:s").' [TASK] ReBuilt BOLLET Data!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	
	//[ STEP 01 ]: 取得各區域資料
	$area_data = array();
	$DB_GET	= $db->DBLink->prepare( "SELECT * FROM area_main WHERE 1;" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_GROUP_CODE_SQL_FAIL');
    }
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  $area_data[$tmp['ano']] = $tmp;	
	}
	
	
	//[ STEP 02 ]: 掃描所有未抽籤資料
	$fix_counter = 0; 
	
	// 查詢目標資料
	$DB_OBJ = $db->DBLink->prepare( "SELECT * FROM area_booking WHERE _status='收件待審' AND _ballot=1 AND _keep=1 ORDER BY am_id ASC,date_enter ASC,abno ASC;");
	if(!$DB_OBJ->execute()){
	  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	}
	
	while( $dbraw = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
	  
	  echo "\n".$dbraw['abno'].' : '.$dbraw['apply_code'].' : '.$dbraw['am_id'];
	  
	  if(!isset($area_data[$dbraw['am_id']])){
		$logs_message = date("Y-m-d H:i:s").' '.$dbraw['apply_code'].' AREA ID FAIL!.'.PHP_EOL;
		file_put_contents($logs_file,$logs_message,FILE_APPEND);
		echo $logs_message;    
		continue; 
	  }
	  
	  $area = $area_data[$dbraw['am_id']];
	  $data_bollet_date = $dbraw['_ballot_date'];
	  $real_bollet_date = date('Y-m-d',strtotime('-'.($area['accept_min_day']-1).' day',strtotime($dbraw['date_enter'])));
	  $data_progress = json_decode($dbraw['_progres'],true);
	  
	  
	  // 修復抽籤日期
	  if($data_bollet_date != $real_bollet_date){
		echo  " ".$data_bollet_date.' <=> '.$real_bollet_date;
	    var_dump($data_progress['review'][2]);
	  
	    $data_progress['review'][2][0] = [
	      'time'=>$real_bollet_date,
		  'status'=>'系統抽籤',
		  'note'=>'',
		  'logs'=>date('YmdHis').' fixed '.$data_bollet_date.'=>'.$real_bollet_date
	    ];
		
		$DB_Fix = $db->DBLink->prepare( "UPDATE area_booking SET _ballot_date=:_ballot_date,_progres=:_progres WHERE abno=:abno");
	    $DB_Fix->bindValue(':abno',$dbraw['abno']);
	    $DB_Fix->bindValue(':_ballot_date',$real_bollet_date);
	    $DB_Fix->bindValue(':_progres',json_encode($data_progress));
	    $DB_Fix->execute();
		
		echo " echo fix ballot date. ";
	    $fix_counter++;
	    
	  }
	  
	  // 修復複製申請初始紀錄
	  if(!count($data_progress['client'][1]) ){
		$data_progress['client'][1][0] = [
	      'time'=>$dbraw['apply_date'],
		  'status'=>'收件待審',
		  'note'=>'',
		  'logs'=>""
	    ];  
		
		$data_progress['review'][2][0] = [
	      'time'=>$real_bollet_date,
		  'status'=>'系統抽籤',
		  'note'=>'',
		  'logs'=>""
	    ];
		
	    echo " fill client&review logs. ";
		
		$DB_Fix = $db->DBLink->prepare( "UPDATE area_booking SET _progres=:_progres WHERE abno=:abno");
	    $DB_Fix->bindValue(':abno',$dbraw['abno']);
	    $DB_Fix->bindValue(':_progres',json_encode($data_progress));
	    $DB_Fix->execute();
	  }
	  
	}
	
	echo "\nFound ".$fix_counter." errors!!".PHP_EOL;
	
  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
  
  if(count($result['message'])){
	file_put_contents($logs_file,'[ERROR]:'.PHP_EOL,FILE_APPEND);  
	file_put_contents($logs_file,print_r($result['message'],true),FILE_APPEND);
  }
  $logs_message = date("Y-m-d H:i:s").' [TASK] '.date('Y-m-d').' APPLIED BOLLET FIX FINISH!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
   
  
  
?>