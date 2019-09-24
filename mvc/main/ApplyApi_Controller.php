<?php
class ApplyApi_Controller extends Admin_Controller{
   
  /*
  *    Forest  AreaBooking System - Client Api Module
  *    API界接客戶端模組
  *      - Landing_Model.php
  * 
  */
  
	//-- 共用物件
	public  $IPSaveZoon;
	
	public  $ClientArgv;
	
	
	//初始化要執行的動作以及物件
	public function __construct(){
	  parent::__construct();	
      
	  // 處理遮蔽IP
	  //$this->IPSaveZoon = json_decode(_USER_IP_SAVE_ZOON,true);
	  //header('HTTP/1.0 400 Bad Request', true, 400);
	  //exit(1);
	  
	  // 處理model
	  
	  $this->Model = new Landing_Model;		
	  
	  $this->ClientArgv = [];
	  if(isset($_SERVER["CONTENT_TYPE"])){
		  // 處理content body參數
		  switch($_SERVER["CONTENT_TYPE"]){
			case 'application/json':
			case 'text/json':
				
				$contentbody = file_get_contents('php://input');
				if(isset($this->ActiveAction)){
					switch($this->ActiveAction){
						case 'signon': $this->ClientArgv['applicant'] = $contentbody; break;
						case 'applyform': $this->ClientArgv['application'] = $contentbody; break;
						case 'savembr':  $this->ClientArgv['member'] = $contentbody; break;
					}
				}
				break;
			case 'application/x-www-form-urlencoded':
				$this->ClientArgv = $_REQUEST;
				break;     
		  }  
	  } 
	}
	
	
	// 處理SESSION
	protected function session_restore($ApplyCode,$SessionKey){
		 
	  $session_path = _SYSTEM_CLIENT_PATH.$ApplyCode.'/session_'.$SessionKey;
	  
	  try{
		
		if(!file_exists($session_path)){
		  throw new Exception('_SYSTEM_ERROR_SESSION_EXPIRED');  
	    }
	    if(!$session_cache = json_decode(file_get_contents($session_path),true)){
		  throw new Exception('_SYSTEM_ERROR_SESSION_EXPIRED');  
	    }
		
		if(  !isset($session_cache['APPLYTOKEN']['KEY']) || 
		     (strtotime('now') - intval(str_replace(_SYSTEM_NAME_SHORT.':','',$session_cache['APPLYTOKEN']['KEY']))) > 86400 ){
	  	  throw new Exception('_SYSTEM_ERROR_SESSION_EXPIRED');		
        }
		
		return $session_cache;
	  
	  }catch(Exception $e){
		$this->Model->ModelResult[] = ['action'=>0,'message'=>[$e->getMessage()]];   
		self::data_output('json-oai','',$this->Model->ModelResult);
		exit(1);
	  }
	}
	
	
	
	// JSON: 申請區域清單 
	public function index(){   
	  $this->OpenAPI();
	}
	
