<?php
  session_start();
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');
  
  $db = new DBModule;
  $db->db_connect('PDO');
  
  $user_no = isset($_SESSION['iis']['_USER_NO']) ? intval($_SESSION['iis']['_USER_NO']) : 0;
  $task_no = isset($_REQUEST['task']) ? intval($_REQUEST['task']) : 0;
  
  
  if(!$task_no ){
	header('HTTP/1.0 400 Bad Request', true, 400);
    exit(1);	
  }
  
  $task_query = $db->DBLink->query("SELECT * FROM user_task WHERE utk=".$task_no." AND time_access='';");
  if(!$task_event = $task_query->fetch(PDO::FETCH_ASSOC)){
	header('HTTP/1.0 400 Bad Request', true, 400);
    exit(1);  
  }
  
  ob_end_clean();
  header('Content-Type: text/event-stream');
  header('Cache-Control: no-cache');
  ob_start();
  
  // 動作仍在持續
  if($task_event['time_finish']=='0000-00-00 00:00:00'){
	echo "event: _PROCESSING"."\n";
	echo 'data: {"task": "'.$task_no.'", "time": "'.intval(strtotime('now')-strtotime($task_event['time_initial'])).'", "progress": "'.$task_event['task_done'].' / '.$task_event['task_num'].'"}'."\n\n";  
  }else{
	$task_query = $db->DBLink->query("UPDATE user_task SET time_access='".date('Y-m-d H:i:s')."' WHERE utk=".$task_no.";");  
	echo "event: ".$task_event['task_type']."\n";
	echo 'data: {"task": "'.$task_no.'", "name": "", "count": "'.$task_event['task_done'].'", "href": "'.$task_event['task_result'].'"}'."\n\n";  
  }
  ob_flush();
  flush();
  exit(1);
?>