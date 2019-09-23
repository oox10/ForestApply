<?php        

  /*
  常駐處理任務  :  資料庫備份
  
  NOTE: 
  1. 每天一次
  2. 
  
  */
  define('ROOT',dirname(dirname(__FILE__)).'/');
  require_once(ROOT.'/conf/system_config.php');
   
  //ini_set('memory_limit', '4096M');
  ini_set('max_execution_time', 0);
  
  ob_start();
  
  define('_SYSTEM_MYSQL_PATH','C:/AppServ/"MariaDB 10.2"/bin/');  //
  define('_SYSTEM_7ZIP_PATH','C:/"Program Files"/7-Zip/');
  
  try{
     
	$sql_path = 'C:/AppServ/backup/database/';
	
	$sql_file = 'forest_db_backup_'.date('Ymd');
	
	$dbdump_commend   = "-h localhost -u "._SYSTEM_DB_USER." -p"._SYSTEM_DB_PASS." forestbooking_db --ignore-table=forestbooking_db.logs_system > ".$sql_path.$sql_file.'.sql';  // --no-create-info
	//$dbimport_commend = "-h 192.168.31.183 -u ahcms -pahcms -f --default-character-set=utf8 ahcm_db < ";
	
	//if(!is_dir(_SYSTEM_MYSQL_PATH)) throw new Exception('MYSQL資料夾不存在');  
    // 執行輸出
	
	$time_backupstart = time();
	
	echo "DB Backup Start:".$time_backupstart.PHP_EOL;
	ob_flush();
	flush();
	exec(_SYSTEM_MYSQL_PATH."mysqldump ".$dbdump_commend,$output,$return_var);
	echo "DB Backup Finish: ".$sql_file.'.sql'."(".filesize($sql_path.$sql_file.'.sql')." bytes) / ".(intval(time())-intval($time_backupstart))."'s ".PHP_EOL;
	ob_flush();
	flush();
	
	// 執行壓縮
	ob_flush();
	flush();
	$time_packagestart = time();
	echo "DB Zip Start:".$time_packagestart.PHP_EOL;
	ob_flush();
	flush();
	exec(_SYSTEM_7ZIP_PATH."7z a -tzip ".$sql_path.$sql_file.'.zip'." ".$sql_path.$sql_file.'.sql',$output,$return_var);
	echo "DB Zip Finish: ".$sql_file.'.zip'."(".filesize($sql_path.$sql_file.'.zip')." bytes) / ".(intval(time())-intval($time_packagestart))."'s ".PHP_EOL;
	ob_flush();
	flush();
	
    echo "DELETE sql file..";
	if(file_exists($sql_path.$sql_file.'.zip')){
	  unlink($sql_path.$sql_file.'.sql');
	}
	
	
	//清空資料庫備份
	$db_download_path = "C:/AppServ/webroot/ForestApply/webroot-admin/docs/DB/";
	$backupfiles = array_slice(scandir($db_download_path),2);
	
	foreach($backupfiles as $bpf){
		if( intval(date('Ymd',strtotime("-5 day"))) < intval(substr($bpf,-12,8))) continue;
		unlink($db_download_path.$bpf);
	}
	
	//移動資料庫備份
	if(copy($sql_path.$sql_file.'.zip',$db_download_path.$sql_file.'.zip')){
		unlink($sql_path.$sql_file.'.zip');
	}
	
	
	
	echo "Done.".PHP_EOL;
	/*
	echo "FTP Connect..";
	$conn_id = ftp_connect("140.112.145.78",21) or die("can't connect to ftp!!");
	
	$login_result = ftp_login($conn_id,'forest','forestapply');
	if (ftp_put($conn_id, $sql_file.'.zip', $sql_path.$sql_file.'.zip', FTP_ASCII)) {
	  echo "successfully uploaded ".PHP_EOL;;
	} else {
	  echo "There was a problem while uploading".PHP_EOL;;
	}
	// close the connection
	ftp_close($conn_id);
	*/
	
	echo "==========================================end".PHP_EOL;
	
	//file_put_contents($sql_path.'logs.txt',print_r($output,true),FILE_APPEND);
	
	
  } catch (Exception $e) {
	//$db->DBLink->query("UPDATE system_crontask SET result='".$e->getMessage()."' WHERE stcode='".$task['stcode']."';");  
	echo "FAIL".$e->getMessage();
  }
  
  
  
  
  
  
  
?>