	// JSON: 申請區域清單 
	public function paareas($Group=''){   
	  $this->Model->Access_Get_Active_Area_List($Group);
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	// JSON: 申請目標區域資訊 
	public function areainfo($AreaCode='',$CalendarStart='_now'){
	  $this->Model->Access_Get_Select_Area_Meta($AreaCode);
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	
	//json_decode(base64_decode(str_replace('*','/',rawurldecode($Applicant))),true); 
	
	
	// AJAX : 初始化申請
	public function signon($ApplyCode=''){
	  
	  $agentpost = isset($this->ClientArgv['applicant']) ? $this->ClientArgv['applicant'] : "[]";  
	  $ApplicantData = rawurlencode(str_replace('/','*',base64_encode($agentpost)));
	  
	  $this->Model->Apply_Record_SignOn($ApplicantData,$ApplyCode);
	  self::data_output('json-oai','',$this->Model->ModelResult);
	
	}
	
	
	// AJAX : 更新申請資料
	public function applyform($ApplyCode='',$LoginKey=''){     
	  
	  $agentpost = isset($this->ClientArgv['application']) ? $this->ClientArgv['application'] : "[]";
	  $ApplicationData = rawurlencode(str_replace('/','*',base64_encode($agentpost)));
	  
	  $session_config = $this->session_restore($ApplyCode,$LoginKey);
	  
	  $this->Model->Apply_Input_ApplyForm($ApplyCode,$ApplicationData,$session_config['APPLYTOKEN']);
	  $this->Model->Apply_Get_Member_Record($ApplyCode,$session_config['APPLYTOKEN']);
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 儲存成員名單
	public function savembr($ApplyCode='',$LoginKey=''){
		
	  $agentpost = isset($this->ClientArgv['member']) ? $this->ClientArgv['member'] : "[]";
	  $MemberString = rawurlencode(str_replace('/','*',base64_encode($agentpost)));	
		
	  $session_config =  $this->session_restore($ApplyCode,$LoginKey);
	  
	  $this->Model->Apply_Save_MbrEdit($ApplyCode,$MemberString,$session_config['APPLYTOKEN'],$session_config['APPLICANT']);
      self::data_output('json-oai','',$this->Model->ModelResult); 
	}
	
	
	// AJAX : 遞交申請資料
	public function submit($ApplyCode='',$LoginKey=''){
	  
	  $session_config = session_restore($ApplyCode,$LoginKey);
	  
	  $result = $this->Model->Apply_Record_Check($ApplyCode,$session_config['APPLYTOKEN']);
      self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	// AJAX : 取消申請資料
	public function cancel($ApplyCode,$LoginKey=''){   
	  
	  $session_config =  $this->session_restore($ApplyCode,$LoginKey);
	  
	  $this->Model->Apply_Record_Cancel($ApplyCode,$session_config['APPLYTOKEN']);
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	// JSON: 取得申請狀態 
	public function status($ApplyCode='',$LoginKey=''){
	  
	  $session_config =  $this->session_restore($ApplyCode,$LoginKey);
	  
	  $this->Model->Apply_License_Status_Rawdata($ApplyCode,$session_config['APPLYTOKEN']);	
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
	
	// AJAX: Download Apply License // 下載許可證 
	public function license($ApplyCode='',$LoginKey=''){
	  
	  $session_config =  $this->session_restore($ApplyCode,$LoginKey);
	  
	  $result = $this->Model->Apply_Record_Read( $ApplyCode, $session_config['APPLYTOKEN'] );
	  $active = $this->Model->Apply_Download_Check();
	  if(!$result['action'] || !$active['action']){
		self::data_output('json-oai','',$this->Model->ModelResult);    
	    exit(1);
	  }
	  $this->Model->Apply_Feform_Application_Page($ApplyCode,'license');
	  self::data_output('pdf','print_user_license',$this->Model->ModelResult);   
	}
	
	// JSON: OAS 規格 
	public function OpenAPI(){
	  $this->Model->OpenAPI_Config_Json();
	  self::data_output('json-oai','',$this->Model->ModelResult);
	}
	
    
	// 測試
	public function TestAPI($API,$Code='',$Key=''){
		//登入
		$applicant = '{"applicant_name":"HSIAO","applicant_userid":"T122806987","applicant_mail":"hsiaoiling@ntu.edu.tw","source":"MTAPI"}';
		$application = '{"area":{"code":"c6261eb8","inter":["測試進入範圍"],"gate":{"entr":"塔曼山","entr_time":"05:00:00","exit":"小烏來","exit_time":"18:00:00"}},"reason":[{"item":"民眾為環境教育之需要","limit":1}],"attach":[{"code":"C2E1B82C.pdf","time":"2019-09-24 11:24:17","file":"行政院及所屬各機關資訊安全管理要點"}],"dates":[["2019-10-09","2019-10-09"]],"fields":{"application_field_1":{"field":"一、請完整填寫申請當日行進路線【包含預計抵達時間及地點、並說明抵達後從事之行為種類】：","value":"1、08:00由滿月圓進入→10:30水源地→10:40木屋遺址→12:20插天山→13:30木屋遺址→13:40水源地→15:50由滿月圓離開。"},"application_field_2":{"field":"二、環境維護措施(垃圾、廢棄物處理方式)及環境教育內容簡介：","value":"1、全體隊員遵守進入管制規定及無痕山林規範。\n2、不隨意離開已開放供使用之步道及區域。"},"application_field_3":{"field":"三、緊急災難處理(應變相關裝備概述、辦理保險及撤退路線等說明)：","value":"1.裝備：無線電*4隻 (無線電頻率153.300)，地圖、指北針、高度計、手機、山刀、繩索、小急救包、登山\n定位GPS-550T、通訊裝備：手機數支等。\n2.保險:200萬意外,20萬意外醫療,保險期間:2月8日05:00時至2月9日05:00時。\n3.留守人員姓名：OOO、聯絡電話：OOOOOOOOO。\n4.撤退方式：如身體不適或天氣變化或路線受阻，立即原路撤退下山、選擇OOO路線撤退。"}}}';
		$member = '[{"member_role":"領隊","member_name":"HSIAO","member_id":"T122806987","member_birth":"1980-01-01","member_sex":"男","member_tel":"0999999999","member_cell":"0999999999","member_addr":"台北市","member_org":"單位1","member_contacter":"陳OO","member_contactto":"0911111111"},{"member_role":"成員","member_name":"陳XX","member_id":"A220133129","member_birth":"1981-01-01","member_sex":"女","member_tel":"0922222222","member_cell":"0922222222","member_addr":"台中市","member_org":"單位2","member_contacter":"陳OO","member_contactto":"0911111111"},{"member_role":"成員","member_name":"林ZZ","member_id":"A104698418","member_birth":"1982-01-01","member_sex":"男","member_tel":"0933333333","member_cell":"0933333333","member_addr":"台南市","member_org":"單位3","member_contacter":"陳OO","member_contactto":"0911111111"}]';
		
		$url = "http://localhost/ForestApply/webroot-client/ApplyApi/";
		
		$ch 	 = curl_init();
		$options = array(CURLOPT_URL => $url.$API.($Code?'/'.$Code:'').($Key?'/'.$Key:''),
                   CURLOPT_HEADER => 1,
                   CURLOPT_RETURNTRANSFER => true,
                   CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:10.0.2) Gecko/20100101 Firefox/10.0.2",
				   CURLOPT_REFERER   => "http://140.112.114.183",
				   CURLOPT_COOKIEFILE => 'D:\webroot\O_O\cookie2.txt',
                   CURLOPT_COOKIEJAR => 'D:\webroot\O_O\cookie2.txt',
                   CURLOPT_FOLLOWLOCATION => true ,
				   CURLOPT_SSL_VERIFYPEER => 0,
                   CURLOPT_SSL_VERIFYHOST => 2,
                   CURLOPT_CAINFO => getcwd() . "D:\webroot\O_O\CAcerts\GTECyberTrustGlobalRoot.crt",
				   CURLOPT_POST => 1,
                   CURLOPT_POSTFIELDS => "applicant=".$applicant
                  );
		curl_setopt_array($ch, $options);
		$output = curl_exec($ch);
		curl_close($ch);
		
		
		echo "<pre>";
		var_dump($output);
		
		var_dump($_POST);
		
		 
		
	}
	
	
	
	
	
	
}