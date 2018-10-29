<?php

  class Mailer_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	protected $SearchString;  // 查詢條件
	protected $ResultCount;   // 查詢結果數量
	protected $PageNow;       // 當前頁數 
	protected $LengthEachPage;// 每頁筆數
	protected $Metadata;
	
	
	/*[ Module Function Set ]*/ 
    
	//-- Admin Mailer Page Data OList 
	// [input] : $DataType   =  _all / wait / fail / sent;
	public function ADMailer_Get_Mailer_List($DataType){
	  
	  $result_key = parent::Initial_Result('records');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{   
		// 查詢資料庫
		
		switch($DataType){
		  case 'wait': $DB_OBJ = $this->DBLink->prepare(SQL_AdMailer::GET_MAILER_STATUS_JOBS());	$DB_OBJ->bindValue(':status',0); break;
		  case 'fail': $DB_OBJ = $this->DBLink->prepare(SQL_AdMailer::GET_MAILER_STATUS_JOBS());	$DB_OBJ->bindValue(':status',-1); break;
		  case 'sent': $DB_OBJ = $this->DBLink->prepare(SQL_AdMailer::GET_MAILER_STATUS_JOBS());	$DB_OBJ->bindValue(':status',1); break;
		  default: $DB_OBJ = $this->DBLink->prepare(SQL_AdMailer::GET_MAILER_JOBS()); break;
		}
		
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		// 取得列表
		$data_list = array();
		$data_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
		
		$result['action'] = true;		
		$result['data']['list']   = $data_list;		
	    $result['data']['type']   = $DataType;		
	    
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Meta Page Data OList 
	// [input] : $PagerMaxNum => int // 頁面按鈕最大數量
	public function ADMailer_Get_Page_List( $PagerMaxNum=1 ){
	  
	  $result_key = parent::Initial_Result('page');
	  $result  = &$this->ModelResult[$result_key];
      
	  try{
        
		$page_show_max = intval($PagerMaxNum) > 0 ? intval($PagerMaxNum) : 1;
		
	    $pages = array();
		
		$pages['all'] = array(1=>'');
		
		
		// 必要參數，從ADMeta_Get_Meta_List而來
		$this->ResultCount;   // 查詢結果數量
	    $this->PageNow;   
	    $this->LengthEachPage;
		
		$total_page = ( $this->ResultCount / $this->LengthEachPage ) + ($this->ResultCount%$this->LengthEachPage ? 1 :0 );
		
		// 建構分頁籤
		for($i=1;$i<=$total_page;$i++){
		  $pages['all'][$i] = (($i-1)*$this->LengthEachPage+1).'-'.($i*$this->LengthEachPage);
		}
		
		$pages['top']   = reset($pages['all']);
		$pages['end']   = end($pages['all']);
		$pages['prev']  = ($this->PageNow-1 > 0 ) ? $pages['all'][$this->PageNow-1] : $pages['all'][$this->PageNow];
		$pages['next']  = ($this->PageNow+1 < $total_page ) ? $pages['all'][$this->PageNow+1] : $pages['all'][$this->PageNow];
		$pages['now']   = $this->PageNow;  
		
		$check = ($page_show_max-1)/2;
		
	    if($total_page < $page_show_max){
		  $pages['list'] = $pages['all'];  	
		}else {  
          if( ($this->PageNow - $check) <= 1 ){    // 抓最前面 X 個
            $start = 0;
		  }else if( ($this->PageNow + $check) > $total_page ){  // 抓最後面 X 個
            $start = $total_page-(2*$check)-1;    
		  }else{
            $start = $this->PageNow - $check -1;
		  }
	      $pages['list'] = array_slice($pages['all'],$start,$page_show_max,TRUE);
		}
		
		$result['data']   = $pages;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Collect Page Initial  取得目前資料列表
	// [input] : RecordType   = (string) all ;
	// [input] : PageLimit    = (string) 1-10 / ;
	// [input] : SearchString = (string) base64_decode ;  [condition = [] , orderby? = [field: , mode: 0 1 2 ]]
	public function ADMailer_Get_Record_List($RecordType,$PageLimit,$SearchString){
	  
	  $result_key = parent::Initial_Result('records');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
	    
		
		// 處理頁數參數
		$limit = explode('-',$PageLimit);
        $limit_start   = 0;
        $limit_length  = 0;
		
        $limit_start = (isset($limit[0]) && intval($limit[0]) ) ? intval($limit[0])-1 : 0;
		$limit_length= (isset($limit[1]) && intval($limit[1]) && intval($limit[1]) > intval($limit[0]) ) ?  (intval($limit[1])-$limit_start) : 10;
		
		$record_count = 0;
		
		// 解析頁面建構參數
		$search_config = json_decode(base64_decode(str_replace('*','/',rawurldecode($SearchString))),true);
		 
		// 欄位參數
		$search_fields = [
		    'mail_type','mail_from','mail_to','mail_title','mail_content',
		];  
		
		// 條件參數		
		$search_condition=[''];  // 存放SQL條件，第一個為空白，用來串接SQL
		$search_patterns=[];     // 存放搜尋條件作為資料標示
		
        // 依據類型篩選資料
		switch($RecordType){
		  case 'all': break;
		  case 'sent': $search_condition[] = "_status_code='1'" ; break; 
		  case 'wait': $search_condition[] = "_status_code='0'" ; break; 
		  case 'fail': $search_condition[] = "_status_code='-1'" ; break; 
		  default:  $search_condition[] = "_status_code='".$RecordType."'" ; break; 
		}
		
		
		// 處理搜尋條件
		if($search_config && isset($search_config['condition']) && trim($search_config['condition']) ){
			$terms  = explode('&',$search_config['condition']);
			$querys = array();
			foreach($terms as $t){
			  $condition = array();
			  foreach($search_fields as $f){
				$condition[] =  $f." LIKE '%".$t."%'";
				$search_patterns[] = '/('.$t.')/u';
			  }	
			  $querys[] = "(".join(" OR ",$condition).")"; 
			}
			$search_condition[] = join(' AND ',$querys);  
		}	  
		  
        // 處理排序條件
        $order_by_config = 'ORDER BY _mail_date DESC,smno ASC '; 
		
		
		$DB_COUNT = $this->DBLink->prepare( SQL_AdMailer::SELECT_COUNT_RECORD($search_condition) );
		if(!$DB_COUNT->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	   	}
		
		$record_count = $DB_COUNT->fetchColumn();	
     	$record_list  = [];
		
		$DB_OBJ = $this->DBLink->prepare(SQL_AdMailer::SELECT_SEARCH_RECORD($search_condition,$order_by_config));
		$DB_OBJ->bindValue(':page_start',$limit_start,PDO::PARAM_INT);
		$DB_OBJ->bindValue(':page_length',$limit_length,PDO::PARAM_INT);
		 
		if(!$DB_OBJ->execute()){
	      throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
	   	}
		
		// 取得列表
		$record_list = array();
		$record_list = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);		
		 
		
		// 共用參數
		$this->ResultCount    = $record_count;
		$this->PageNow        = round($limit_start/$limit_length)+1;
		$this->LengthEachPage = $limit_length;
		
		$result['action'] = true;		
		$result['data']['type']   = $RecordType;
		$result['data']['list']   = $record_list;		
	    $result['data']['count']  = $record_count;		
	    $result['data']['config'] = $search_config;		
	    $result['data']['limit']  = array('start'=>$limit_start,'length'=>$limit_length,'range'=> '1-'.$limit_length);	
       	$result['data']['nums']   = $limit_length;
        $result['data']['page']   = $PageLimit;		
	    
	  } catch (Exception $e) {
		$result['message'][] = $e->getMessage();
      }
	  
	  return $result;
	}
	
	
	
	
	
	
	//-- Admin Mailer Get Mail Data 
	// [input] : uno  :  \d+;
	
	public function ADMailer_Get_Mailer_Data($DataNo=0){
		
	  $result_key = parent::Initial_Result('record');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	   
		// 取得公告資料
		$mail_data = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdMailer::GET_MAIL_DATA() );
		$DB_GET->bindParam(':smno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$mail_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
	    
		$mail_data['mail_content'] = htmlspecialchars_decode($mail_data['mail_content'],ENT_QUOTES);
		$mail_data['_active_logs'] = json_decode($mail_data['_active_logs'],true);
		
		// final
		$result['action'] = true;
		$result['data'] = $mail_data;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Mail Save Mail Data 
	// [input] : DataNo    :  \d+  = DB.system_mailer.smno;
	// [input] : DataModify  :   urlencode(base64encode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	
	public function ADMailer_Save_Mailer_Data( $DataNo=0 , $DataModify=''){
	  
	  $result_key = parent::Initial_Result('save');
	  $result  = &$this->ModelResult[$result_key];
	  $data_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($DataModify))),true);
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$DataNo)  || !is_array($data_modify)  ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$mail_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdMailer::GET_MAIL_DATA());
		$DB_GET->bindParam(':smno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$mail_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 檢查更新欄位是否合法
		foreach($data_modify as $mf => $mv){
		  if(!isset($mail_data[$mf])){
		    throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		  }  
		  if($mf == 'mail_content'){
			$data_modify[$mf] = htmlspecialchars($mv,ENT_QUOTES,'UTF-8');  
		  }
		  
		  if($mf == '_mail_date' &&  strtotime($mv.' 23:59:59') < strtotime('+4 hour') ){
			throw new Exception('_MAILER_MAIL_DATE_EXPIRED');  
		  }
		}
		
		if($data_modify && count($data_modify)){
			
		  $mail_logs = json_decode($mail_data['_active_logs'],true);
		  $mail_logs[date('Y-m-d H:i:s')] = 'Mail Update by '.$this->USER->UserID;
		  $data_modify['_active_logs'] = json_encode($mail_logs);	
			
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdMailer::UPDATE_MAIL_DATA(array_keys($data_modify)));
		  $DB_SAVE->bindValue(':smno' , $DataNo);
		  foreach($data_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }
		}
		
		// final 
		$result['data']   = $DataNo;
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	//-- Admin Mailer Delete Mail Data 
	// [input] : DataNo  :  \d+;
	public function ADMailer_Del_Mailer_Data($DataNo=0){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		
		// 檢查資料序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
	    
		// _keep => 0
		$DB_SAVE	= $this->DBLink->prepare( SQL_AdMailer::UPDATE_MAIL_DATA(array('_keep')));
		$DB_SAVE->bindParam(':smno'  , $DataNo , PDO::PARAM_INT);
		$DB_SAVE->bindValue(':_keep' , 0 );
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		
		// final 
		$result['data']   = $DataNo;
		$result['action'] = true;
		sleep(1);
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	//-- Admin Mailer Sent Mail Now 
	// [input] : DataNo  :  \d+;
	public function ADMailer_Mail_Sent_Now($DataNo=0){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^\d+$/',$DataNo)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$mail_data = NULL;
		$DB_GET	= $this->DBLink->prepare(SQL_AdMailer::GET_MAIL_DATA());
		$DB_GET->bindParam(':smno'   , $DataNo , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$mail_data = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 設定信件內容
        $to_sent 		= $mail_data['mail_to'];
        $mail_error 	= '';
		$mail_content	= htmlspecialchars_decode($mail_data['mail_content'],ENT_QUOTES);
        $mail_title		= $mail_data['mail_title'];
        
		$mail_logs      = json_decode($mail_data['_active_logs'],true);
		$mail_logs[date('Y-m-d H:i:s')] = 'Send mail by '.$this->USER->UserID ;
		
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
		  //$mail->AddAddress('','');
          
		  $mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$to_sent,$mail_paser)) ? trim($mail_paser[1]) : trim($to_sent);
		  if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
		    throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
		  }
		  
		  $mail->AddAddress($mail_to_sent,'');
		  $mail->SetFrom( $mail_data['mail_from'] , _SYSTEM_MAIL_FROM_NAME);
		  $mail->AddReplyTo($mail_data['mail_from'] , _SYSTEM_MAIL_FROM_NAME); // 回信位址
		  $mail->Subject = $mail_title;
		  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; // optional - MsgHTML will create an alternate automatically
		  $mail->MsgHTML($mail_content);
		  
		  //$mail->AddCC(); 
		  //$mail->AddAttachment('images/phpmailer.gif');      // attachment
	      if(!$mail->Send()) {
			throw new Exception($mail->ErrorInfo);  
		  }  
		  sleep(3);
		  
		  // final 
		  $result['data']   = $mail_data['mail_to'];
		  $result['action'] = true;
		
		} catch (phpmailerException $e) {
		  $mail_error = $e->errorMessage();
		  $result['message'][] = $e->errorMessage();  //Pretty error messages from PHPMailer
		} catch (Exception $e) {
		  $mail_error = $e->errorMessage();
		  $result['message'][] = $e->errorMessage();  //echo $e->getMessage(); //Boring error messages from anything else!
		}
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  // final 
	  $mail_logs[date('Y-m-d H:i:s')] = $mail_error ?  'SENT MAIL Fails:'.$mail_error : 'SENT MAIL SUCCESS.';
	  $fail_array = array_map(function($logs){ return preg_match('/SENT MAIL Fails/',$logs)?1:0; },array_values($mail_logs));
	  $status = $mail_error=='' ? 1 : -1;  
	  $DB_UPD = $this->DBLink->prepare(SQL_AdMailer::UPDATE_MAIL_DATA(array('_active_logs','_status_code','_active_time','_result')));
	  $DB_UPD->bindValue(':_status_code' 	, $status , PDO::PARAM_INT);	
	  $DB_UPD->bindValue(':_active_time' 	, date('Y-m-d H:i:s'));
	  $DB_UPD->bindValue(':_result'  		, $mail_error ? $mail_error:'Mail Sent By '.$this->USER->UserID);
	  $DB_UPD->bindValue(':_active_logs'  	, json_encode($mail_logs));
	  $DB_UPD->bindValue(':smno'    		, intval($DataNo));
	  $DB_UPD->execute();
	  
	  return $result;  
	}
	
	
	//-- Landing regist mail 
	// [input] : DataCode  :  string;  // apply_code
	public function Mailer_Regist_Mail($DataCode=0 ){
	  
	  $result_key = parent::Initial_Result('mail');
	  $result  = &$this->ModelResult[$result_key];
	  try{  
		
		// 檢查序號
	    if(!preg_match('/^[\d\w]{8}$/',$DataCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 取得資料
		$booking = NULL;
		$DB_GET	= $this->DBLink->prepare( SQL_AdBook::GET_BOOKING_RECORD() );
		$DB_GET->bindParam(':abno'   , $DataCode , PDO::PARAM_INT);	
		if( !$DB_GET->execute() || !$booking = $DB_GET->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 取得區域資料
		$area_meta = array();
		$DB_AREA= $this->DBLink->prepare( SQL_AdBook::GET_AREA_META() );
		$DB_AREA->bindParam(':ano'   , $booking['am_id'] );	
		if( !$DB_AREA->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		$area_meta = $DB_AREA->fetch(PDO::FETCH_ASSOC);
		
		// 解析訊息
		$progress = json_decode($booking['_progres'],true);
		$application = json_decode($booking['apply_form'],true);
		
		// 設定通知信種類
		$mail_title_type  = '';
		$mail_status_type = '';
		$mail_status_info = '';
		
		switch( $booking['_stage'] ){
		  case 1: 
		    $mail_title_type  = '受理通知'; 
		    $mail_status_type = '已確認收到申請資料，進入待審程序';
		    break;
          
		  case 3: 
		    $mail_title_type = '審查通知'; 
		    if(isset($progress['client'][$booking['_stage']])){
			  $message_conf = array_pop($progress['client'][$booking['_stage']]);
			  $mail_status_type = $message_conf['status'];
			  $mail_status_info = $message_conf['note'];	
			}
			break;
			
		  case 5: $mail_title_type = '結果通知'; 
		    if(isset($progress['client'][$booking['_stage']])){
			  $message_conf = array_pop($progress['client'][$booking['_stage']]);
			  $mail_status_type = $message_conf['status']; 
			  $mail_status_info = $message_conf['note'];	
			}
			break;
		  default: $mail_title_type = '通知';break;
		}
		
		// 設定信件內容
        $to_sent = $booking['applicant_mail'];
        $mail_title = _SYSTEM_HTML_TITLE." / ".$mail_title_type." / 申請編號:".$booking['apply_code'];        
		
        $mail_content  = "<div>".$booking['applicant_name']." 您好：</div>";
		$mail_content .= "<div>台端於 <strong>".$booking['apply_date']."</strong> 申請進入『".$area_meta['area_name']."』 </div>";
		$mail_content .= "<div>申請狀態：".$mail_status_type."</div>";
		if($mail_status_info){
		  $mail_content .= "<div >訊息通知：<span style='color:red;font-weight:bold;'>".$mail_status_info."</span></div>";	
		}
		
		// 註冊存取序號
		$license_access_key = hash('sha1',$booking['applicant_name'].'雜'.$booking['apply_code'].'湊'.$booking['applicant_id']);
		
		$mail_content .= "<div>申請連結："._SYSTEM_SERVER_ADDRESS.'index.php?act=Landing/direct/'.$booking['apply_code'].'/'.$license_access_key."</div>";
		
		$mail_content .= "<div> <br/> </div>";
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
		$mail_content .= "<div>四、為維護自然生態，各保護區設有進入人數之承載量管制，若申請截止日(依據各區設定)總人數逾越承載量，系統將進行隨機抽籤，並發給審查通知或結果通知等狀態之電子郵件，請隨時留意通知內容，有資料不全通知補件時，應盡速補件，未於期限內補件者將予以駁退。已通知核准進入者，請登入申請單查詢頁面下載許可證，未出示許可證者禁止進入自然保護區域。。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>※本郵件由系統自動發送，請勿直接回覆，如有任何問題，請洽各區域管理機關(構)查詢。</div>";
		$mail_content .= "<div> </div>";
		$mail_content .= "<div>林務局"._SYSTEM_HTML_TITLE." 敬啟</div>";
		$mail_content .= "<div><a href='"._SYSTEM_SERVER_ADDRESS."' target=_blank >"._SYSTEM_SERVER_ADDRESS."</a></div>";
		      			  
        
		// 註冊信件工作
		$mail_to_sent = (preg_match('/.*?\s*<(.*?)>$/',$to_sent,$mail_paser)) ? trim($mail_paser[1]) : trim($to_sent);
		if(!filter_var($mail_to_sent, FILTER_VALIDATE_EMAIL)){
		  throw new Exception('_LOGIN_INFO_REGISTER_MAIL_FALSE');
		}
		
		$mail_logs = [date('Y-m-d H:i:s')=>'Regist Alert Mail From ['.$booking['apply_code'].'].' ];
		
		$DB_MAILJOB	= $this->DBLink->prepare(SQL_AdMailer::REGIST_MAIL_JOB());
		$DB_MAILJOB->bindValue(':mail_type',$mail_title_type);
		$DB_MAILJOB->bindValue(':mail_from',_SYSTEM_MAIL_ACCOUNT_USER.'@'._SYSTEM_MAIL_ACCOUNT_HOST);
		$DB_MAILJOB->bindValue(':mail_to',$mail_to_sent);
		$DB_MAILJOB->bindValue(':mail_title',$mail_title);
		$DB_MAILJOB->bindValue(':mail_content',htmlspecialchars($mail_content,ENT_QUOTES,'UTF-8'));
		$DB_MAILJOB->bindValue(':creator',$this->USER->UserID);
		$DB_MAILJOB->bindValue(':editor',$this->USER->UserID);
		$DB_MAILJOB->bindValue(':mail_date',date('Y-m-d'));
		$DB_MAILJOB->bindValue(':active_logs',json_encode($mail_logs));
		if(!$DB_MAILJOB->execute()){
		  throw new Exception('_APPLY_MAIL_REGIST_FAIL');	
		}
		
		// final 
		$result['data']['maildate']   = date('Y-m-d');
		$result['data']['mailuser']   = $booking['applicant_name'].'('.$booking['applicant_mail'].')';
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	
	
	
	
  }
?>