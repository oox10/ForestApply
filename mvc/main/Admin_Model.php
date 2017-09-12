<?php
  class Admin_Model extends DBModule{
    
	/***--  Initial  --***/
	public	$ModelResult;  //save model result data 
	protected  $USER;
	
	public function __construct(){
	  $this->db_connect();
	  $this->ModelResult    = array();									 
	}
	
	public function initial_user($UserObjectSerialize){  
	  $UserObjectSerialize; // FROM SESSION $_SESSION[_SYSTEM_NAME_SHORT]['xxxxx']['USER']
	  $user_object = unserialize($UserObjectSerialize);	
	  $this->USER  = ($user_object) ? $user_object : array('UserNO'=>0,'UserID'=>'GUEST','UserIP'=>'0.0.0.0');	
	}
	
	public function __destruct(){
      $this->DBLink 	= NULL;
	}
	
	/*[ Structure Function Set ]*/ 
	
	
	//-- Option func result initial 
	/*
	    定義處理method 回傳資料格式，$this->ModelResult[xxx] = array();
		若未給予名稱, 則預設為  0 
	*/
	public function Initial_Result($SetName=''){
	  if( strlen($SetName) && !isset($this->ModelResult[$SetName])){
		$this->ModelResult[$SetName] = array('action'=>false,'message'=>array(),'data'=>array());     
	    $result_tag = $SetName;
	  }else{
		$result_tag = count($this->ModelResult);
		$this->ModelResult[$result_tag] = array('action'=>false,'message'=>array(),'data'=>array()); 
	  }
	  return $result_tag;
	}
	
	
	/*[ Data Function Set ]*/ 
	
	
	//-- Method Return Message Code to String  // 系統訊息輸出
	public function Get_Action_Message($message,$langCode='cht'){
	  if(is_array($message)){
		return self::Message_Translate(join('；',$message),$langCode); 		
	  }else{
		return self::Message_Translate($message,$langCode);   		
	  }
	}
	
	//-- Message Code to String  // 系統訊息翻譯
	// [input] : $MessageCode  : string (concat width；)
	// [input] : $LangCode     : cht / eng 
	public function Message_Translate($MessageCode=NULL,$LangCode='cht'){
	  
	  $INFO_MESSAGE_MAP = array();  // 訊息對應表
	  $message_return   = array();  // 訊息轉換結果
	  
	  $error_set = explode('；',$MessageCode);
	  
	  if(!count($error_set)){
		return array();    
	  }
	  
	  $ConfigFile = _SYSTEM_ROOT_PATH.'conf/info_'.$LangCode.'.conf'; 
	  if(!is_file($ConfigFile)){
	    return "WARNING:Can not found info.".$LangCode.".conf.";
	  }
	  
	  //載入訊息設定檔
	  $conf_handle = @fopen($ConfigFile,'r');
	  if (!$conf_handle) {
		return "WARNING:Can not open info.".$LangCode.".conf.";  
	  }
            
	  while (($buffer = fgets($conf_handle)) !== false) {
        $Message_Map_String = trim($buffer);	  
		if(!preg_match('/^(#|\[)/',$Message_Map_String) && strlen($Message_Map_String)){
          list($mcode,$mlang) = explode('=',$Message_Map_String) ;
		  if(isset($mcode) && isset($mlang)){
			$INFO_MESSAGE_MAP[trim($mcode)] = trim($mlang); 
		  }
	    }
      }
      fclose($conf_handle);	 
	  
	  foreach($error_set as $err_code){
		$message_return[] = isset($INFO_MESSAGE_MAP[$err_code]) ? $INFO_MESSAGE_MAP[$err_code] : "WARNING:".$err_code.".";    
	  }
      
	  return join("\n",$message_return);    
	}
	
	
	// 取得登入者帳號相關資訊
	public function GetUserInfo(){
	  $result_key = self::Initial_Result('user');
	  $this->ModelResult[$result_key]['data']['user']   = $this->USER->UserInfo;
	  $this->ModelResult[$result_key]['data']['signin'] = $this->USER->UserID;
	  $this->ModelResult[$result_key]['data']['permission'] = $this->USER->PermissionNow;
	  $this->ModelResult[$result_key]['data']['group']=array();
	  foreach($this->USER->PermissionQue as $gid => $gset){
		$this->ModelResult[$result_key]['data']['group'][] = array('id'=>$gid,'name'=>$gset['group_name'],'roles'=>$gset['group_roles'],'now'=> $this->USER->PermissionNow['group_code']==$gid ? 1 : 0 );
		if($this->USER->PermissionNow['group_code']==$gid){
		  $this->ModelResult[$result_key]['data']['user']['user_group'] = $gset['group_name'];
          $this->ModelResult[$result_key]['data']['user']['user_roles'] = $gset['group_roles'];
		}
	  }
      $this->ModelResult[$result_key]['data']['login'] = $this->USER->LoginTime;
	  
	  /*
	  // 取得工作資料  apply
	  if( isset($this->USER->PermissionNow['group_roles']['R00']) || 
		 (isset($this->USER->PermissionNow['group_roles']['R03']) && $this->USER->PermissionNow['group_roles']['R03'] > 1) ){
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_AdApply::SELECT_ADMIN_APPLY_TASKS());
	    $DB_OBJ->execute();
		$count = $DB_OBJ->fetchcolumn();
	  }else{
		// 查詢資料庫
		$DB_OBJ = $this->DBLink->prepare(SQL_AdApply::SELECT_USER_APPLY_TASKS());	
		$DB_OBJ->bindValue(':user_no',$this->USER->UserNO);
		$DB_OBJ->execute();
		$records = $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		$count   = count($records);
	  } 
	  $this->ModelResult[$result_key]['data']['apply_task'] = $count;
	  */
	  $this->ModelResult[$result_key]['action'] = true;
	}
	
	
	// 取得登入者帳號資訊
	public function GetUserGroups(){
	  $result_key = self::Initial_Result('group');
	  $user_groups= array();
	  
	  if(isset($this->USER->PermissionQue)){
		foreach($this->USER->PermissionQue as $gid=>$gset){
		  
		  if($gset['master']){
			$user_groups = array($gid=>$gset['group_name'])+$user_groups;   
		  }else{
			$user_groups = $user_groups+array($gid=>$gset['group_name']);  
		  }
		  //$user_groups[$gid] = $gset['group_name'];
		}  
	  }
	  $this->ModelResult[$result_key]['data']   = $user_groups;
	  $this->ModelResult[$result_key]['action'] = true;
	}
	
	
	/******************************************
	  取得 metadata 設定
		參數   
		  1. $UiLanguage   顯示語言
		回傳
		  $MetaConfig
        提醒
          $this->MetaConf 定義於一開始
		  
	******************************************/
	public function Get_Meta_Setting($UiLanguage = 'cht'){
	  $MetaConfig = array();
	  if(defined('_SYSTEM_META_CONFIG')){
	  
	    $MetaConfig = json_decode(_SYSTEM_META_CONFIG,true);
	    foreach( $MetaConfig  as $MetaFieldCode => $MetaFieldSet){
		  $MetaConfig[$MetaFieldCode]['DisplayValue'] = self::Message_Translate('_SYSTEM_FIELD_'.$MetaFieldSet['FieldName'],$UiLanguage); 
	    }
	  }
	  return $MetaConfig;
	}
	
	
	/*[ Priority Function Set ]*/ 

	
	
	//-- user group now switch
	// [input] : $GroupCode  : (String) adm/tpc/rcdh ...
	public function Group_Now_Switch ($GroupCode=''){
	    
	  $result_key = $this->Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
        if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER'])){
		  throw new Exception('_LOGIN_INFO_ACCOUNT_UNLOGIN');  
		}
        
		if(!isset($this->USER->PermissionQue[$GroupCode])){
		  throw new Exception('_ACCOUNT_OUT_OF_GROUP'); 
		}
		
		$this->USER->PermissionNow = $this->USER->PermissionQue[$GroupCode];
		$_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER'] = serialize($this->USER);
		$result['action'] = true; 
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
	  return $result;
	}
	
	
	
    //-- Check user role can active action
	// [input] : $ActClass  : controller name
	// [input] : $ActMethod : action name 
	public static function Role_Action_Filter ($ActClass='',$ActMethod=''){
	    
	  $permission = true;
		
	  try{
		
        if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER'])){
		  throw new Exception('not login');  
		}
        
		$user_login = unserialize($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
		$action_map = isset($user_login->PermissionNow['group_action']) ? $user_login->PermissionNow['group_action'] :  array();
		
		if( isset($action_map[$ActClass])  &&  !in_array( $ActMethod , $action_map[$ActClass]) ){
	      $permission = false;     
		}
		
	  } catch (Exception $e) {
        //$result['message'][] = $e->getMessage();
		$permission=true;
      }
	  
	  return $permission;
	}
	
	
	
	//-- ReBuilt Sql String Width Target Permission Rule Set
	// [input]  : QuerySQLString 	: SQL String Pattern ;
	// [return] : QuerySqlWidthPermission : [string] ;
	public function SQL_Permission_Filter($QuerySQLString){
	
	  $user_pri_map = isset($this->USER->PermissionNow['group_filter']) ? $this->USER->PermissionNow['group_filter'] : array();
	  $sql_width_permission = $QuerySQLString; // initial
	
	  if(preg_match("/(FROM|UPDATE|DELETE)\s+([\w\_\d]+)\s+(LEFT.*|SET.*)?WHERE/",$QuerySQLString,$pattern)){
		$table_name = $pattern[2];
        if(isset($user_pri_map[$table_name])){
		  $filter_condition = '('.join(' OR ',$user_pri_map[$table_name]).')';
		  $sql_width_permission = str_replace($pattern[0],$pattern[0].' '.$filter_condition.' AND' ,$QuerySQLString); 
		} 
	  }
	  
	  if(LOGS_FILTER_SQL_LOGS_FLAG){
		file_put_contents(_SYSTEM_SQL_FILTER_LOGS, date('c').'		'.$sql_width_permission."\n",FILE_APPEND);  
	  }
	  
	  
	  
	  return $sql_width_permission; 
	}
	
	
	
	//-- System Input Filter  // 查驗輸入資料
	public static function CheckDataInputPattern($pattern,$input){
    
      switch($pattern){
		
        // DB.data_book_catalog
		case 'bookid' : $pattern='^\d{3}\w\-\d\d\-\d\d$'; break;
        
		default: $pattern='^=$';
	  }	  
	  return preg_match('/'.$pattern.'/',$input);
	  
	}
	
	
	//-- Tool : show user login account session  // 檢視目前帳號session結構
	public function Tool_Display_User_Account_Session(){
	  echo "<pre>";
	  var_dump($this->USER);
	  exit(1);
	}
	
	
	/*[ System work Function Set ]*/ 
	
	//-- System Action Used Logs
	// [input]  : active function 	: class::method ;
	public function System_Logs_Used_Action($ControlerAction=''){	
	  $acc_url = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] : "comment line";	 
	  $acc_ip  = System_Helper::get_client_ip();
	  $DB_OBJ = $this->DBLink->prepare( SQL_Admin::SYSTEM_LOGS_USED_ACTION() );
	  $DB_OBJ->bindValue(':acc_ip' ,	$acc_ip ? $acc_ip : '');
	  $DB_OBJ->bindValue(':acc_act',	$ControlerAction);
	  $DB_OBJ->bindValue(':acc_url',	$acc_url);
	  $DB_OBJ->bindValue(':session',	isset($_SESSION) ? serialize($_SESSION):'');
	  $DB_OBJ->bindValue(':request',	isset($_REQUEST) ? serialize($_REQUEST) : '');
	  $DB_OBJ->bindValue(':acc_from',	isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']:'' );
	  $DB_OBJ->bindValue(':result',		isset($this->ModelResult['action']) ? serialize($this->ModelResult['action']) : '');
	  $DB_OBJ->bindValue(':agent',	    isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']:'');
	  $DB_OBJ->execute();
	}
	
	//-- other unchecked ~~~ 
	
	//呈獻資料縮短
	public function Shorten_Text($term,$length){
	  $CleanTerm = preg_replace('@<.*?>@','',$term);
	  if(mb_strlen($CleanTerm)>$length){
	    $text1=mb_substr($CleanTerm,0,$length);
	    $printout="<ins title=\"".$CleanTerm."\" >".$text1."..</ins>";
	  }else{
	    $printout=$CleanTerm;
	  }
	  return $printout;
    }
	
	//呈獻訊息縮短
	public function Shorten_Info($term,$length){
	  $term = strip_tags($term);
	  $CleanTerm = preg_replace('@<.*?>@','',$term);
	  if(mb_strlen($CleanTerm)>$length){
	    $text1=mb_substr($CleanTerm,0,$length);
	    $printout=$text1."..";
	  }else{
	    $printout=$CleanTerm;
	  }
	  return $printout;
    }
	
	
	
  }
  
?>