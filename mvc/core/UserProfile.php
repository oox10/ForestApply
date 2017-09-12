<?php

  /*********************************************************
      使用者 profile 類別  
  
       
  *********************************************************/
  
  class UserProfile{
    
	private $UserId;
	public	$ProfileAddress = NULL;
	public  $UserProfileError = NULL;
	protected $ProfileTable = array();
	
	public function __construct($UID = NULL){
	  $this->UserId = $UID;
	}
	
	//檢查user profile 格式
	protected function CheckProfile($CheckAddress=NULL){
	  
      if($this->UserId){
	    
		//設定 user file address
		if(!$CheckAddress){
		  if(preg_match('/^'._SYSTEM_LOGIN_ID_HEADER.'\d+/',$this->UserId)){
		    $this->ProfileAddress = _SYSTEM_GUEST_PROFILE_PATH.$this->UserId;
		  }else{
		    $this->ProfileAddress = _SYSTEM_MEMBER_PROFILE_PATH.$this->UserId;
		  }
	    }else{
		  $this->ProfileAddress = $CheckAddress.$this->UserId;
		}
		//檢查 user file 格式
	    if(is_dir($this->ProfileAddress)){
	      if(!is_file($this->ProfileAddress.'/InterFace.conf')){
		    $this->CreateUserSetting();
		  }
	    }else{
		  $this->CreateProfile();
	    }
	  }else{
		$this->UserProfileError = '_PROFILE_TARGET_USER_NULL';
	  }
	}
    
    
	protected function CreateUserSetting(){
      $UserProfileSetting =  _USER_PROFILE_DEFAULE;    
 	  file_put_contents($this->ProfileAddress.'/InterFace.conf', $UserProfileSetting);
	}

	protected function CreateProfile(){
	  if(mkdir($this->ProfileAddress,0777)){
	    $this->CreateUserSetting();
	  }else{
	    $this->UserProfileError = '_PROFILE_FILE_CREATE_FAILED';
	  }
	} 
	
	
	public function Read(){
	  $this->CheckProfile();
	  $this->ProfileTable = json_decode(file_get_contents($this->ProfileAddress.'/InterFace.conf'),true);
	  return  $this->ProfileTable;
	}  
    
    public function Write($NewProfileSetting){
	  $this->CheckProfile();
	  
	  if(!count($this->ProfileTable)){
	    self::Read();
	    //var_dump($this->ProfileTable);
	  }
	  
	  if(count($NewProfileSetting)){
	  
		foreach($NewProfileSetting as $ProfileField=>$ProfileValue){
		  
		  if(array_key_exists($ProfileField,$this->ProfileTable)){
		  
		    switch($ProfileField){
			
			  case 'USER_Tags':
				foreach($ProfileValue as $Tag_Nam => $Tag_Num){
				  if($Tag_Num == 'unset'){
				    unset($this->ProfileTable[$ProfileField][$Tag_Nam]);
				  }else{
				    $this->ProfileTable[$ProfileField][$Tag_Nam] = intval($Tag_Num);
				  } 
				}				
				break;
			  
              case 'TEMP_Query_Breadcrumbs':
			    $this->ProfileTable[$ProfileField] = $ProfileValue;
			    break;
				
			  default:
			    if(is_array($ProfileValue) && count($ProfileValue)){
			      foreach($ProfileValue as $subProfileField => $subProfileValue ){
			        $this->ProfileTable[$ProfileField][$subProfileField] = $subProfileValue;
			      } 
		        }else{
		          $this->ProfileTable[$ProfileField] = $ProfileValue;
		        }
	            break;	  
  		    }
		  }
		 
		}
		file_put_contents($this->ProfileAddress.'/InterFace.conf',json_encode( $this->ProfileTable)); 
	  }else{
	    $this->UserProfileError = '_PROFILE_WRITE_ERROR';
	  }
	
	}
	
	public function MemberUser(){	  
	  $this->CheckProfile(_SYSTEM_MEMBER_PROFILE_PATH);
	}

	public function GuestUser(){
	  $this->CheckProfile(_SYSTEM_GUEST_PROFILE_PATH); 
	}
    
	public function ErrorInfo(){
	  return  $this->UserProfileError;   
	}
    
	
	public function ProfileResult(){
	  
	  $ProFileInfo = array();
	  
	  if($this->UserProfileError){
	    $ProFileInfo['success'] = false;
		$ProFileInfo['error_message'] = $this->UserProfileError;
	  }else{
	    $ProFileInfo['success'] = true;
	  }
	  
	  return   $ProFileInfo;   
	}
	
	
    public function __destruct(){
      $this->UserId = NULL;
      $this->UserProfileError = NULL;
	}
  
  }


?>