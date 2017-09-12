<?php

  class Classify_Model extends Admin_Model{
    
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	}
	
	/*[ Classify Function Set ]*/ 
	
	//-- Admin Classify Page Initial  - Get Tags Folders & ClassLevel  
	// [input] : null
	public function Get_User_Classify_Data($RecordFilter = ''){
	  /*
	  echo "<pre>";
	  var_dump($this->USER);
	  exit(1);
	  */
	  $result_key = parent::Initial_Result('classify');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 查詢資料庫
		
		$DB_UTAGS = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_GET_USER_TAGS());
		$DB_UTAGS->execute(array('user'=>$this->USER->UserNO));
		$result['data']['tags'] = $DB_UTAGS->fetchAll(PDO::FETCH_ASSOC);
		
		$DB_FOLDER = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_GET_USER_FOLDERS());
		$DB_FOLDER->execute(array('user'=>$this->USER->UserNO));
		$result['data']['folder'] = $DB_FOLDER->fetchAll(PDO::FETCH_ASSOC);
		
		$DB_CLASS = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_GET_SYSTEM_LEVEL());
		$DB_CLASS->execute();
		$result['data']['level'] = $DB_CLASS->fetchAll(PDO::FETCH_ASSOC);
		
		$result['session']['user_tags'] = array();
		foreach($result['data']['tags'] as $tag){
		  $result['session']['user_tags'][$tag['utno']] = $tag['tag_term']; 
		}
		
		$result['session']['user_folder'] = array();
		foreach($result['data']['folder'] as $folder){
		  $result['session']['user_folder'][$folder['ufno']] = $folder['name']; 
		}
		$result['action'] = true;		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Admin Classify Term Modify  - T_no F_no L_no
	// [input] : null
	public function Save_Term_Name_Modify($TermId = '' , $TermName=''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 檢查參數
		if(!preg_match('/^[TFL]\_\d+$/',$TermId) || !strlen(trim($TermName))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		// 處理變數
		list($term_type,$term_id) = explode('_',$TermId);
		$new_term  = trim(rawurldecode($TermName));
		
	    switch($term_type){
		  case 'T':
		    
			if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id])){
			  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');		
			}
			$DB_UTAGS = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_UPDATE_USER_TAGS());
		    $DB_UTAGS->bindValue(':new_term',$new_term);
			$DB_UTAGS->bindValue(':user',$this->USER->UserNO);
			$DB_UTAGS->bindValue(':term_id',intval($term_id));
			if(!$DB_UTAGS->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');		
			}
			
			if($count = $DB_UTAGS->rowCount()){
			  $meta_target  = intval($term_id).':'.intval($this->USER->UserNO).':'.$_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id];
			  $meta_replace = intval($term_id).':'.intval($this->USER->UserNO).':'.$new_term;
			  $DB_UMETA = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_META_UPDATE_TERMS('tags'));
			  $DB_UMETA->bindValue(':search_term',$meta_target);
			  $DB_UMETA->bindValue(':replace_term',$meta_replace);
			  $DB_UMETA->bindValue(':condition','%'.$meta_target.'%');
			  $DB_UMETA->execute();
			  $_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id] = $new_term;
			  $result['data'] = $DB_UMETA->rowCount();
			}
		    break;
			
		  case 'F':
		    if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['user_folder'][$term_id])){
			  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');		
			}
			$DB_UFOLD = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_UPDATE_USER_FOLDER());
		    $DB_UFOLD->bindValue(':new_term',$new_term);
			$DB_UFOLD->bindValue(':user',$this->USER->UserNO);
			$DB_UFOLD->bindValue(':term_id',intval($term_id));
			if(!$DB_UFOLD->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');		
			}
			if($count = $DB_UFOLD->rowCount()){
			  $_SESSION[_SYSTEM_NAME_SHORT]['user_folder'][$term_id] = $new_term;
			  $result['data'] = $count;
			}
		    
		    break;
	    }
		$result['action'] = true;		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Classify Term Modify  - T_no F_no L_no
	// [input] : null
	public function Delete_Term($TermId = ''){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 檢查參數
		if(!preg_match('/^[TFL]\_\d+$/',$TermId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		// 處理變數
		list($term_type,$term_id) = explode('_',$TermId);
		
	    switch($term_type){
		  case 'T':
		    
			if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id])){
			  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');		
			}
			
			$DB_DTAGS = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_DELETE_USER_TAGS());
		    $DB_DTAGS->bindValue(':user',$this->USER->UserNO);
			$DB_DTAGS->bindValue(':term_id',intval($term_id));
			if(!$DB_DTAGS->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');		
			}
			
			if($count = $DB_DTAGS->rowCount()){
			  $meta_target  = intval($term_id).':'.intval($this->USER->UserNO).':'.$_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id];
			  $meta_replace = '';
			  $DB_UMETA = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_META_UPDATE_TERMS('tags'));
			  $DB_UMETA->bindValue(':search_term',$meta_target);
			  $DB_UMETA->bindValue(':replace_term',$meta_replace);
			  $DB_UMETA->bindValue(':condition','%'.$meta_target.'%');
			  $DB_UMETA->execute();
			  unset($_SESSION[_SYSTEM_NAME_SHORT]['user_tags'][$term_id]);
			  $result['data'] = $DB_UMETA->rowCount();
			}
		    break;
			
		  case 'F':
		    if(!isset($_SESSION[_SYSTEM_NAME_SHORT]['user_folder'][$term_id])){
			  throw new Exception('_SYSTEM_ERROR_ACCESS_DENIAL');		
			}
			$DB_DFOLD = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_DELETE_USER_FOLDER());
		    $DB_DFOLD->bindValue(':user',$this->USER->UserNO);
			$DB_DFOLD->bindValue(':term_id',intval($term_id));
			if(!$DB_DFOLD->execute()){
			  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');		
			}
			if($count = $DB_DFOLD->rowCount()){
			  unset($_SESSION[_SYSTEM_NAME_SHORT]['user_folder'][$term_id]);
			  $result['data'] = $count;
			}
		    
		    break;
		  
		  case 'L':
		    
			// 確認權限  R01 才可修改
 			if( !in_array('R01',$this->USER->PermissionNow['group_roles']) ){
			  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');		
			}
			
			// search level data
			$level = array();
			$DB_LEVEL = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_SELECT_SYSTEM_LEVEL());
		    if(!$DB_LEVEL->execute(array('lvno'=>$term_id)) || !$level = $DB_LEVEL->fetch(PDO::FETCH_ASSOC)){
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		    }
			
			//確認無所屬資料
			if($level['count']){
			  throw new Exception('_CLASSIFY_DELETE_LEVEL_NOT_EMPTY');
			}
			
			// search sub level
			$DB_SUBLV = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_SELECT_SYSTEM_LEVEL('uplv'));
		    if(!$DB_SUBLV->execute(array('uplv'=>$level['lvcode'])) ){  
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		    }
			$sublevel = $DB_SUBLV->fetchAll(PDO::FETCH_ASSOC);
			//確認無子階層
			if(count($sublevel)){
			  throw new Exception('_CLASSIFY_DELETE_LEVEL_NOT_EMPTY');
			}
			
			// 刪除類別資料
			$DB_DELLV = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_DELETE_SYSTEM_LEVEL_RECORD());
		    if(!$DB_DELLV->execute(array('lvno'=>$term_id)) ){  
			  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		    }
			break;
	    }
		
		
		$result['action'] = true;		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Admin Classify Level Insert 
	// [input] : $UpLevelNo
	// [input] : $NewLevelCode : L_0 // 新專題   | F_no : 資料夾掛載
	// [input] : $NewLevelName 
	public function Insert_New_ClassLevel($UpLevelNo = 0 , $NewLevelCode='' ,$NewLevelName='' ){
	  
	  $result_key = parent::Initial_Result('');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 檢查參數
		if(!preg_match('/^L\_\d+$/',$UpLevelNo) ||  !preg_match('/^[FL]\_\d+$/',$NewLevelCode)  ||!strlen(trim($NewLevelName))){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		// 處理變數
		list($term_type,$term_id) = explode('_',$NewLevelCode);
		$new_level_term  = trim(rawurldecode($NewLevelName));
		$up_level_no     = intval(substr($UpLevelNo,2));
		
		// 新增類別需要確認權限  R01 才可修改
 	    if($term_type=='L'){
		  if( !in_array('R01',$this->USER->PermissionNow['group_roles']) ){
		    throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');		
		    exit(1);
		  }
	    }
		
		$DB_LEVEL = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_SELECT_SYSTEM_LEVEL());
		$DB_LEVEL->execute(array('lvno'=>$up_level_no));
	    $parent_level = $DB_LEVEL->fetch(PDO::FETCH_ASSOC);	
	    
		$DB_LEVEL = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_SELECT_SYSTEM_LEVEL('uplv'));
		$DB_LEVEL->execute(array('uplv'=>$parent_level['lvcode']));
	    $brother_level = $DB_LEVEL->fetchAll(PDO::FETCH_ASSOC);
		$last_lvcode = 0;
		foreach($brother_level as $lv){
		  $last_lvcode = max($last_lvcode,intval($lv['lvid']));
		  if($new_level_term == $lv['name']){
			throw new Exception('_CLASSIFY_NEW_LEVEL_NAME_DOUBLE');	
            exit(1);			
		  }
		}
		$new_level_id     = str_pad(($last_lvcode+1),3,'0',STR_PAD_LEFT);
		$new_level_code   = $parent_level['lvcode'].$new_level_id ;
		$new_level_string = $parent_level['info'].'/'.$new_level_term;
		
		// insert system level
		$DB_LVADD = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_INSERT_SYSTEM_LEVEL());
		$DB_LVADD->bindValue(':uplv',$parent_level['lvcode']);
		$DB_LVADD->bindValue(':lvid',$new_level_id);
		$DB_LVADD->bindValue(':lvcode',$new_level_code);
		$DB_LVADD->bindValue(':site',$parent_level['site']+1);
		$DB_LVADD->bindValue(':name',$new_level_term);
		$DB_LVADD->bindValue(':info',$new_level_string);
		
		if(!$DB_LVADD->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_UPDATE_FAIL');		
		}
	    
		$new_level_no = $this->DBLink->lastInsertId();
		
		// 如果是掛載資料夾，更新Metadata Classlevel
		if($term_type=='F'){
		  $DB_UMTCL = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_UPDATE_FOLDER_DATA_LEVEL());
		  $DB_UMTCL->bindValue(':fid',$term_id);
		  $DB_UMTCL->bindValue(':newlevel',$new_level_string.';');
		  $DB_UMTCL->execute();
		  
		  //更新 class_level 數量
		  $DB_USLVC = $this->DBLink->prepare(SQL_AdClassify::ADMIN_CLASSIFY_UPDATE_SYSTEM_LEVEL_COUNT());
		  $DB_USLVC->execute(array('count'=>$DB_UMTCL->rowCount(),'lvno'=>$new_level_no));
	    }
		$result['data']['lvno'] = $new_level_no;
		$result['data']['lvcode'] = $new_level_code;
		$result['action'] = true;		
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
  }	
?>	