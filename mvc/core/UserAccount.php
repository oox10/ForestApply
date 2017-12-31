<?php
  
  /*
  *   [RCDH10 System Module] - Account Object 
  *   Create System Account reference 
  *   - account information
  *   - account groups
  *   - account access rule
  *   v 2016-11-04  
  *     group_roles cheang
  *     [group_roles] => array(
          R00=> 0:無權限，會被濾掉不出現  
		  1:一般 
		  2:管理 
		  3: -  
		)
  *
  */

  
  class UserAccount{
	
    public $UserNO;
	public $UserID;
	public $UserIP;
	public $LoginTime;
	public $UserInfo = array();
	
    public $PermissionQue  = array();
	public $PermissionNow  = array();
	public $PermissionCheck  = array();
	public $PermissionClient = array();  // 客戶端資源存取控制 ex table:doaccess
	public $AccountInitial   = false;    // 確認帳號是否正確啟動
	
	
	public function __construct($UserNo=0,$UserAccount=NULL){
	  $this->UserNO = intval($UserNo);
	  $this->UserID = $UserAccount;
	  $this->UserIP = System_Helper::get_client_ip();
	  $this->LoginTime = date('Y-m-d H:i:s');
	  
	  self::initialAccount();
	}   
	
	protected function initialAccount(){
       
	  $Model = new Account_Model;
	   
	  // get user information
	  $DB_OBJ = $Model->DBLink->prepare(SQL_Account::GET_ACCOUNT_INFO_DATA());
	  $DB_OBJ->execute(array('uid'=>$this->UserNO));
	  $this->UserInfo = $DB_OBJ->fetch(PDO::FETCH_ASSOC);
	  
	  // get user group & action limit
	  $permission = array();
	  
	  
      $DB_GPS = $Model->DBLink->prepare(SQL_Account::GET_ACCOUNT_GROUPS());
	  if($DB_GPS->execute(array('uid'=>$this->UserNO))){
		while($tmp = $DB_GPS->fetch(PDO::FETCH_ASSOC)){
		  
		  if(trim($tmp['filter'])){
			if(!System_Helper::check_ip_in_limit($this->UserIP,$tmp['filter'])){
			  continue;	
			}
		  }
		  
		  $permission[$tmp['gid']] = array();
		  $permission[$tmp['gid']]['group_code'] = $tmp['gid'];
		  $permission[$tmp['gid']]['group_no']   = $tmp['ug_no'];
		  $permission[$tmp['gid']]['group_name'] = $tmp['ug_name'];
		  $permission[$tmp['gid']]['group_info'] = $tmp['ug_info'];
		  $permission[$tmp['gid']]['group_pri']  = $tmp['ug_pri'];
		  
		  $permission[$tmp['gid']]['master'] 	 = $tmp['master'];
		    
		  
		  /*==[ 取得群組資源過濾條件 ]==*/
		  // get group permission filter 
		  $permission[$tmp['gid']]['group_filter'] = array();
		  $DB_FL = $Model->DBLink->prepare(SQL_Account::GET_GROUPS_ACCESS_RULES());
		  if($DB_FL->execute(array('gid'=>$tmp['gid'],'user'=>$this->UserID))){
			
			$rules =  $DB_FL->fetchAll(PDO::FETCH_ASSOC);  
			foreach($rules as $ru){
			  switch($ru['operator']){
				case 'IN'	: $condition = $ru['field']." IN('".join("','",array_filter(explode(',',$ru['contents'])))."')"; break;
                case '='	:  
                default		: $condition = $ru['field']." ".$ru['operator']." '".$ru['contents']."'";	break;			
			  }
			  if( !isset($permission[$tmp['gid']]['group_filter'][$ru['table']]) ) $permission[$tmp['gid']]['group_filter'][$ru['table']] = array();
              $condition    = preg_replace('/#SELF/',$tmp['gid'],$condition);
			  $permission[$tmp['gid']]['group_filter'][$ru['table']][] = $condition;
			  
			  // 影像存取套用於所有客戶端帳號
			  if($ru['table']=='doaccess'){
				if(!isset($this->PermissionClient['doaccess'])) $this->PermissionClient['doaccess'] = array();
				$this->PermissionClient['doaccess'][] =  $condition; 
			  }
			}
		  }
          
		  
		  /*==[ 取得role介面過濾條件 ]==*/
		   // get group roles 
		  $group_roles = array_filter(json_decode($tmp['roles'],true));
		  $permission[$tmp['gid']]['group_roles'] = $group_roles;
		  krsort($group_roles);
		  
		  // get group & role UI config 
		  $DB_RUI = $Model->DBLink->prepare(SQL_Account::GET_GROUPS_ROLE_INTERFACE_CONFIG());
		  
		  // ui limit by rule
		  $permission[$tmp['gid']]['interface_mask'] = array();
		  foreach($group_roles as $role=>$level){
		    $DB_RUI->bindValue(':mode','rbac');  
			$DB_RUI->bindValue(':limit','role');  
			$DB_RUI->bindValue(':target',$role);  
			if($DB_RUI->execute()){
			  while( $rule = $DB_RUI->fetch(PDO::FETCH_ASSOC)){	
				$rule['table'];     // 頁面名稱
				$rule['field'];     // DOM ID
				$rule['contents'];  // 顯示方式 查看條件 or 1 顯示 0遮蔽
				if(!isset($permission[$tmp['gid']]['interface_mask'][$rule['table']])) $permission[$tmp['gid']]['interface_mask'][$rule['table']] = [];				
				if($rule['contents']=='1' || $rule['contents']=='0'){
				  $permission[$tmp['gid']]['interface_mask'][$rule['table']][$rule['field']] = intval($rule['contents']);
				}else{
				  $permission[$tmp['gid']]['interface_mask'][$rule['table']][$rule['field']] = in_array($tmp['gid'],explode(',',$rule['contents'])) ? 1 : 0;
				}
			  }
		    }
		  }
		  
		   
		  /*==[ 取得角色對應動作 ]==*/
		 
		  // get permission filter // 取得角色action權限表
		  $permission[$tmp['gid']]['group_action'] = array(); 
		  $role_action_map = array();
		  //if(!isset($group_roles['R00']) || intval($group_roles['R00'])==0 ){  //R00 目前由 permission_action 控制 於 20170609 註銷
		  //}
			
			$DB_AC = $Model->DBLink->prepare(SQL_Account::GET_GROUPS_ROLE_ACTION());
			foreach($group_roles as $role=>$level){
			  $DB_AC->bindValue(':role',$role);
			  $DB_AC->bindValue(':level',$level);	
			  if(!$DB_AC->execute()){
				continue;
			  }
			  
			  while($ac=$DB_AC->fetch(PDO::FETCH_ASSOC) ){
			    if(!isset($role_action_map[$ac['controller']])){
				  $role_action_map[$ac['controller']] = array();  
			    }
			    //排序越後面的資料位階越高，可覆蓋前面的設定
			    if($ac['model']=='*'){
				  $role_action_map[$ac['controller']] = array($ac['model']=>$ac['permission']);
			    }else{
				  $action_list = array_filter(explode(';',$ac['model']));
				  foreach($action_list as $action){
					$role_action_map[$ac['controller']][$action] = $ac['permission'];  
				  } 
			    }
			  }
			}
		 
		  /*==[ 取得帳號對應動作表 ]==*/
		  // 帳號優先於角色設定
		  // get action map by user id
		  $DB_AC = $Model->DBLink->prepare(SQL_Account::GET_GROUPS_USER_ACTION());
		  $DB_AC->bindValue(':user',$this->UserID);
		  while($ac=$DB_AC->fetch(PDO::FETCH_ASSOC) ){
			if(!isset($role_action_map[$ac['controller']])){
			  $role_action_map[$ac['controller']] = array();  
			}
			// 使用者設定高於角色設定
			if($ac['model']=='*'){
			  $role_action_map[$ac['controller']] = array($ac['model']=>$ac['permission']);
			}else{
			  $role_action_map[$ac['controller']][$ac['model']] = $ac['permission'];
			}
		  }
		  
		  $permission[$tmp['gid']]['group_action'] = $role_action_map;
		  
		  
		  
		  /*==[ 決定目前的權限設定與查核表單 ]==*/
		  if($tmp['master']){  
			$this->PermissionNow   = $permission[$tmp['gid']];
			$this->PermissionCheck = $permission[$tmp['gid']]['group_action'];
		  }
		
		} 
		
		$this->AccountInitial   = count($permission) ? true : false;
        $this->PermissionQue    = $permission;
		
	  }
	  unset($Model);
	}
	
	
	//-- check account profile folder & work tmp file
	protected function AccountProfile(){
	  if(!is_dir(_SYSTEM_MEMBER_PROFILE_PATH.$this->UserID)){
	    mkdir(_SYSTEM_MEMBER_PROFILE_PATH.$this->UserID,0777);
	  }
			
	  if(!is_file(_SYSTEM_MEMBER_PROFILE_PATH.$this->UserID.'\\task_work.tmp')){
		$work = array('user'=>array(),'task'=>array('wno'=>'','bid'=>'','mno'=>'','time'=>'','field'=>array(),'chk'=>'','save'=>''));
		file_put_contents( _SYSTEM_MEMBER_PROFILE_PATH.$this->UserID.'\\task_work.tmp' , json_encode($work) );
	  }
	}
	
	
	//-- 變換身份 
	public function AccountFaceOff(){
	  
	  // 變身
      if($this->UserID == 'admin'){
		/*  
		$this->ModelResult['data']['SYSTEM_AD_USER_NAME'] = 'oos0.0y@gmail.com';
	    $this->ModelResult['data']['User_Name'] = 'oos0.0y@gmail.com';
		$this->ModelResult['data']['User_No']   = 8;
		$this->ModelResult['data']['User_Pri']  = '5';
		      
		$this->ModelResult['data']['SYSTEM_AD_USER_NAME'] = ' sty0088@gmail.com';
	    $this->ModelResult['data']['User_Name'] = ' sty0088@gmail.com';
		$this->ModelResult['data']['User_No']   = 172;
		$this->ModelResult['data']['User_Pri']  = '5';	 
	    */
	  }
	  
	}
	
  }
  
  
?>