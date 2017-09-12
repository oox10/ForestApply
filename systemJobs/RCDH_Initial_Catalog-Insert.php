<?php
    
	/**
	
	  讀取db資料表欄位  並建立新增sql
      
      讀取excel 欄位  並新增入資料庫	  
	
	**/
	
	require('../conf/system_config.php');
	require('../mvc/core/DBModule.php'); 
	require('../mvc/main/Admin_Model.php'); 
    require('../mvc/lib/PHPExcel_1.8.0_doc/Classes/PHPExcel.php');   
	
	class CatalogImport{
	  private $DB;
	  private $Task		  = '';
	  private $UploadRecord = array();
	
	  private $UploData	   = array();
	  private $FileData      = array();
	  private $MetaData      = array();
	  private $ProcessLogs   = array();
	  
	  private $MimeLimit     = array('xlsx','xls');
	 
      private $SheetNum         = 1;
	  private $ExcelFieldLength = 19;
	  private $StartRowNum      = 2;
	  private $TargetTable      = 'data_book_catalog';
	    
	  private $TimeStart     = '';
	
	  
      public function __construct($db,$task=0){
	    $this->DB  = $db;
	    $this->Task = $task;
	    $DB_Access = $db->DBLink->query("SELECT * FROM task_upload WHERE utkid=".$task." AND _upload!='' AND _process='';");
        if( $DB_Access->execute() && $upload = $DB_Access->fetchAll(PDO::FETCH_ASSOC) ){
	      $this->UploadRecord = $upload;
        }
	    $this->TimeStart = microtime(true); 
	  }	
	  
	  public function __destruct(){
	    echo microtime(true) - $this->TimeStart;; 
	  }
	  
	  
	  /*>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>*/
	  
	  
	  
	  //STEP01 -- check db record file && content is right
	  protected function prepareImport($UplRecord){
	  
	    try{ 
	    
		  $this->FileData      = array();
	      
		  // get record data & mark to excute 
		  $this->UploData = $UplRecord;
		  $this->DB->DBLink->query("UPDATE task_upload SET _process='".date('Y-m-d H:i:s')."' WHERE urno = ".$this->UploData['urno'].";");
		  
	      // check file 
	      $file_location = _SYSTEM_UPLD_PATH.'CATALOG/'.str_pad($this->UploData['urno'],4,'0',STR_PAD_LEFT).$UplRecord['hash'];
	      if(is_file($file_location) && filesize($file_location)==$UplRecord['size'] ){
		    $this->FileData['source'] = $file_location;
	      }else{
		    throw new Exception('上傳暫存錯誤');  	
		  }
	      
		  // check file type
		  if(!in_array($UplRecord['type'],$this->MimeLimit)){
			throw new Exception('檔案格式不符');  
		  }
		  
		  $this->FileData['filetemp'] = $UplRecord['hash'].'.'.$UplRecord['type'];
	      $this->FileData['filename'] = $UplRecord['name'];   
	      $this->FileData['filetype'] = $UplRecord['type'];   
		  
	      return true;
	  
	    } catch (Exception $e) {
          return $e->getMessage();
        }
	  }
	  
	  //STEP02 -- check db record file && content is right
	  private function activeImport(){
	    $meta = array();
	    $meta_end_fleg = 0;
	    		
		try{
			
		  $meta_insert = array(); 
		  
		  // read excel file
		  $objReader = PHPExcel_IOFactory::createReader('Excel2007');
		  $objPHPExcel = $objReader->load($this->FileData['source']);
		  for($sht=0 ;  $sht<$this->SheetNum ; $sht++ ){
		    
			$objSheet=$objPHPExcel->getSheet($sht);
		    
			$meta_read = array();
	        $row = $this->StartRowNum; 
		    $meta_end_fleg = 0;
			
			$meta_insert[$sht] = array(); 
			
			
			while( $meta_end_fleg < 10 ){
			  
			  $row++;
			  for($col=0 ; $col<$this->ExcelFieldLength; $col++){  
		        $field_value = trim($objSheet->getCellByColumnAndRow($col,$row)->getValue());
			    $meta_read[$col] = $field_value;

		        // 檢查欄位
		        /*
		        switch($col){
			      case 0 : if(!preg_match('/^\d{3}$/',$field_value))  $meta_error[] = $meta_field[$col].':格式錯誤';   break;    //"類號"
			      case 1 : if(!preg_match('/^\d{1}$/',$field_value))  $meta_error[] = $meta_field[$col].':格式錯誤';   break;    //"類號"
		        }
	            */
		      }
		      
		      if(!count(array_filter($meta_read))){
                $meta_end_fleg++;
				continue;
			  }			  
			  
			  $meta_dbfield = array();
			  $meta_dbfield['bno']			= NULL;
			  $meta_dbfield['bookid']		= $meta_read[0].'-'.$meta_read[1].'-'.$meta_read[2];
			  $meta_dbfield['council']		= $meta_read[4];
			  $meta_dbfield['book_type']	= $meta_read[3];
			  $meta_dbfield['period'] 		= $meta_read[5];
			  $meta_dbfield['book_name']	= $meta_read[6];
			  $meta_dbfield['language'] 	= $meta_read[7];
			  $meta_dbfield['keep_status'] = $meta_read[8];
			  $meta_dbfield['keep_source'] = $meta_read[9];
			  $meta_dbfield['keep_gatted'] = $meta_read[10];
			  $meta_dbfield['keep_time'] 	= $meta_read[11];
			  $meta_dbfield['keep_verson'] = $meta_read[12];
			  $meta_dbfield['storage_id'] 	= $meta_read[13];
			  $meta_dbfield['storage_locat'] = $meta_read[14];
			  $meta_dbfield['limit_tiff'] 	= $meta_read[15];
			  $meta_dbfield['limit_jpg'] 	= $meta_read[16];
			  $meta_dbfield['print_type'] 	= $meta_read[17];
			  $meta_dbfield['descrip'] 	= '';
			  $meta_dbfield['meta_count'] 	= 0;
			  $meta_dbfield['page_count'] 	= 0;
			  $meta_dbfield['cover'] 		= '';
			  $meta_dbfield['state'] 		= 'initial';
			  $meta_dbfield['keep'] 		= 1;
			  $meta_dbfield['create_info'] = $this->UploData['user'].'@'.date('Y-m-d H:i:s');
			  $meta_dbfield['update_time'] = NULL;
			  
			  $meta_insert[$sht][$row] = $meta_dbfield;
				 
			}
		  }
		  unset($objPHPExcel);
		  
		  // check data
		  $error_record = array();
		  foreach($meta_insert as $sht_no=>$sht_data){	  
			foreach($sht_data as $row=>$meta){
			  if(Admin_Model::CheckDataInputPattern('bookid',$meta['bookid'])){
				$error_record[] = array('time'=>date('Y-m-d H:i:s'),'mark'=>'ERROR','info'=>'S['.$sht_no.']/R['.$row.'] 序號錯誤('.$meta['bookid'].')');
			  }
			  $id_chk = $this->DB->DBLink->query("SELECT count(*) FROM ".$this->TargetTable." WHERE bookid='".$meta['bookid']."'")->fetchColumn();
			  if($id_chk){
				$error_record[] = array('time'=>date('Y-m-d H:i:s'),'mark'=>'ERROR','info'=>'S['.$sht_no.']/R['.$row.'] 序號重複('.$meta['bookid'].')');
			  }
			}
		  }
		  
		  if(count($error_record)){
			$this->ProcessLogs = $error_record;  
			throw new Exception('資料檢查錯誤');  	 
		  }
		  
		  //--  Insert Catalog Meta		  
		  $DB_INSERT = $this->DB->DBLink->prepare("INSERT INTO metadata VALUES(:bno,:bookid,:council,:book_type,:period,:book_name,:language,:keep_status,:keep_source,:keep_gatted,:keep_time,:keep_verson,:storage_id,:storage_locat,:limit_tiff,:limit_jpg,:print_type,:descrip,:meta_count,:page_count,:cover,:state,:keep,:create_info,:update_time);");
		  foreach($meta_insert as $sht_no=>$sht_data){
			foreach($sht_data as $row=>$meta){
			  foreach($meta as $field => &$value){
                $DB_INSERT->bindValue(':'.$field , $value);
              }
			  if($DB_INSERT->execute()){
			    $this->ProcessLogs[] = array('time'=>date('Y-m-d H:i:s'),'mark'=>'ALERT','info'=>$meta['bookid'].' inserted.'); 
			    echo $meta['bookid'].' inserted.'."/n";
			  
			  }else{
			    $this->ProcessLogs[] = array('time'=>date('Y-m-d H:i:s'),'mark'=>'ERROR','info'=>$meta['bookid'].' insert fail.');
			    //throw new Exception('導入meta資料失敗');
			  }
			}
		  }   
		    
		  copy($this->FileData['source'] , _SYSTEM_XLSX_PATH.'catalog/'.mb_convert_encoding($this->FileData['filename'].'.'.$this->FileData['filetype'],'BIG5','UTF-8')) ;
		  unlink($this->FileData['source']);
		
		  return true;
	    } catch (Exception $e) {
          return $e->getMessage();
        }  
	  }
	  
	  
	  //-- 錯誤紀錄
	  public function processfalse($falseLogs){
		$this->DB->DBLink->query("UPDATE task_upload SET _process='".date('Y-m-d H:i:s')."',_logs='".$falseLogs."' WHERE urno = ".$this->UploData['urno'].";");
		self::logsTask( 'ERROR',$falseLogs);
		if(count($this->ProcessLogs)){
		  foreach($this->ProcessLogs as $logs){
		    self::logsTask( $logs['mark'],$logs['info'],$logs['time']);
		  }	
		}
	  }
	
	  //-- 最終完成手續
	  public function finishImport(){
	    // 完成上載queue
		$this->DB->DBLink->query("UPDATE task_upload LEFT JOIN user_task ON utk=utkid SET task_done=(task_done+1),_archived='".date('Y-m-d H:i:s')."',_logs='finished' WHERE urno = ".$this->UploData['urno'].";");
	    return true;
	  }
	
	  //-- 最終任務
	  public function finishTask(){
        $this->DB->DBLink->query("UPDATE task_admin SET time_finish='".date('Y-m-d H:i:s')."' WHERE utk='".$this->Task."';");  
	    self::logsTask('ALERT','Task Finish.');
		return true;
	  }
	  
	  //-- LOGS Task
	  public function logsTask( $Status , $Info , $Time='' ){
		$Time = $Time ? $Time : date('Y-m-d H:i:s');  
        $this->DB->DBLink->query("INSERT INTO logs_task VALUES(NULL,".$this->Task.",'".$Time."','".$Status."','".$Info."','');");
	  }
	  
	  
	  //-- Main Process
	  public function processImport(){
	  
	    try{
		
          if(!count($this->UploadRecord)){
		    throw new Exception('目前無待輸入資料');  	
		  }   
        
		  foreach($this->UploadRecord as $ufile){
		  
		    echo $ufile['name'].":";
		  
		    //-- 讀取檔案
		    $check = $this->prepareImport($ufile);
		    if($check!==true){
			  self::processfalse($check);
			  echo $check;
			  continue;
		    }			  
		  
		    //-- 處理匯入
		    $active = $this->activeImport();
		    if($active!==true){
			  self::processfalse($active); 
			  echo $check;
              continue;
		    }     
		  
		    //-- 完成匯入手續 
		    $finish = $this->finishImport();
		    if($finish!==true){
			  echo $finish;
		    }
		    echo " done.\n";
		  }
		
		  $this->finishTask();
        
	    } catch (Exception $e) {
          echo $e->getMessage()."\n";
        }
	  }  
	}
	
	if(!isset($argv[1])){
	  echo "task no fail"; 
	  exit(1);  
    }
  
    $task_num = $argv[1];
    if(!intval($task_num)){
	  echo "task no fail";  
      exit(1);
    }
  
    $db = new DBModule;
    $db->db_connect('PDO');
    $import_update = new CatalogImport($db,$task_num);
    $import_update->processImport();
	
            



?>