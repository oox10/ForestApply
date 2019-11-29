<?php
  
  /* 
  發信機器人
  
  時間：每日
  頻率：10min
  對象：所有信件列表
  
  */
  
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  require_once(dirname(dirname(__FILE__)).'/mvc/dbsql/SQL_AdJobs.php');   
  
  require ROOT.'mvc/lib/vendor/autoload.php';
  
  $result  = array('action'=>false,'data'=>array(),'message'=>array());
  $logs_file = ROOT.'/systemJobs/jobs.logs';
  $logs_message = date("Y-m-d H:i:s").' [TASK] SYSTEM MAILJOBS START!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
  try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
	
	$MailType = isset($argv[1]) ? $argv[1] : 'APPLY';
	$MailDate = isset($argv[2]) ? $argv[2] : date('Y-m-d');
	
	// 取得本日發信工作
	$mail_jobs = NULL;
	$DB_GET	= $db->DBLink->prepare(SQL_AdJobs::GET_MAIL_JOBS());
	$DB_GET->bindValue(':mail_date'   , strtotime($MailDate) ? date('Y-m-d',strtotime($MailDate)) : date('Y-m-d'));
	if( !$DB_GET->execute() ){
	  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
	}
	
	while( $mail_job = $DB_GET->fetch(PDO::FETCH_ASSOC) ){
	  // 設定信件內容
	  $logs_message = date("Y-m-d H:i:s")." [TASK] SENT MAIL [".$mail_job['mail_to']."]: ";	  
	  $to_sent      = explode(';',$mail_job['mail_to']);		  
	  $mail_title   = $mail_job['mail_title'];
	  $mail_content = htmlspecialchars_decode($mail_job['mail_content'],ENT_QUOTES);
	  $mail_from    =  $mail_job['mail_from'];
	  $mail_logs    = json_decode($mail_job['_active_logs'],true);
	  
	  
	  
	  
	  switch($mail_job['mail_method']){
		case 'hike':
             
			$api_address  = _HIKE_MAILAPI_SERVER_TEST; //_HIKE_MAILAPI_SERVER_PATH
			$mail_submit  = [];
			
			try{
			
				$ch = curl_init();
				$options = array(CURLOPT_URL => $api_address,
						   CURLOPT_HEADER => false,
						   CURLOPT_NOBODY => false,
						   CURLOPT_RETURNTRANSFER => true,
						   CURLOPT_USERAGENT 	=> "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36",
						   CURLOPT_COOKIEFILE	=> _SYSTEM_ROOT_PATH.'logs\cookie.txt',
						   CURLOPT_COOKIEJAR 	=> _SYSTEM_ROOT_PATH.'logs\cookie.txt',
						   CURLOPT_FOLLOWLOCATION => true ,
						   CURLOPT_SSL_VERIFYPEER => 0,
						   CURLOPT_SSL_VERIFYHOST => 2,
						   CURLOPT_CAINFO => getcwd() . _SYSTEM_ROOT_PATH."logs\GTECyberTrustGlobalRoot.crt",
						   CURLINFO_HEADER_OUT=> true,
						   CURLOPT_VERBOSE=>true
						  );
				
				$options[CURLOPT_POST] = 1;
				$options[CURLOPT_HTTPHEADER] = array('Content-Type: application/json');
				
				$mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$to_sent,$mail_paser)) ? trim($mail_paser[1]) : trim($to_sent);
				if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
					throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
				}
				
				$mail_submit['mail_to']		= $mail_to_sent;
				$mail_submit['mail_title']	= $mail_title;
				$mail_submit['mail_content']= $mail_content;
				
				$options[CURLOPT_POSTFIELDS] = json_encode($mail_submit);		
				curl_setopt_array($ch, $options);
				$active_result = curl_exec($ch);
				
				if(!$active_result){
					throw new Exception('API送信未成功');
				}
				
				if(!$apiresult = json_decode($active_result,true)){
					throw new Exception('API回傳無法解析:'.$active_result);
				}
				
				if(!isset($apiresult['action']) || !intval($apiresult['action'])){
					throw new Exception('API回覆失敗:'.$apiresult['info']);
				}
				
			    
				// final 
				$logs_message .= 'success.'.PHP_EOL;
				file_put_contents($logs_file,$logs_message,FILE_APPEND);
				echo $logs_message;
				sleep(2);
			
			} catch (Exception $e) {
			    $mail_error = $e->getMessage();  //echo $e->getMessage(); //Boring error messages from anything else!
				$logs_message .= "fail:".$mail_error.PHP_EOL;
				file_put_contents($logs_file,$logs_message,FILE_APPEND);
				echo $logs_message;
			}
			
			break;		
		

        default: 
			
			$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
			$mail->IsSMTP(); // telling the class to use SMTP 
			$mail->SMTPOptions = array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				)
			);
			$mail->SMTPDebug  = 0;
			$mail_error = '';
			  
			try {  
				
				$mail->SMTPAuth   = _SYSTEM_MAIL_SMTPAuth;   // enable SMTP authentication      
				if(_SYSTEM_MAIL_SSL_ACTIVE){
				  $mail->SMTPSecure = _SYSTEM_MAIL_SECURE;   // sets the prefix to the servie
				}
				$mail->Port       = _SYSTEM_MAIL_PORT;     // set the SMTP port for the GMAIL server
				$mail->Host       = _SYSTEM_MAIL_HOST; 	   // SMTP server
				$mail->CharSet 	= "utf-8";
				$mail->Username   = _SYSTEM_MAIL_ACCOUNT_USER;  // MAIL username
				$mail->Password   = _SYSTEM_MAIL_ACCOUNT_PASS;  // MAIL password
				
				foreach($to_sent as $mail_to){
				  //$mail->AddAddress('','');
				  $mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$mail_to,$mail_paser)) ? trim($mail_paser[1]) : trim($mail_to);
				  if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
					throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
				  }
				  $mail->AddAddress($mail_to_sent,'');	
				}
				
				$mail->SetFrom( $mail_from, _SYSTEM_MAIL_FROM_NAME);
				$mail->AddReplyTo( $mail_from, _SYSTEM_MAIL_FROM_NAME); // 回信位址
				$mail->Subject = $mail_title ;
				$mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
				$mail->MsgHTML($mail_content);
			  
				//$mail->AddCC(); 
				//$mail->AddAttachment('images/phpmailer.gif');      // attachment
				
				if(!$mail->Send()) {
				  throw new Exception($mail->ErrorInfo);  
				}  
				
				$logs_message .= 'success.'.PHP_EOL;
				file_put_contents($logs_file,$logs_message,FILE_APPEND);
				echo $logs_message;
				sleep(2);
				
			} catch (phpmailerException $e) {
				$mail_error = $e->errorMessage();  //Pretty error messages from PHPMailer
				$logs_message .= "fail:".$mail_error.PHP_EOL;
				file_put_contents($logs_file,$logs_message,FILE_APPEND);
				echo $logs_message;
				
			} catch (Exception $e) {
				$mail_error = $e->getMessage();  //echo $e->getMessage(); //Boring error messages from anything else!
				$logs_message .= "fail:".$mail_error.PHP_EOL;
				file_put_contents($logs_file,$logs_message,FILE_APPEND);
				echo $logs_message;
			}    
			
			
			break;		
		
	  }
	  
	  
	  
	  
	  
	  
	  
	  
	 
	  
	  // final 
	  $mail_logs[date('Y-m-d H:i:s')] = $mail_error ?  'SENT MAIL Fails:'.$mail_error : 'SENT MAIL SUCCESS.';
	  $fail_array = array_map(function($logs){ return preg_match('/SENT MAIL Fails/',$logs)?1:0; },array_values($mail_logs));
	  $status = $mail_error=='' ? 1 : (array_sum($fail_array) >= 5 ? -1:0) ;  
	  $DB_UPD = $db->DBLink->prepare(SQL_AdJobs::UPDATE_MAIL_JOBS());
	  $DB_UPD->bindValue(':status'  , $status , PDO::PARAM_INT);	
	  $DB_UPD->bindValue(':acttime' , date('Y-m-d H:i:s'));
	  $DB_UPD->bindValue(':result'  , $mail_error ? $mail_error:'Mail Sent');
	  $DB_UPD->bindValue(':activelogs'  , json_encode($mail_logs));
	  $DB_UPD->bindValue(':smno'    , $mail_job['smno']);
	  $DB_UPD->execute();
	}
  } catch (Exception $e) {
	$result['message'][] = $e->getMessage();
  }
   
  $logs_message = date("Y-m-d H:i:s").' [TASK] SYSTEM MAILJOBS FINISH!.'.PHP_EOL;
  file_put_contents($logs_file,$logs_message,FILE_APPEND);
  echo $logs_message;
  
?>