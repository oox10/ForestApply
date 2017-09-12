<?php

  class Archive_Model extends Admin_Model{
    
	private		$Config;
	private		$UserConf		= array();  // 使用者設定
	private		$MetaConf		= array();  // META設定
	
	/***--  Function Set --***/
    public function __construct(){
	  parent::__construct();
	  parent::initial_user($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER']);
	  
	  if(isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['USER'])){
		// 讀取檢索設定檔案
		$this->Config  = new UserProfile($this->USER->UserID);
	    $this->UserConf= $this->Config->Read();
	    $this->MetaConf= json_decode(_SYSTEM_META_CONFIG,true);  
	  }
	}
	
	
	
	/*[ Archive Function Set ]*/ 
	
	//-- Get User Access Folders
	// [input] : 
	public function Get_User_Folders(){
	  
	  $result_key = parent::Initial_Result('folders');
	  $result  = &$this->ModelResult[$result_key];
      try{
		$folder = array('user'=>array(),'share'=>array());
		
		$DB_OBJ = $this->DBLink->prepare(parent::SQL_Permission_Filter(SQL_AdArchive::ADMIN_ARCHIVE_GET_FOLDERS()));
		$DB_OBJ->bindValue(':owner'	, $this->USER->UserNO);
	
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		
		while($tmp = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
		  if($tmp['owner']==$this->USER->UserNO){
			$folder['user'][$tmp['ufno']] = $tmp;    
		  }else{
			$folder['share'][$tmp['ufno']] = $tmp;    
		  }
		}
		
		//return
		$result['data'] 	= $folder;
		$result['action'] 	= true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Get All User Tags
	// [input] : 
	public function Get_User_TagReference(){
	  
	  $result_key = parent::Initial_Result('tags');
	  $result  = &$this->ModelResult[$result_key];
      try{
		
		$DB_OBJ = $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_GET_TAGS());
		if(!$DB_OBJ->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');  
		}
		//return
		$result['data'] 	= $DB_OBJ->fetchAll(PDO::FETCH_ASSOC);
		$result['action'] 	= true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	//-- Get Data Class Level
	// [input] : $OrganCode
	// [input] : $LevelType
	// [input] : $LevelTarget
	
	public function Get_FileLevel_Array( $OrganCode , $LevelType='classify', $LevelTarget=''){
		
	  $result_key = parent::Initial_Result('level');
	  $result  = &$this->ModelResult[$result_key];	
		
      $FileLevelArray = array();
	  $LevelSet = array(); 
	  $LevelSet[$OrganCode] = self::built_file_level($this->DBLink,$OrganCode,$LevelType,'site','1',$FileLevelArray,$LevelTarget);
	  $result['action'] = true;	
	  $result['data'] = $LevelSet;
	  return $result;
	}
	
    public function built_file_level($DBConnect,$OrganCode,$Level_Type,$Level_Field,$Level_Value,$FileLevelArray,$LevelTarget){
	    
		$sublv = '';  #本層ID  用來尋找搜尋出來的階層之子階層  uplevel = sublv
		$lvopen= '';
		$DefaultDisplayLevel = _SYSTEM_AP_FILE_LEVEL_DEFAULT_LEVEL ? _SYSTEM_AP_FILE_LEVEL_DEFAULT_LEVEL:1;  // 預設開啟階層
	    $DisplayLevelCheckNum   = 2*$DefaultDisplayLevel - 1;  // 開啟階層轉換成檢測數值
	    $DisplayOptionCheckNum  =  (($DefaultDisplayLevel-1)*2-1)==1 ? 2 : (($DefaultDisplayLevel-1)*2-1) ;//
	  
		$Level_Visit = false ;
		
		if($Level_Value != 1){
			if( preg_match('/'.$OrganCode.'\-'.$Level_Value.'/',$LevelTarget)){
			  $FileLevelArray[$Level_Value]['LevelView'] =  'inline';          
			  $Level_Visit = true;
			  $FileLevelArray[$Level_Value]['LevelOption'] = $Level_Visit && $FileLevelArray[$Level_Value]['LevelOption']!=' * '?  "−" : $FileLevelArray[$Level_Value]['LevelOption'] ;   
			
			}else if(strlen($Level_Value) < (2*_SYSTEM_AP_FILE_LEVEL_DEFAULT_LEVEL-1)){
			  if(strlen($Level_Value)>$DisplayLevelCheckNum){
                //階層ID長度 >1 就預設關閉		
		        $FileLevelArray[$Level_Value]['LevelView'] =  'none';
			  }else{
                $FileLevelArray[$Level_Value]['LevelView'] =  'inline';          
	          }
			}else{
			  if(strlen($Level_Value)>$DisplayLevelCheckNum){  
                //階層ID長度 > DefaultDisplayLevel 就預設關閉		
		        $FileLevelArray[$Level_Value]['LevelView'] =  'none';
	          }else{
			    $FileLevelArray[$Level_Value]['LevelView'] =  'inline';          
	          }
		      //return $FileLevelArray;
			  //exit(1);
		    }
		}
		
		$idbs = $DBConnect->prepare(SQL_AdArchive::SELECT_FILE_LEVEL( $OrganCode , $Level_Type , $Level_Field)); 
	    $idbs->bindParam(':Value',$Level_Value,PDO::PARAM_STR);
	    
	    $DB_Result= $idbs->execute();
		$DB_Array = array();
	    $DB_Array = $idbs->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($DB_Array as $LevelData){
         
			$FileLevelArray[$LevelData['lvcode']]['LevelTable']	=  $Level_Type;
		    $FileLevelArray[$LevelData['lvcode']]['LevelId']	=  $LevelData['lvcode'];
	        $FileLevelArray[$LevelData['lvcode']]['LevelSite']	=  $LevelData['site'];
		    $FileLevelArray[$LevelData['lvcode']]['LevelUplv']	=  $LevelData['uplv'];
		    $FileLevelArray[$LevelData['lvcode']]['LevelName']	=  $LevelData['name'];
			$FileLevelArray[$LevelData['lvcode']]['LevelInfo']	=  $LevelData['info'];
			$FileLevelArray[$LevelData['lvcode']]['LevelNum']	=  $LevelData['count'];
		    
		    $sublv=$LevelData['lvcode'];                  
		    $idbs = $DBConnect->prepare(SQL_AdArchive::SELECT_FILE_LEVEL($OrganCode , $Level_Type , 'uplv')); 
	        $idbs->bindParam(':Value', $sublv,PDO::PARAM_STR); 
	    
	        $SubDB_Result= $idbs->execute();
	        $SubDB_Array = $idbs->fetchAll(PDO::FETCH_ASSOC);
		  
		    //LevelSite 1 mark
		    $FileLevelArray[$LevelData['lvcode']]['LevelClass'][] =  ($LevelData['site'] == '1') ? 'LevelTop' : '' ;
		  
		    if(count($SubDB_Array)){
			  $FileLevelArray[$LevelData['lvcode']]['LevelOption'] = (strlen($Level_Value)<$DisplayOptionCheckNum) ? "−" : "+" ;
			  $FileLevelArray=self::built_file_level($DBConnect,$OrganCode,$Level_Type,'uplv',$sublv,$FileLevelArray,$LevelTarget);
		    }else{             	
              $FileLevelArray[$LevelData['lvcode']]['LevelOption'] = "*";                           
            }
        }
		return $FileLevelArray;
	}
	
	
	
	/*
	*   資料庫檢索函數組
	*   1. Built_Search_Set
	*   2. Built_Search_Info
	*	3.
	*/
	
	public   	$SearchInfo 	= array();  // 儲存檢索資料
	public		$UserQuerySet 	= array();  // 使用者檢所組合
	private     $Breadcrumbs    = array();  // 系統麵包屑
	//-- Admin Archive 
	// [input] : $ActionFrom 			:   search folder level 
	// [input] : $QueryConditionEncode  :   accnum:urlencode(base64encode(json_pass()))  = array(0=>F:S|S|S,1=>F:S|S)
	// [input] : $AccessNum  :   [int]
	// [output] : query set   array( 0=> f:query:attr )
	
	public function ADArchive_Built_Search_Set( $ActionFrom , $QueryConditionEncode='' , $AccessNum=0){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  $access_num 	= 0;       // 繼承檢索序號
	  $add_query_set= array(); // 新檢索條件 
 	  
	  try{
		
		$User_Conf_ReSet	   = array();
		$User_Query_String_Set = array();  // 初始分割
		$User_Query_Normal_Set = array();  // 第一階段處理分割
		$Parent_Query_Term_Set = array();  // 上次檢索條件
		
		$this->SearchInfo['access_from']    = $AccessNum;
		$this->SearchInfo['action_from']    = $ActionFrom;
		
		$access_num    = intval($AccessNum) ? intval($AccessNum) : 0; 
		
		if( intval($access_num) ){  // 繼承檢索組合
		  $DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::GET_SEARCH_TEMP_RECORD());
		  $DB_Result 	= $DB_OBJ->execute(array('ACCNUM'=>$access_num,'UAID'=>$this->USER->UserID)); 
		  $DB_Data		= $DB_OBJ->fetch(PDO::FETCH_ASSOC);
          $Parent_Query_Term_Set = explode('⊕',$DB_Data['Query_Term_Set']);
		  
		}else{  					// 新檢索
		  // 檢索歷史存入
		  if($this->UserConf['TEMP_Last_QuerySet']){
			$DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::INSERT_SEARCH_HISTORY_TABLE());
			$DB_Result 	= $DB_OBJ->execute(array(	'UID'	=>$this->USER->UserID,
													'USID'	=>$this->USER->UserID,
													'ACCNUM'=>$this->UserConf['TEMP_Last_QueryAccNum'],
													'QTSET' =>$this->UserConf['TEMP_Last_QuerySet'],
													'PAGE'	=>$this->UserConf['SET_PageNow']));
		  }
          $User_Conf_ReSet = array('SET_LevelMark'=>'','SET_PageNow'=>'1');
		}
		
		// 依不同檢所作為進行條件解析
	  
		switch($ActionFrom){
			
			case 'index':
			  // 取得最新的結果
			  
			  $User_Query_Normal_Set[0]['field']   = 'new'; 
			  $User_Query_Normal_Set[0]['value'][] = date('Y-m-d');
			  $User_Query_Normal_Set[0]['attr']    = '+';
		
			  $User_Conf_ReSet['MODE_SearchAP']	 				= false;
			  $User_Conf_ReSet['SET_LevelMark']					= '';
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] 	 		= '';
			  $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] 		= array();
			  $this->SearchInfo['Breadcrumbs'] = array('type'=>'最新上傳', 'term'=>'', 'link'=>$_SERVER['REQUEST_URI'] , 'accno'=>0 ,'result'=>0);
			  
			  break;
			  
			  
			  // 取得最後一次檢索結果
			  break;
		
			//使用者輸入檢索
			case 'search':  
			  
			  // 解析query submit
		      $add_query_set = json_decode(rawurldecode($QueryConditionEncode),true); 
		      
			  if(!$add_query_set){
		        throw new Exception('_ARCHIVE_SEARCH_QUERY_FAIL');
		      }
			  
			  if(!count($add_query_set)){
				throw new Exception('_ARCHIVE_SEARCH_TERM_EMPTY');
			  }
			  
			  $Set_Counter	   	   = 0;    
			  foreach($add_query_set as $query_string){
				
				$query_set = explode(':',$query_string);
				
				if(isset($query_set[1])){
				  $NewQueryString = $query_set[1];
				  $NewQueryField  = $query_set[0];
				}else{
				  $NewQueryString = $query_set[0];
				  $NewQueryField  = 'kwds';
				}
				
				//$System_Chan_Code = array('∩','∪');
				$NormalQueryString		= '';  				// 存放轉換後的查詢字串       
				
				//處理 在兩引號""間保留原始查詢條件  用來處理  英文多字之辭彙查詢  如  National Reconditioning
				$WordKeep = False;
				$QuotationCount = 0;
				for($SetStringPoint=0 ; $SetStringPoint<mb_strlen($NewQueryString) ; $SetStringPoint++){
				  $SetStringWord = mb_substr($NewQueryString,$SetStringPoint,1);   //將條件逐字拆開
				
				  if($SetStringWord == '"'){   					//如果遇到開頭引號  則開啟保留功能  結束引號則關閉功能
					$QuotationCount++;
					$WordKeep = ($QuotationCount%2) ? TRUE:FALSE;  
				  }
				  
				  switch($SetStringWord){
					case '&' : case '＆':
					  $NormalQueryString.= $WordKeep ? $SetStringWord:'∩';
					  break;
					case ' ': case '|': case '｜':
					  $NormalQueryString.= $WordKeep ? $SetStringWord:'∪';
					  break;
					default:  $NormalQueryString.= $SetStringWord; break;
				  }
				}
				 
				$SearchAP_Check		   = FALSE;
				$User_Query_String_Set = preg_split('/∩/',$NormalQueryString);
				foreach($User_Query_String_Set as $User_Set_String){
				  
				  $User_Search_format = array(); //接收使用者自訂欄位
				  $User_Search_Term	  = '';  // 將要處理的檢索條件
				  $User_Search_Field  = '';  // 將要處理的檢索欄位
				
				  // 特殊檢索樣式解析
				  if(preg_match('/\?(.*?):(.*)(\s+)?/',$User_Set_String,$User_Search_format)){
			  
					$User_Search_Term 	= preg_replace('/\?.*?:.*(\s+)?/','',$User_Set_String);
					$Term_Discript 		= '';
					$Term_Search_String = '';
					$Term_Search_AP		= strtolower($User_Search_format[1]);
					
			  
					switch($Term_Search_AP){
					
					  // 一般欄位
					  default: 
						$User_Search_Field	= is_array($this->MetaConf[$Term_Search_AP]) ? $Term_Search_AP:'kwds';
						$User_Search_Term 	= $User_Search_format[2]; 
						break;
					}
			  
				  }else{
					$User_Search_Term	= $User_Set_String;
					$User_Search_Field 	= $NewQueryField;
				  }
				  
				  /*****----- 以下處理檢索詞彙 -----*****/
				  $CleanNormalNewSetString = '';
				  $CleanNormalTermArray = array();
				  $CleanNormalNewSetString	= trim($User_Search_Term);
				 
				  // 處理詞彙 NOT 或 function 狀態
				  if(preg_match('/^-/',$CleanNormalNewSetString)){
					$CleanNormalNewSetString = preg_replace('/^-/','',$CleanNormalNewSetString);
					$User_Query_Normal_Set[$Set_Counter]['attr'] = '-';
				  }else if(preg_match('/^\w%/',$CleanNormalNewSetString,$APMatch)){
					$CleanNormalNewSetString = preg_replace('/^\w%/','',$CleanNormalNewSetString);
					$User_Query_Normal_Set[$Set_Counter]['attr'] = $APMatch[0];
				  }else{
					$User_Query_Normal_Set[$Set_Counter]['attr'] = '+';
				  }
				  
				  //處理詞彙 or 狀態
				  $User_Query_Normal_Set[$Set_Counter]['field'] = $User_Search_Field;
				  $User_Query_Normal_Set[$Set_Counter]['value'] = array_filter(preg_split('/∪/',$CleanNormalNewSetString)); 
				  
				  $Set_Counter++;
				}
			  }	
			  
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] = $QueryConditionEncode;
			  $User_Conf_ReSet['MODE_SearchAP']	 = false;	
			  if(!count($Parent_Query_Term_Set)) $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] = array();
			  
			  $crumbs_term = count($User_Query_Normal_Set[0]['value'])>2 ? join('or',array_slice($User_Query_Normal_Set[0]['value'],0,2)) : join('or',$User_Query_Normal_Set[0]['value']);
			  $crumbs_other= $Set_Counter > 1 ? "…".$Set_Counter.'組條件' : '';
			  $this->SearchInfo['Breadcrumbs'] = array('type'=>'搜尋', 'term'=>$crumbs_term.$crumbs_other, 'link'=>$_SERVER['REQUEST_URI'] , 'accno'=>0 ,'result'=>0);
			  
			  break;
			
			//瀏覽檢索
			
			case 'level': 
			  
			  if(!preg_match('/^\w+\-\d+$/',$QueryConditionEncode)){
				throw new Exception('_ARCHIVE_SEARCH_TERM_EMPTY');
			  }
			  
		      list($organ_code,$class_id)	= explode('-',$QueryConditionEncode);
			  $Class_List	= array();
			  $DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::GET_FILE_LEVEL_TARGET());
			  do{  
				$DB_Result 	= $DB_OBJ->execute(array('organ'=>$organ_code,'LV'=>$class_id));
				$DB_Data		= $DB_OBJ->fetch(PDO::FETCH_NUM);
				$Class_List[]	= $DB_Data[0];
			  }
			  while( $class_id=substr($class_id,0,-3));
				
			  $User_Query_Normal_Set[0]['field']   = 'cllv'; 
			  $User_Query_Normal_Set[0]['value'][] = join('/',array_reverse($Class_List));
			  $User_Query_Normal_Set[0]['attr']    = '+';
		
			  $User_Conf_ReSet['MODE_SearchAP']	 = false;
			  $User_Conf_ReSet['Dom_Display']['level_area']		= 'block';
			  $User_Conf_ReSet['Dom_Display']['postquery_area']	= 'block';
			  $User_Conf_ReSet['Dom_Display']['history_area']	    = 'block';
			  $User_Conf_ReSet['SET_LevelMark']					= $QueryConditionEncode;
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] 			= $QueryConditionEncode;
			  $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] = array();
			  $this->SearchInfo['Breadcrumbs'] = array('type'=>'類別', 'term'=>$User_Query_Normal_Set[0]['value'][0], 'link'=>$_SERVER['REQUEST_URI'] , 'accno'=>0 ,'result'=>0);
			  
			  break;
			
			case 'tags': 
			
			  $tag_string = rawurldecode($QueryConditionEncode); 
			  
			  if(!strlen(trim($tag_string))){
				throw new Exception('_ARCHIVE_SEARCH_TERM_EMPTY');
			  }
			  
			  $User_Query_Normal_Set[0]['field']   = 'tags'; 
			  $User_Query_Normal_Set[0]['value'][] = ':'.$tag_string.';';
			  $User_Query_Normal_Set[0]['attr']    = '+';
		
			  $User_Conf_ReSet['MODE_SearchAP']	 				= false;
			  $User_Conf_ReSet['SET_LevelMark']					= '';
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] 	 		= $QueryConditionEncode;
			  $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] 		= array();
			  $this->SearchInfo['Breadcrumbs'] = array('type'=>'標籤', 'term'=>$User_Query_Normal_Set[0]['value'][0], 'link'=>$_SERVER['REQUEST_URI'] , 'accno'=>0 ,'result'=>0);
			  
			  break;
			
			  
			case 'pquery':  //後分類篩選
			  
			  // 解析query submit
		      $add_query_set = json_decode(rawurldecode($QueryConditionEncode),true); 
		      
			  if(!$add_query_set){
		        break;
		      }
			  
			  if(!count($add_query_set)){
				break;
			  }
			  
			  $Set_Counter	   	   = 0;    
			  foreach($add_query_set as $query_string){
				
				$query_set = explode(':',$query_string);
				if(isset($query_set[1])){
				  $NewQueryString = $query_set[1];
				  $NewQueryField  = $query_set[0];
				}else{
				  $NewQueryString = $query_set[0];
				  $NewQueryField  = 'kwds';
				}
				$User_Query_String_Set = explode('∩',$NewQueryString);
				
				foreach($User_Query_String_Set as $User_Set_String){
				  
				  $User_Search_Term	    = $User_Set_String;
				  $User_Search_Field 	= $NewQueryField;
				  
				  
				  /*****----- 以下處理檢索詞彙 -----*****/
				  $CleanNormalNewSetString = '';
				  $CleanNormalTermArray = array();
				  $CleanNormalNewSetString	= trim($User_Search_Term);
				 
				  // 處理詞彙 NOT 或 function 狀態
				  if(preg_match('/^-/',$CleanNormalNewSetString)){
					$CleanNormalNewSetString = preg_replace('/^-/','',$CleanNormalNewSetString);
					$User_Query_Normal_Set[$Set_Counter]['attr'] = '-';
				  }else if(preg_match('/^\w%/',$CleanNormalNewSetString,$APMatch)){
					$CleanNormalNewSetString = preg_replace('/^\w%/','',$CleanNormalNewSetString);
					$User_Query_Normal_Set[$Set_Counter]['attr'] = $APMatch[0];
				  }else{
					$User_Query_Normal_Set[$Set_Counter]['attr'] = '+';
				  }
				  
				  //處理詞彙 or 狀態
				  $User_Query_Normal_Set[$Set_Counter]['field'] = $User_Search_Field;
				  $User_Query_Normal_Set[$Set_Counter]['value'] = array_filter(explode('|',$CleanNormalNewSetString)); 
				  $Set_Counter++;
				}
			  }	
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] = $QueryConditionEncode;
			  $User_Conf_ReSet['MODE_SearchAP']	 = false;	
			  break;
			
			case 'sort':
			  
			  $sort_conf = json_decode(_SYSTEM_ORDER_CONF,true);
			  $sort_set = json_decode(rawurldecode($QueryConditionEncode),true); 
			  
			  $sort_field  = isset($sort_conf[$sort_set['field']]) ? $sort_set['field'] : array_keys($sort_conf)[0];
			  $sort_method = strtoupper($sort_set['type'] ) == 'ASC' ? 'ASC' : 'DESC';
			  
			  $User_Conf_ReSet['SET_OrderByTarget'] = $sort_field ;
			  $User_Conf_ReSet['SET_OrderByMethod'] = $sort_method;
			  
			  break;
			  
			
			case 'folder':  //我的資料夾
			  
			  if(!intval($QueryConditionEncode)){
				throw new Exception('_ARCHIVE_SEARCH_FOLDER_FAILS');
			  }
				
			  //check folder id is access	
			  $DB_OBJ = $this->DBLink->prepare(SQL_AdArchive::CHECK_FOLDER_CAN_ACCESS());
			  $DB_OBJ->execute(array('owner'=>$this->USER->UserNO,'ufno'=>intval($QueryConditionEncode)));
			  $folder = array();
			  if(!$folder = $DB_OBJ->fetch(PDO::FETCH_ASSOC)){
				throw new Exception('_ARCHIVE_SEARCH_FOLDER_ACCESS_DENIAL');  
			  }
			  
			  $User_Query_Normal_Set[0]['field']	= 'fid';
			  $User_Query_Normal_Set[0]['value'][]	= intval($QueryConditionEncode);
			  $User_Query_Normal_Set[0]['attr']		= '+';
				
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] 	 = $QueryConditionEncode;
			  $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] = array();
			  $this->SearchInfo['Breadcrumbs'] = array('type'=>'資料夾', 'term'=>$folder['name'], 'link'=>$_SERVER['REQUEST_URI'] , 'accno'=>0 ,'result'=>0);
			  
			  break;  
			  
			case 'history':   // 檢索歷史
			  
			  $DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::GET_USER_HISTORY());
			  $DB_OBJ->bindParam(':UID',$this->UserID,PDO::PARAM_STR);
			  $DB_OBJ->bindParam(':HSN',$NewQueryField,PDO::PARAM_INT);
			  $DB_Result = $DB_OBJ->execute();
			  $DB_Data	 = $DB_OBJ->fetch(PDO::FETCH_ASSOC);
			  $Parent_Query_Term_Set = explode('⊕',$DB_Data['Query_Term_Set']);
			  
			  $Page_Now_Num = intval($NewQueryString) ? intval($NewQueryString) : 1;
			  
			  $User_Conf_ReSet['SET_PageNow']						= $Page_Now_Num;
			  $User_Conf_ReSet['TEMP_Last_QueryTerm'] 			= '';
			  
			  break;		
			
			case 'termPop':
		   
			  if(preg_match('/^\d+_\d+$/',$NewQueryString)){
				$popNo=explode('_',$NewQueryString);
				$qhtempLv1  = $Parent_Query_Term_Set;
				$termSetNum = count($qhtempLv1);
				$stopNum    = $popNo[0];
				$cutNum     = $termSetNum-$stopNum;
			
				if($termSetNum && $termSetNum > $stopNum){  //確認是否存在檢索條件
				  for($x=0 ; $x < $cutNum ; $x++){ //進行檢索條件消去 
					array_pop($qhtempLv1);
				  }
				}else{
				  if($cutNum){
					echo "pop_none_query_term!!";
				  }else{
					//不做任何反應
				  }
				}
				$Parent_Query_Term_Set=$qhtempLv1;
				$User_Conf_ReSet['SET_PageNow']						= 1;
				$User_Conf_ReSet['TEMP_Last_QueryTerm'] 			= '';
			  }
			  break;
		    
			default: break;
		}
		//var_dump( $Parent_Query_Term_Set);
		//var_dump( $User_Query_Normal_Set);
		

		
	    //-- STEP 03 過濾檢索組合	
	    foreach($User_Query_Normal_Set as $Term_Set_Num => &$Term_Set_Array){
		  /*
		  $Term_Set_Array['field']
		  $Term_Set_Array['value']
		  $Term_Set_Array['attr']
		  */
		  $Checked_Term_Array = array();
		  
		  foreach($Term_Set_Array['value'] as $Term_String){
		    
			$Temp_Term 	= $Term_String;
			$AP_Count 	= preg_match_all('/\.\{.*?\}/',$Term_String,$AP_Pattern,PREG_SET_ORDER);
			
			
			//替換AP Pattern防止被破壞
			if(preg_match('/^\w%/',$Term_Set_Array['attr']) and $AP_Count){
			  for($i=0  ; $i<$AP_Count; $i++){
			   $Temp_Term = preg_replace('/\.\{.*?\}/','＃SEARCHPATTERN'.$i.'＃',$Temp_Term,1);
			  }
			}
			
			//進行字串檢查過濾
			//$New_Term = preg_replace('/(\[|\]|\(|\)|\{|\}|<|>|\.|\*|\?|\\|\/|=|\!|\$|&|%|#|@|\^|:|\'|")/','_',$Temp_Term); //過濾奇怪符號
			//$New_Term = preg_replace('/台/','臺',$New_Term);   //轉換通用字  2013-10-25 館方取消 
			$New_Term = preg_replace('/([\x00-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F])/','\\\\\1',$Temp_Term);
			
			//換回原本之AP Pattern
			if($AP_Count){
			  for($i=0  ; $i<$AP_Count; $i++){
			    $New_Term = preg_replace('/＃SEARCHPATTERN'.$i.'＃/',$AP_Pattern[$i][0],$New_Term);
			  }
			}
			
			if( preg_replace('/_/','',$New_Term) != '' ){
			  $Checked_Term_Array[] = $New_Term;  
			}else{
			  continue;
			}
		  }
		  
		  // 檢查通過檢所詞，若無則移除檢索組
		  if(count($Checked_Term_Array)){
		    $Term_Set_Array['value'] = $Checked_Term_Array;
		  }else{
		    unset($User_Query_Normal_Set[$Term_Set_Num]);
		  }
	    }
	    
	  
	    //-- STEP 04 組合檢索條件	
	  
	    //條件pattern     kw:x|y|z:+ -
	    $User_Query_Final_Set = count($Parent_Query_Term_Set) ? $Parent_Query_Term_Set : array(); 
        foreach($User_Query_Normal_Set as $Term_Set_Num => $Term_Set_Data){
		  $User_Query_Final_Set[] =  $Term_Set_Data['field'].':'.join('|',$Term_Set_Data['value']).':'.$Term_Set_Data['attr'];
	    }
	  
        //var_dump($User_Query_Final_Set);
	  
	    if(!count($User_Query_Final_Set)){
	      throw new Exception('_ARCHIVE_SEARCH_QUERY_FAIL');  
		}
		
		//寫入 User conf
	    if(count($User_Conf_ReSet)){
		  $this->Config->Write($User_Conf_ReSet);
		  $this->UserConf = $this->Config->Read();
		  unset($User_Conf_ReSet);
	    }
		
		self::Built_Search_Info($User_Query_Final_Set,$result['message']); 
		
		$result['data']['query_set'] = $User_Query_Final_Set;
		
		if(!count($result['message'])){
		  $result['action'] = true;	
		}
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  
      return $result;
	}
	
	
	
	private function Set_SQL_Connect_Operator($TermAttr , $FieldSearchType ,$FieldMatchType){
	  
	  $SQL_Operator  = array('link'=>'','left'=>'','right'=>'');  // SQL 串連運算元
	  switch($FieldSearchType){
		case 'exact':
		  switch($TermAttr){
			case '+':  $SQL_Operator['link'] = ' = ' ; break;
			case '-':  $SQL_Operator['link'] = ' != '; break;
			default :  $SQL_Operator['link'] = ' = ' ; break;
		  }
		  break;
							  
		case 'fuzzy':
		  switch($TermAttr){
			case '+':  $SQL_Operator['link'] = ' LIKE ' ; break;
			case '-':  $SQL_Operator['link'] = ' NOT LIKE '; break;
			default :  $SQL_Operator['link'] = ' LIKE ' ; break;
		  }
							  
		  switch($FieldMatchType){
			case 'all':
			  $SQL_Operator['left']='';
			  $SQL_Operator['right']='';
			  break;
			case 'any':
              $SQL_Operator['left']='%';
			  $SQL_Operator['right']='%';
		      break;
            case 'left':
			  $SQL_Operator['left']='%';
			  $SQL_Operator['right']='';
              break;					  
			case 'right':
			  $SQL_Operator['left']='';
			  $SQL_Operator['right']='%';
			  break;
			default:
              $SQL_Operator['left']='%';
			  $SQL_Operator['right']='%';
		      break;
	      }
		  break;
	    
		case 'fulltext':
		  switch($TermAttr){
			case '+':  $SQL_Operator['link'] = '' ; break;
			case '-':  $SQL_Operator['link'] = '-'; break;
			default :  $SQL_Operator['link'] = '' ; break;
		  }
		  break;
	  }
	  return $SQL_Operator;
	}
	
	
	/************************************************
	  組合檢索條件為sql
	    參數  none  
	
	    回傳
		  false : 失敗
		  true  : 成功
		  
        提醒
		  取得建構好得 set 為 $this->UserQuerySet
		  
	***********************************************/
	
	public function Built_Search_Info( $UserQuerySet ,  &$message){
	  
		  
	  try{	  
		  
		  $User_Query_Set = $UserQuerySet;
		  $Search_Ap_Flag = 0;
		  $Search_FT_Flag = FALSE;
		  $Query_Set_Sql_Format_Array = array('mysql'=>array(),'mysqljoin'=>'','sphinx'=>array(),'sphinxadd'=>array());
		  
		  $New_Query_Access_Num = 0;  // 註冊後的最新 access_num
		 
		  $Term_Attr_Count = array('-'=>0,'+'=>0,'t%'=>0,'c%'=>0);
		  
		  if(!count($User_Query_Set)){
			throw new Exception('_ARCHIVE_SEARCH_QUERY_FAIL');  
		  }
		
		
		foreach($User_Query_Set as $Set_Num => $Set_String){
		  $Set_Array=array();
		  
		  if(preg_match('/^([\w\d]+):(.*):(.*?)$/',$Set_String,$Set_Split)){
            array_shift($Set_Split);
		  }else{
		    $Set_Split = explode(':',$Set_String);
		  } 
		  
		  $Set_Array['field'] = $Set_Split[0];
		  $Set_Array['value'] = explode('|',$Set_Split[1]);
		  $Set_Array['attr']  = $Set_Split[2];
		  
		  // 處理反向條件導致 輸出 .all 錯誤
		 
		  if($Set_Num === 0 && $Set_Array['attr'] =='-'){
		    $this->ModelError = TRUE;
		    $this->ModelErrInfo = '_SYSTEM_SEARCH_FIRST_TERM_INVERSE_ILLEGAL';
			continue;
		  }
		 
		  /*******----- 以下架構檢索詞組模式 -----*******/
		  
		  
		  // 處理 attr
		  $Sql_Built_Mode	= 'normal';     //定義sql組合的模式   normal / regular 
		  $SQL_Condition_Set = array('mysql'=>array(),'sphinx'=>array('field'=>'','value'=>array(),'link'=>''));
		  
		  if($Set_Array['field']=='not'){
		    $Search_Ap_Flag++;
		  }
		  
		  switch($Set_Array['attr']){
			
			case '+':
		    case '-':
			  $Sql_Built_Mode	= 'normal';  break;
			case 't%':
			case 'c%':
			  $Sql_Built_Mode	= 'regular'; $Search_Ap_Flag++; break;
		  }
		  
		  $Term_Attr_Count[$Set_Array['attr']]+= count($Set_Array['value']);
		  
		  
		  
		  // 處理 value
		  switch($Sql_Built_Mode){
		    
			case 'normal':
		      
			  foreach($Set_Array['value'] as $Term){
			    
				if($Term && !preg_match('/~$/',$Term)){    // 確認條件目前無遮蔽
				  
				  //處理field
				  switch($Set_Array['field']){
				    case 'kwds':
				     
					  $Term_Condition_Set = array();
					  
					  foreach($this->MetaConf as $FieldCode => $FieldSet ){
						if($FieldSet['SearchState']){
						  //組合檢索條件
						  
						  $FieldSet['FieldName'] = $FieldSet['FieldName']=='Tags' ? "IFNULL(Tags,'')" : $FieldSet['FieldName'];  
						  /*因為 NULL 不可做比較 因必為 否 所以LEFT JOIN 的 Tags 要先配上  IFNULL 函數轉換內容  2013-10-17*/
						  
						  $SQL_Operator = self::Set_SQL_Connect_Operator($Set_Array['attr'] , $FieldSet['SearchType'] ,$FieldSet['MatchType']);
						  $Term_Condition_Set[] = $FieldSet['FieldName'].$SQL_Operator['link']."'".$SQL_Operator['left'].$Term.$SQL_Operator['right']."'"; 
						}else{
						  //此欄位不要被搜尋
						}
					  
					  }
					  
					  $Term_Condition_String = "(".join(' OR ',$Term_Condition_Set).")";
					  $Term_Condition_String = preg_replace('/\s+/',' ',$Term_Condition_String);
					  $SQL_Condition_Set['mysql'][]   = $Term_Condition_String;
						 
					  $SQL_Operator = self::Set_SQL_Connect_Operator($Set_Array['attr'] , 'fulltext' ,$this->MetaConf[$Set_Array['field']]['MatchType']);	 
					  $SQL_Condition_Set['sphinx']['field']		= '@* ';
					  $SQL_Condition_Set['sphinx']['link']		= $SQL_Operator['link'];
					  $SQL_Condition_Set['sphinx']['value'][]	= '"'.$Term.'"';
					  break;  // end of kw case
					
					
					
					case 'new':
					  $date_end = date('Y-m-d H:i:s');
					  $date_start   = date( 'Y-m-d 00:00:00',strtotime('-2 month',strtotime($date_end)));
					  $SQL_Condition_Set['mysql'][]="upload_time BETWEEN '".$date_start."' AND '".$date_end."'";
					  
					  break;
					
					case 'not':
					  
					  $SQL_Operator = self::Set_SQL_Connect_Operator($Set_Array['attr'] , $this->MetaConf['note']['SearchType'] ,$this->MetaConf['note']['MatchType']);
					  $SQL_Condition_Set['mysql'][] = "Notes".$SQL_Operator['link']."'".$SQL_Operator['left'].$Term.$SQL_Operator['right']."'";
			          
					  //因為個人筆記沒有加入全文檢索
                      //所以必須使用檢索後  過濾			
			          $Query_Set_Sql_Format_Array['sphinxadd'][] = "IFNULL(Notes,'')".$SQL_Operator['link']."'".$SQL_Operator['left'].$Term.$SQL_Operator['right']."'";
				      break;
				    
					
					case 'fid':
					   $Query_Set_Sql_Format_Array['mysqljoin'] = 'INNER JOIN folder_map ON system_id = sid ';
					  
				    default:
				      
					  if($Term=='\.none'){
						$SQL_Condition_Set['mysql'][]=$this->MetaConf[$Set_Array['field']]['FieldName']."=''";
					    $SQL_Condition_Set['sphinx']['field']		= "@".$this->MetaConf[$Set_Array['field']]['FieldName']." ";
				        $SQL_Condition_Set['sphinx']['link']		= ' = ';
					    $SQL_Condition_Set['sphinx']['value'][]	    = '""';    
					  }else{
						$SQL_Operator = self::Set_SQL_Connect_Operator($Set_Array['attr'] , $this->MetaConf[$Set_Array['field']]['SearchType'] ,$this->MetaConf[$Set_Array['field']]['MatchType']);
					    $SQL_Condition_Set['mysql'][]=$this->MetaConf[$Set_Array['field']]['FieldName'].$SQL_Operator['link']."'".$SQL_Operator['left'].$Term.$SQL_Operator['right']."'";
					  
					    $SQL_Operator = self::Set_SQL_Connect_Operator($Set_Array['attr'] , 'fulltext' ,$this->MetaConf[$Set_Array['field']]['MatchType']);
					    $SQL_Condition_Set['sphinx']['field']		= "@".$this->MetaConf[$Set_Array['field']]['FieldName']." ";
				        $SQL_Condition_Set['sphinx']['link']		= $SQL_Operator['link'];
					    $SQL_Condition_Set['sphinx']['value'][]	= '"'.$Term.'"';  
					  }
					  
					  
				      break;
				  }
				  
				}else{
				  $Term_Attr_Count[$Set_Array['attr']]--;
				  //遮蔽條件  不處理此項
				}
			  }
			  break;  // end of normal mode
		  }
		  
		  /*******----- 串連所有條件 -----*******/
		  //串連mysql sql
		  $Temp_Condition_Join_String = '';
		  if(count($SQL_Condition_Set['mysql'])){
			$Temp_Condition_Join_String='('.join(' OR ',$SQL_Condition_Set['mysql']).')';
	        
			//處理條件反向條件
	        $Query_Set_Sql_Format_Array['mysql'][] = ($Set_Array['attr']=='-') ? preg_replace('/\sOR\s/',' AND ',$Temp_Condition_Join_String) : $Temp_Condition_Join_String ;
		  }
	          
		  //串連 sphinx sql
		  $Temp_Condition_Join_String = '';
	      if(count($SQL_Condition_Set['sphinx']['value'])){
	        $Temp_Condition_Join_String = join('|',$SQL_Condition_Set['sphinx']['value']);
	        $Query_Set_Sql_Format_Array['sphinx'][] = $SQL_Condition_Set['sphinx']['field'].$SQL_Condition_Set['sphinx']['link'].'('.$Temp_Condition_Join_String.')';
	      }
		}
		
		if($Term_Attr_Count['-'] > 0 ){
          unset($Term_Attr_Count['-']); 		  
		  if( !array_sum($Term_Attr_Count) ){
		    unset($Query_Set_Sql_Format_Array);
			throw new Exception('_ARCHIVE_SEARCH_TOTAL_TERM_INVERSE_ILLEGAL'); 		    
		  }
		}
		

		/*******----- 以下檢查檢索條件串  -----*******/
		
		// 檢查檢索條件
		if(!count($Query_Set_Sql_Format_Array['mysql'])){
		  // 如果沒有檢索條件，則創一永不成立條件，保持SQL不出錯 
		  $Query_Set_Sql_Format_Array['mysql'][] = "system_id='0'";
		}
		
		//檢查是否有全文檢索條件   
        //而且  termpat clipterm 都不使用全文檢索  因為會有  少案 以及 排除等狀況
        //   因為全文檢索引擎會先把20筆資料取出  才比對是否有符合墜詞條件  所以會有某頁但是都沒有符合條件的資料
        //   另外  因為 sphinx 不支援只有   -xxx 的條件  所以在排除贅詞時會出現錯誤
        //   因此目前使用贅詞系統時  就  不使用全文檢索引擎!!
        if(count($Query_Set_Sql_Format_Array['sphinx']) && $Search_Ap_Flag===0){
          $Search_FT_Flag = TRUE;
        }else{
          $Search_FT_Flag = FALSE;
	      $Query_Set_Sql_Format_Array['sphinx'][]="@system_id '0'";
        }
		
		// 使用者選擇模式,不適合使用全文檢索
		if($this->UserConf['MODE_ResultMode'] == '_selected'){
		  $Search_FT_Flag = FALSE;
		}
		$Search_FT_Flag = FALSE;
		
		/*******----- 以下加入個人化附加條件  -----*******/
		if($this->UserConf['MODE_ResultMode'] == '_selected'){
		  $Query_Set_Sql_Format_Array['mysql'][] 	 = "SelecterId='".$this->USER->UserID."'";
		  $Query_Set_Sql_Format_Array['mysql'][] 	 = "_keep=1";
	      $Query_Set_Sql_Format_Array['sphinxadd'][] = "SelecterId='".$this->USER->UserID."'";
		}
		
		/*******----- 國傳司開放條件  -----*******/
		$Query_Set_Sql_Format_Array['mysql'][] 	 = "(classlevel !='' OR upload_user='".$this->USER->UserID."')";
		$Query_Set_Sql_Format_Array['mysql'][] 	 = "_keep=1";
		$Query_Set_Sql_Format_Array['sphinxadd'][] = "upload_user='".$this->USER->UserID."'";
		
		
		
		/*******----- 以下組合條件  -----*******/
		
		$Final_Query_SQL = array('mysql'=>array(),'sphinx'=>array());
		$FT_Query_Order_Set = json_decode(_SYSTEM_ORDER_CONF,true);
		
		//組合所有條件
        //$ConditionSqlString = "Select * from metadata where ".join(' and ',$QuerySetSqlFormat)." order by ".$SysSet['Order_By'];
        $Final_Query_SQL['mysql']['head'] = SQL_AdArchive::SEARCH_SQL_HEADER_MYSQL($this->USER->UserID).$Query_Set_Sql_Format_Array['mysqljoin'] ;
		$Final_Query_SQL['mysql']['body'] = "WHERE ".join(' AND ',$Query_Set_Sql_Format_Array['mysql'])." ";
		$Final_Query_SQL['mysql']['foot'] = "ORDER BY ".$this->UserConf['SET_OrderByTarget']." ".$this->UserConf['SET_OrderByMethod'].", system_id ".$this->UserConf['SET_OrderByMethod'];
		
		if($Search_FT_Flag){
          $FT_Sort_Condition = "sort=extended:".$FT_Query_Order_Set[$this->UserConf['SET_OrderByTarget']]." ".$this->UserConf['SET_OrderByMethod'].";";
		  $FT_Add_Condition  = count($Query_Set_Sql_Format_Array['sphinxadd']) ? " AND ".join(" AND ",$Query_Set_Sql_Format_Array['sphinxadd']):"";
		  
		  $Final_Query_SQL['sphinx']['head'] = SQL_AdArchive::SEARCH_SQL_HEADER_SPHINX($this->USER->UserID);
		  $Final_Query_SQL['sphinx']['body'] = "WHERE query='".join('&',$Query_Set_Sql_Format_Array['sphinx']).";mode=ext;limit="._SYSTEM_SEARCH_SPHINX_LIMIT.";maxmatches="._SYSTEM_SEARCH_SPHINX_LIMIT.";".$FT_Sort_Condition."' ";
		  $Final_Query_SQL['sphinx']['foot'] = $FT_Add_Condition;
		}
		
		/*******----- 以下註冊檢索條件  -----*******/
		$DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::INSERT_SEARCH_TEMP());
		
		$DB_OBJ->bindParam(':UAID'		 , $this->USER->UserID, PDO::PARAM_STR,(strlen(_SYSTEM_LOGIN_ID_HEADER)+_SYSTEM_LOGIN_ID_LENGTH));
        $DB_OBJ->bindValue(':SQL_Mysql'  , join('',$Final_Query_SQL['mysql']), PDO::PARAM_STR); 
		$DB_OBJ->bindValue(':SQL_Sphinx' , join('',$Final_Query_SQL['sphinx']), PDO::PARAM_STR); 
		$DB_OBJ->bindValue(':Query_Set'  , join('⊕',$User_Query_Set), PDO::PARAM_STR); 
		$DB_OBJ->bindParam(':ACTION'  	 , $this->SearchInfo['action_from'], PDO::PARAM_STR); 
		$DB_OBJ->bindValue(':ACCNUM'  	 , $this->SearchInfo['access_from']?$this->SearchInfo['access_from']:0, PDO::PARAM_INT); 
		
		
		if(!$DB_OBJ->execute()){
          throw new Exception('_ARCHIVE_SEARCH_REGIST_FAILS');
		}  
  
		$New_Query_Access_Num = $this->DBLink->lastInsertId();
		$this->SearchInfo['access_num']		= $New_Query_Access_Num;
		$this->SearchInfo['sql_mysql']		= join('',$Final_Query_SQL['mysql']);
		$this->SearchInfo['sql_sphinx']		= join('',$Final_Query_SQL['sphinx']);
		$this->SearchInfo['query_set']		= join('⊕',$User_Query_Set);
		$this->SearchInfo['search_mode'] 	= $Search_FT_Flag ? 'sphinx':'mysql';
		$this->SearchInfo['search_ap']		= $Search_Ap_Flag ? true : false ;		
        
	    
	  } catch (Exception $e) {
        $message[] = $e->getMessage();
      }
	
	}
	
	
	
	/************************************************
	  取得檢索資料
	    參數  none  
	
	    回傳
		  $System_Query  // 系統檢所所需之相關資訊
		  
        提醒
		  取得建構好得 set 為 $this->UserQuerySet
		  
	***********************************************/
	
	public function ADArchive_Built_Result_Data(){
	
	  $System_Query		= array('result_num'=>'','search_sql'=>'','link_map'=>'','result_data'=>array(),'search_term'=>'',
	                            'access_num'=>'','query_set'=>'','page_now'=>'','page_num'=>'','page_data'=>array());
								
	  $User_Conf_ReSet	= array();
	  
	  $result_key = parent::Initial_Result('result');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $access_num 	= 0;       // 繼承檢索序號
	  $add_query_set= array(); // 新檢索條件 
 	  
	  try{
		
        if( count($this->SearchInfo) < 3  ){
		  throw new Exception('_ARCHIVE_SEARCH_DATA_FALSE');
		}
        
        $DB_Data = array();
         
		if($this->SearchInfo['action_from'] == 'page'){
		  $System_Query['result_num'] = $this->UserConf['TEMP_Last_QueryResultNum'];
		  $System_Query['search_sql'] = urldecode($this->UserConf['TEMP_Last_QuerySql']); 
		}else{
		  switch($this->SearchInfo['search_mode']){
		    case 'sphinx':
              $excute_sql = str_replace(array('SELECT * FROM','sort=extended:stno ASC;'),array('SELECT count(*) FROM',''),$this->SearchInfo['sql_sphinx']);
			  $DB_RESULT = $this->DBLink->query($excute_sql);
			  $DB_Data   = $DB_RESULT->fetch(PDO::FETCH_NUM);
		      
			  if($DB_Data[0]){
				$System_Query['search_sql'] = $this->SearchInfo['sql_sphinx'];
				break;
			  }  
			
			case 'mysql':
			  //var_dump($this->SearchInfo['sql_mysql']);
			  $DB_RESULT = $this->DBLink->query(preg_replace('/SELECT \* FROM/','SELECT count(*) FROM',$this->SearchInfo['sql_mysql']));
			  $DB_Data   = $DB_RESULT->fetch(PDO::FETCH_NUM);
			  $System_Query['search_sql'] = $this->SearchInfo['sql_mysql'];
			  break;
		  }
		  $System_Query['result_num'] = $DB_Data[0];
		}

		//Get Search Result Data
		if(intval($System_Query['result_num'])){
		  
		  // Built Query SQL
		  $Data_Start_Point = (($this->UserConf['SET_PageNow']-1)*$this->UserConf['SET_PageNum']);
		  if(preg_match('/FROM metadatafts/',$System_Query['search_sql'])){
	        $Page_Result_Sql = preg_replace('/limit=\d+;maxmatches=\d+;/',"limit=".$this->UserConf['SET_PageNum'].";offset=".$Data_Start_Point.";maxmatches=".intval($Data_Start_Point+$this->UserConf['SET_PageNum']).";",$System_Query['search_sql']);
	      }else{
	        $Page_Result_Sql = $System_Query['search_sql'];//建立檢索SQL
	      }

		  // Built Result Data
		  $get_page_result = self::ADArchive_Loading_Results($this->SearchInfo['access_num'] , 0);
		  $System_Query['result_data'] = $get_page_result['data']['records'];
		  
		}else{
		  $System_Query['search_term'] = $this->UserConf['TEMP_Last_QueryTerm'];
		}
		
		// 重新設定 user conf
		$User_Conf_ReSet['TEMP_Last_QueryAccNum']	 = $this->SearchInfo['access_num'];
		$User_Conf_ReSet['TEMP_Last_QueryFrom']		 = $this->SearchInfo['action_from'];
        $User_Conf_ReSet['TEMP_Last_QuerySet']		 = $this->SearchInfo['query_set'];
		$User_Conf_ReSet['TEMP_Last_QuerySql'] 	 	 = urlencode($System_Query['search_sql']);
		$User_Conf_ReSet['TEMP_Last_QueryTime']		 = date('Y-m-d H:i');
		$User_Conf_ReSet['TEMP_Last_QueryResultNum'] = $System_Query['result_num'];	
		
		if(isset($this->SearchInfo['Breadcrumbs'])){
		  $this->SearchInfo['Breadcrumbs']['accno']  = $this->SearchInfo['access_num'];
		  $this->SearchInfo['Breadcrumbs']['result'] = $System_Query['result_num'];
		  if($last = end($this->UserConf['TEMP_Query_Breadcrumbs'])){
		    if( $last['link'] != $this->SearchInfo['Breadcrumbs']['link']){
			  $this->UserConf['TEMP_Query_Breadcrumbs'][]  = $this->SearchInfo['Breadcrumbs'];  
		    } 
		  }else{
		    $this->UserConf['TEMP_Query_Breadcrumbs'][]  = $this->SearchInfo['Breadcrumbs'];	
		  }
		  $User_Conf_ReSet['TEMP_Query_Breadcrumbs'] = $this->UserConf['TEMP_Query_Breadcrumbs'];	
		}
		
		
		//寫入 User conf
	    if(count($User_Conf_ReSet)){
	      $this->Config->Write($User_Conf_ReSet);
		  $this->UserConf = $this->Config->Read();
	      unset($User_Conf_ReSet);
		}
		
		$System_Query['access_num'] = $this->SearchInfo['access_num'];
		$System_Query['sort_target'] 	= $this->UserConf['SET_OrderByTarget']; 
		$System_Query['sort_method'] 	= $this->UserConf['SET_OrderByMethod']; 
		$System_Query['query_breadcrumbs'] 	= $this->UserConf['TEMP_Query_Breadcrumbs']; 
		
		
		$result['data']=$System_Query;
		$result['action']=true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	  
	  return $result;
	}
	
	
	
	//-- Admin Archive Built Post Query
	// [input] : $accnum 
	public function ADArchive_Loading_Results($AccNo = 0 , $Slot=0){
	  
	  $result_key = parent::Initial_Result();
	  $result     = &$this->ModelResult[$result_key];
	  
	  $search_field_map = array(
	    
		'system_id'=>'系統序號',
	    'identifier'=>'系統編號',
	    'classlevel'=>'檔案類別',
	    'file_name'=>'檔案名稱',
	    'file_type'=>'檔案型態',
	    'doc_type'=>'檔案類別',
	    'title_main'=>'檔案標題',
	    'title_second'=>'檔案副標題',
	    'organ_main'=>'主管機關',
	    'organ_work'=>'執行單位',
	    'execute_year'=>'執行年度',
	    'execute_person'=>'執行人',
	    'contact_person'=>'連絡人',
	    'research_domain'=>'研究領域',
	    'research_member'=>'研究人員',
	    'research_property'=>'研究屬性',
	    'research_method'=>'研究方法',
	    'research_time'=>'研究期間',
	    'research_area'=>'研究區域',
	    'funding'=>'經費項目',
	    'keywords'=>'關鍵詞彙',
	    'abstract'=>'摘要',
	    'cataloge'=>'目錄',
	    'provider'=>'提供者',
	    'PQ_YearNum'=>'年分',
		
	  );
	  
	  try{
	    
		if(!intval($AccNo) ){  // fist page
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		  
		}
		
		$DB_OBJ		= $this->DBLink->prepare(SQL_AdArchive::GET_SEARCH_TEMP_RECORD());
		$DB_Result 	= $DB_OBJ->execute(array('ACCNUM'=>$AccNo,'UAID'=>$this->USER->UserID)); 
		$DB_Data	= $DB_OBJ->fetch(PDO::FETCH_ASSOC);
        $set_query  = $DB_Data['Query_Term_Set'];
		$sql_query  = ($DB_Data['Sql_2']) ? $DB_Data['Sql_2'] : $DB_Data['Sql_1'];
		
		$data_each_solt   = isset($this->UserConf['SET_PageNum']) ? $this->UserConf['SET_PageNum'] : _SYSTEM_SEARCH_PAGE_DATA_NUM; 
	    $data_start_point = $Slot * $data_each_solt;
		
		//處理擊中顯示
		$query_pattern = explode('⊕',$set_query);
		$search_hit    = array();  
		
		foreach($query_pattern as $qp){
		  list($f,$term,$att) = explode(':',$qp);
          if( $f!='kwds' && !$this->MetaConf[$f]['SearchState'] ) continue;  // 確認是可檢索之欄位
		  if(!isset($search_hit[$this->MetaConf[$f]['FieldName']]))	$search_hit[$this->MetaConf[$f]['FieldName']] = array();  
		  $search_hit[$this->MetaConf[$f]['FieldName']][] = $term;
		}
		
		$records  = array();
		$number   = 1;
		
		//建立檢索SQL
		if(preg_match('/FROM metadatafts/',$sql_query)){
	      $page_result_sql = preg_replace('/limit=\d+;maxmatches=\d+;/',"limit=".$data_each_solt.";offset=".$data_start_point.";maxmatches=".intval($data_start_point+$data_each_solt).";",$sql_query);	
	    }else{
	      $page_result_sql = $sql_query;  //.' LIMIT '.$data_start_point.','.$data_each_solt;
	    }
		$DB_Query = $this->DBLink->query($page_result_sql);
		while($tmp = $DB_Query->fetch(PDO::FETCH_ASSOC)){
		  $match = array();
		  
		  
		  // 將搜尋資料標示
		  foreach($search_hit as $sf => $sc){  
			if($sf=='KeyWords'){
			  foreach($tmp as $mf=>$mv){  
				if(!isset($search_field_map[$mf])) continue;
				foreach($sc as $s){
				  if(preg_match("/^(.*?)(".$s.")(.*)/u",$mv,$mths)){
					if(mb_strwidth($mv) < 34 ){
					  $match[] = "<span title='".$search_field_map[$mf]."' >".preg_replace('/('.$s.')/u',"<em class='match'>\\1</em>",$mv)."</span>";	
					}else{
					  $match[] = "<span title='".$search_field_map[$mf]."' >…".mb_substr($mths[1],-3)."<em class='match'>".$mths[2]."</em>".$mths[3]."</span>";	 		
					}
				  }
				}
			  }
			}else{
			  if(!isset($tmp[$sf]) || !isset($search_field_map[$sf])) continue;			
              foreach($sc as $s){
				if(preg_match("/^(.*?)(".$s.")(.*)/u",$tmp[$sf],$mths)){
				  if(mb_strwidth($tmp[$sf]) < 34 ){	
				    $match[] = "<span title='".$search_field_map[$sf]."' >".preg_replace('/('.$s.')/u',"<em class='match'>\\1</em>",$tmp[$sf])."</span>";
				  }else{
					$match[] = "<span title='".$search_field_map[$sf]."' >…".mb_substr($mths[1],-3)."<em class='match'>".$mths[2]."</em>".$mths[3]."</span>";	   
				  }
				}
			  }
			} 
		  }
		  
		  if(!count($match))  $match[] = $tmp['fulltexts'] ? System_Helper::short_string_utf8($tmp['fulltexts'],34) : $tmp['file_name'];
		  
		  $records[($data_start_point+$number)] = $tmp;
		  
		  $records[($data_start_point+$number)]['@title'] = array();
          
		  if(trim($tmp['title_main'])) $records[($data_start_point+$number)]['@title'][] = $tmp['title_main'];
		  if(trim($tmp['title_second'])) $records[($data_start_point+$number)]['@title'][] = $tmp['title_second'];
		  if(!count($records[($data_start_point+$number)]['@title']))  if(trim($tmp['file_name']))  $records[($data_start_point+$number)]['@title'][] = $tmp['file_name'];
		  
		  $records[($data_start_point+$number)]['@rdomain'] = array_filter(explode(';',$tmp['research_domain']));
		  $records[($data_start_point+$number)]['@rarea']   = array_filter(explode(';',$tmp['research_area']));
		  $records[($data_start_point+$number)]['@rmethod'] = array_filter(explode(';',$tmp['research_method']));
		  
		  $records[($data_start_point+$number)]['match'] = $match;
		  $number++;
		}
		$next_slot = ( count($records) ==  $data_each_solt) ? $Slot+1 : '-';
		$result['data']   = array('records'=>$records ,'slot'=>$next_slot ); 
		$result['action'] = true;
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	
	  return $result;	
	}
	
	//-- Admin Archive Built Post Query
	// [input] : $accnum 
	public function ADArchive_Built_Post_Query_Filter($accnum = 0){
	  $time_start = microtime(true);
	  $result_key = parent::Initial_Result();
	  $result     = &$this->ModelResult[$result_key];
	  
	  $PQ_Conf 	  = json_decode(_SYSTEM_AP_POST_QUERY_LEVEL_SET,true);
	  
	  $filter_data  = array();
	  $filter_queue = array();
	  $filter_terms = array();
	  foreach($PQ_Conf as $pqfield=>$pqconfig){
        $filter_queue[$pqfield] = array();  
	  }
	  $filter_terms = $filter_queue;
	 
	  try{
	    
		if(!intval($accnum)){  // fist page
		  		  
		}else if($this->UserConf['TEMP_Last_QueryResultNum']===0){
			
		}else{
		  ini_set("memory_limit","1024M");
		  
		  $sql_query  = urldecode($this->UserConf['TEMP_Last_QuerySql']);
		  $sql_pquery = preg_replace('/SELECT \* FROM/','SELECT '.join(',',array_keys($filter_queue)).' FROM',$sql_query);
		  $DB_Query  = $this->DBLink->query($sql_query);
		  $i=0;
		  
		  $terms = array();
		  
		  while($search = $DB_Query->fetch(PDO::FETCH_ASSOC)){
			
			foreach($filter_queue as $pqfield => $pqqueue){
			  if( trim($search[$pqfield]) && $search[$pqfield]!='none' ){
				/* 太慢 改為 array_count_values 計算
				$terms = array_unique(array_filter(explode(';',$search[$pqfield])));  
				foreach($terms as $term){
				  if(!isset($filter_queue[$pqfield][$term])) $filter_queue[$pqfield][$term] = 0;
				  $filter_queue[$pqfield][$term]+=1;
				}
				*/
				$filter_terms[$pqfield][] = $search[$pqfield];
			  }
			}
		  }
		  
		  $filter_queue = $filter_terms;
		  
		  foreach($filter_queue as $pqfield => $pqterms){  
			$post_query = array_count_values(array_filter(explode(';',join(';',$pqterms))));  
			 
			$PQ_Sort_Method = isset($PQ_Conf[$pqfield]['sort']) ? $PQ_Conf[$pqfield]['sort'] : '' ;
			switch($PQ_Sort_Method){
				case 'arsort' : arsort($post_query);break;
				case 'ksort'  : ksort($post_query);break;
				case 'krsort' : krsort($post_query);break;
				case 'rsort'  : rsort($post_query);break;
				default: arsort($post_query);break;
			}
			$list =array();
			foreach($post_query as $t=>$c){
			  $list[] = array('name'=>$t , 'view'=> System_Helper::short_string_utf8($t,30) ,'count'=>$c);
			}
			$filter_data[$pqfield] = array('field'=>$PQ_Conf[$pqfield]['code'],'terms'=>array_slice($list,0,50)) ;
		  } 
		}
		
		$result['data']   = $filter_data; 
		$result['action'] = true;
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	  
	  return $result;	
	}
	
	
	//-- Admin Archive Read Metadata
	// [input] : $DocumentId  : metadata identifier 
	public function ADArchive_Read_Object_Meta($DocumentId='' ){
	  
	  $result_key = parent::Initial_Result('read');
	  $result     = &$this->ModelResult[$result_key];
	  
	  try{
	    
		if(!preg_match('/^[\w\d\-]+$/',$DocumentId) ){  // ORL_150708_00034686
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		  
		}
		
		$identifier = $DocumentId;
		$meta     = array();
		
		// 查詢資料
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA());
		$DB_Query->bindValue(':id',$identifier); 
		
	    if(!$DB_Query->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		if(!$meta = $DB_Query->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$pho_info = array();
		
		//$meta['objsrc'] 	= 'index.php?act=Archive/photo.php?src='.$meta['_store'].$meta['identifier'];
			
	    $file_name = $meta['identifier'].'.'.$meta['file_type'];
		$file_path = _SYSTEM_FILE_PATH.'document/'.substr($meta['identifier'],0,1).'/'.$file_name;		
		
		// 確認檔案
		if(!is_file($file_path)){
		  $file_path = 'theme/image/error_file_not_found.png';			  
		  $meta['objsrc'] 	= $file_path;
		  $meta['file_type'] = 'png';
		}else if(strtolower($meta['file_type'])!='pdf'){
		  $file_path = 'theme/image/error_file_un_view.png';		
		  $meta['objsrc'] 	= $file_path;	  
		  $meta['file_type'] = 'png';
		}else{
		  $meta['objsrc'] 	= 'index.php?act=Archive/preview/'.$meta['identifier']; //(安全疑慮暫時關閉)	
		}
		
		$meta['tags']		= $meta['tags'];
		
		$meta['editable']	= 1;	
		if(in_array('R01',$this->USER->PermissionNow['group_roles']) || 
		   in_array('R03',$this->USER->PermissionNow['group_roles']) || 
		   in_array('R00',$this->USER->PermissionNow['group_roles'])){
		  $meta['editable']	= 1;	
		}
		
		// 取得所在資料夾
		$folders = array();
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_FOLDERS());
		$DB_Query->bindValue(':sid',$meta['system_id']); 
		if( $DB_Query->execute() ){
		  $folders = $DB_Query->fetchAll(PDO::FETCH_ASSOC);
		}		
		
		// 取得下載紀錄
		$DB_Count = $this->DBLink->prepare(SQL_AdArchive::COUNT_FILE_DOWNLOAD());
		$DB_Count->bindValue(':id',$meta['identifier']); 
		$DB_Count->execute();
		$meta['download'] 	= intval($DB_Count->fetchColumn());
		
		
		$result['session']['identifier'] = $meta['identifier'];
		$result['data']   = array('meta'=>$meta , 'folder'=>$folders ); 
		$result['action'] = true;
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	
	  return $result;	
	}
	
	
	//-- Admin Archive Save Meta Data 
	// [input] : DocumentId    :  \d+  = DB.user_info.uid;
	// [input] : MetaModify  :  urlencode(base64Mencode(json_pass()))  = array( 'field_name'=>new value , ......   ) ;  // 修改之欄位 - 變動
	// [input] : SessImgid  :   SESSION identifier ; // set from meta read
	
	public function ADArchive_Save_Target_Meta( $DocumentId=0 , $MetaModify='' , $SessImgid=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  $meta_modify = json_decode(base64_decode(str_replace('*','/',rawurldecode($MetaModify))),true);
	  
	  
	  if( in_array('R04',$this->USER->PermissionNow['group_roles']) )  
	    $meta_edit_field  = array('photo_keywords','classlevel','tags');
	  
	  if( in_array('R00',$this->USER->PermissionNow['group_roles']) ||
	      in_array('R01',$this->USER->PermissionNow['group_roles']) ||
		  in_array('R03',$this->USER->PermissionNow['group_roles']) )
        $meta_edit_field  = array('photo_name','photo_title','photo_descrip','photo_keywords','photo_locat','photo_date','creater','score','classlevel','tags','PQ_YearNum'); 		  
	  
	  $class_mdf = array();
	  
	  try{  
		
		// 檢查使用者序號
	    if(!preg_match('/^[\w\d\-]+$/',$DocumentId)  || !is_array($meta_modify) || $DocumentId!=$SessImgid ){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 檢查更新欄位是否合法
		foreach($meta_modify as $mf => $mv){
		  
		  
		  if($mf=='photo_date' && strtotime($mv)){
			$meta_modify['PQ_YearNum'] = date('Y',strtotime($mv)).' - 民國'.(date('Y',strtotime($mv))-1911).'年';
		  }
		  
		  if($mf=='keywords'){
			$meta_modify['keywords']=preg_replace('/[;，,]/u',';',$meta_modify['keywords']);
		  }
		  
		  // 處理標籤
		  if($mf=='tags'){
            $tags = preg_split('/;|；/',$meta_modify['tags']);
		    foreach($tags as $i=>$t){
			  $tset = explode(':',$t);
			  if(count($tset)==3){
				$DB_TAG	= $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_CREATE_USER_TAG()); 
				if($tset[0]=='new'){
				  $DB_TAG->bindValue(':owner'    , $this->USER->UserNO);
				  $DB_TAG->bindValue(':tag_term' , $tset[2]);
                  $DB_TAG->execute();
                  $tset[0] = $this->DBLink->lastInsertId(); 
				  $tset[1] = intval($this->USER->UserNO); 
                  $tags[$i] = join(':',$tset);				  
				}else{
				  $tags[$i] = $t;		
				}
			  }else{
				unset($tags[$i]);
			  }
		    }
			$tags = array_filter($tags);
			$meta_modify['tags'] = join(';',$tags).';';
		  }
		  
		  if($mf=='classlevel'){
			  
			if($mv){
			  $meta_modify['classlevel'] = preg_match('/;$/',$mv) ? $mv : $mv.';';	
			}  
			
		    /*  紀錄受影響之類別 */
			// 查詢舊資料
		    $DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA());
		    $DB_Query->bindValue(':id',$DocumentId); 
		    $DB_Query->execute();
		    $meta = $DB_Query->fetch(PDO::FETCH_ASSOC);
		    $class_mdf = array_filter(explode(';',$meta['classlevel'].$meta_modify['classlevel'])); 
		  
		  }
		}
		
		
		if(count($meta_modify)){
		  $meta_modify['update_user'] = intval($this->USER->UserNO);	
			
		  // 執行更新
		  $DB_SAVE	= $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_UPDATE_META_DATA(array_keys($meta_modify)));
		  $DB_SAVE->bindValue(':identifier' , $SessImgid);
		  foreach($meta_modify as $mf => $mv){
			$DB_SAVE->bindValue(':'.$mf , $mv);
		  }
		  if( !$DB_SAVE->execute()){
			throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		  }  
		}
		
		// 重新計算class file
		if(count($class_mdf)){
		  $DB_UPD = $this->DBLink->prepare("UPDATE search_level SET search_level.count=(SELECT count(*) FROM metadata WHERE classlevel LIKE :class_search AND _keep=1) WHERE info =:class_term AND site=:site;");	
		  foreach($class_mdf as $class){
			$cllv = explode('/',$class);
			do{
			  $DB_UPD->bindValue(':class_search', "%".join('/',$cllv)."%"); 
		      $DB_UPD->bindValue(':class_term'	, join('/',$cllv));
			  $DB_UPD->bindValue(':site'	, count($cllv));			  
			  $DB_UPD->execute();
			  array_pop($cllv);
			}while(count($cllv));
		  }
		}
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Admin Archive Delete Photo Data
	// [input] : $DocumentId  : image id
	public function ADArchive_Delete_Target_Data($DocumentId='' ){
	  
	  $result_key = parent::Initial_Result();
	  $result     = &$this->ModelResult[$result_key];
	  
	  try{
	    
		if(!preg_match('/^[\w\d\-]+$/',$DocumentId) ){  // ORL_150708_00034686
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');		  
		}
		
		$image_id = $DocumentId;
		$meta     = array();
		
		// 查詢資料
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA());
		$DB_Query->bindValue(':id',$image_id); 
		
	    if(!$DB_Query->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		if(!$meta = $DB_Query->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// 取得所在資料夾
		$folders = array();
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_FOLDERS());
		$DB_Query->bindValue(':sid',$meta['system_id']); 
		if( $DB_Query->execute() ){
		  $folders = $DB_Query->fetchAll(PDO::FETCH_ASSOC);
		}		
		
		// 確認權限
		if(!in_array('R01',$this->USER->PermissionNow['group_roles']) && $meta['upload_user']!=$this->USER->UserID){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');		
	    }
		
		// 刪除資料夾關聯檔案  folder_map
		$DB_DELFM = $this->DBLink->prepare(SQL_AdArchive::REMOVE_FROM_FOLDER_MAP());
		$DB_DELFM->execute(array('sid'=>$meta['system_id']));
		
		
		// 設定檔案為 _keep=0
		$DB_DELFM = $this->DBLink->prepare(SQL_AdArchive::REMOVE_FROM_METADATA());
		$DB_DELFM->execute(array('user'=>$this->USER->UserNO, 'sid'=>$meta['system_id']));
		
		// 更新資料夾數量
		$folder_set = array();
		$DB_UDPF = $this->DBLink->prepare(SQL_AdArchive::SUB_COUNT_USER_FOLDER());
		foreach($folders as $f){
		  $DB_UDPF->execute(array("ufno"=>$f['ufno']));	
		  $folder_set[] = $f['ufno'];
		}
		
		// 更新類別統計
		// !!: 單筆資料可能有多重分類，計算時上層分類可能要排除重複計算狀況
		$class_mdf = array_filter(explode(';',$meta['classlevel']));
		if(count($class_mdf)){
		  $class_hit = array();
		  $DB_UDSL = $this->DBLink->prepare(SQL_AdArchive::SUB_COUNT_SYSTEM_LEVEL());
		  foreach($class_mdf as $class){
			$cllv = explode('/',$class);
			do{
			  $cltarget = join('/',$cllv);
              if(!isset($class_hit[$cltarget])){
				$class_hit[$cltarget] = 1;
				$DB_UDSL->bindValue(':level' , $cltarget);
			    $DB_UDSL->bindValue(':site'	 , count($cllv));			  
			    $DB_UDSL->execute();
			  }
			  array_pop($cllv);
			}while(count($cllv));
		  }
		}
		
		
		
		$result['data']['folders'] = $folder_set;
		$result['action'] = true;
	  
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }	
	  return $result;	
	}
	
	
	
	//-- Download Photo  
	// [input] : DocumentId   : ;
	// [input] : ImageType : original / system;
	public function ADArchive_Download_Archive_Image( $DocumentId='',$ImageType){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 檢查資料序號
	    if(!preg_match('/^\w+\_\d{6}\_\d{8}$/',$DocumentId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		
		// 確認權限
		switch($ImageType){
		  case 'rawfile':
		  case 'original':
			if( !isset($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dwnorl']) || intval($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dwnorl'])==0 ) {
			  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		    }
			break;
		  
          case 'system':
			if( !isset($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dwnmin']) || intval($_SESSION[_SYSTEM_NAME_SHORT]['PERMISSION']['archive-dwnmin'])==0 ) {
			  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');	
		    }
            break;

          default:
		    throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');			
		    break;
		}
		
		
		// 查詢資料
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA());
		$DB_Query->bindValue(':id',$DocumentId); 
		
	    if(!$DB_Query->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		if(!$meta = $DB_Query->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		list($iname,$itype) = explode('.',$meta['image_name']);
		
		$image_name = $ImageType=='original' ? $meta['identifier'].'.'.strtolower($itype) : $meta['identifier'].'.jpg';
		
		// set EXIF
		$descrip  = substr(json_encode(array($meta['photo_descrip'])),2,-2); 
		$creater  = substr(json_encode(array($meta['creater'])),2,-2); 
		
		if(copy(_SYSTEM_FILE_PATH.$meta['_store'].$ImageType.'/'.$image_name,_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$image_name)){
		  exec(_SYSTEM_ROOT_PATH.'mvc/lib/exiv2-0.25-win/exiv2.exe -M"set Xmp.dc.title '.$descrip.'" -M"set Exif.Image.Artist '.$creater.'" '._SYSTEM_USER_PATH.$this->USER->UserID.'/'.$image_name  , $meta_update, $return_var);	
		}
		
		//Xmp.dc.title
		//Exif.Image.XPSubject 
		//case 'Exif.Image.ImageDescription':
		//case 'Exif.Image.Artist':
		//case 'Iptc.Application2.City':
        //case 'Iptc.Application2.Keywords':

		$result['data']['location'] = _SYSTEM_USER_PATH.$this->USER->UserID.'/'.$image_name;
		$result['data']['name']     = $image_name;
		$result['data']['size']     = filesize($result['data']['location']);
		
		
		$DB_DLOG = $this->DBLink->prepare(SQL_AdArchive::LOGS_FILE_DOWNLOAD());
		$DB_DLOG->bindValue(':file_id',$DocumentId); 
		$DB_DLOG->bindValue(':file_type',$ImageType); 
		$DB_DLOG->bindValue(':user_id',$this->USER->UserNO); 
		$DB_DLOG->bindValue(':user_ip',$this->USER->UserIP); 
		$DB_DLOG->bindValue(':request_url', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '' ); 
		$DB_DLOG->execute();
		
		// final 
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- User Remove Selected Photos  
	// [input] : SelectData : urlencode(json_pass())  = array(data=array(id,id));
	public function ADArchive_Remove_User_Selected( $SelectData=''){
	  
	  $result_key = parent::Initial_Result('remove');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $select_data = json_decode(rawurldecode($SelectData),true);   
	  $update_count=0;
	  $class_mdf   = array();
	  
	  try{
		  
		if(!isset($select_data['data']) || !count($select_data['data'])){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		$del_count = 0;
		$del_datas = array();
		
		foreach($select_data['data'] as $image_id){
		  $execute = self::ADArchive_Delete_Target_Data($image_id);  	
          if($execute['action']){
			$del_count++;
            $del_datas[] = $image_id; 			
		  }
		}
		
		$result['data']['count']   = $del_count;
		$result['data']['delete']  = $del_datas;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- User Update Photos Metadata 
	// [input] : UpdateData : urlencode(json_pass())  = array(field=>array(value='' , mode=+/-  ;  unify =0 1 data=array(id,id)));
	public function ADArchive_Update_User_Selected( $UpdateData=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  $update_data = json_decode(rawurldecode($UpdateData),true);   
	  $update_count=0;
	  $class_mdf   = array();
	  
	  try{
		  
		if(!$update_data || !count($update_data)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		foreach($update_data as $field=>$update_set){
		  
		  $multiple_value = '';
		  $update_sql = '';
		  
		  // 處理標籤
		  if($field=='tags'){
			$tags = array_filter(explode(';',$update_set['value']));  
			$upd_tags = array();
		    foreach($tags as $i=>$t){
			  $DB_TAG	= $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_SEARCH_USER_TAG()); 
			  $DB_TAG->execute(array('owner'=>$this->USER->UserNO,'tag'=>$t));
			  if($oldtag = $DB_TAG->fetch(PDO::FETCH_ASSOC)){  // 存在
				$upd_tags[] = $oldtag['utno'].':'.$oldtag['owner'].':'.$oldtag['tag_term'];
			  }else{
				$DB_NTAG	= $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_CREATE_USER_TAG()); 
				$DB_NTAG->bindValue(':owner'    , $this->USER->UserNO);
				$DB_NTAG->bindValue(':tag_term' , $t);
                $DB_NTAG->execute();
                $newtagno = $this->DBLink->lastInsertId(); 
                $upd_tags[] = $newtagno.':'.intval($this->USER->UserNO).':'.$t;
			  } 
		    }
		    $update_set['value']=join(';',array_filter($upd_tags));
		  }
		  
		  
		  if($update_set['mode']=='+'){
			if( ($field=='tags'||$field=='classlevel') && !preg_match('/;$/',$update_set['value'])){
			  $update_set['value'] = $update_set['value'].';';  
			}
			if(intval($update_set['unify'])){
			  $update_sql = $field." = '".$update_set['value']."'";	
			}else{
			  $update_sql = $field." = REPLACE(CONCAT(".$field.", '".$update_set['value']."'),';;',';')";		
			} 
		  }else{
			$update_sql = $field." = REPLACE(REPLACE(".$field.", '".$update_set['value']."',''),';;',';')";	  
		  }
		  
		  $DB_Query = $this->DBLink->prepare("UPDATE metadata SET ".$update_sql.",update_user=:update_user WHERE identifier = :identifier AND _keep=1;");
		  
		  foreach($update_set['data'] as $phoid){
			$DB_Query->bindValue(':update_user'	, $this->USER->UserNO); 
		    $DB_Query->bindValue(':identifier'	, $phoid); 
		    $DB_Query->execute();  
			$update_count++;
		  }
		  
		  /*  紀錄受影響之類別 */
		  if($field=='classlevel'){
			$class_mdf = array_unique(array_filter(explode(';',$update_set['value']))+$class_mdf); 
		  }
		}
		
		// 重新計算class file
		if(count($class_mdf)){
		  $DB_UPD = $this->DBLink->prepare("UPDATE search_level SET search_level.count=(SELECT count(*) FROM metadata WHERE classlevel LIKE :class_search AND _keep=1) WHERE info =:class_term AND site=:site;");	
		  foreach($class_mdf as $class){
			$cllv = explode('/',$class);
			do{
			  $DB_UPD->bindValue(':class_search'	, "%".join('/',$cllv)."%"); 
		      $DB_UPD->bindValue(':class_term'	, join('/',$cllv));
			  $DB_UPD->bindValue(':site'	, count($cllv));			  
			  $DB_UPD->execute();
			  array_pop($cllv);
			}while(count($cllv));
		  }
		}
		
		$result['data']['update']  = $update_count;
		$result['action'] = true;
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	//-- Initial Photo Upload Task 
	// [input] : UploadData : urlencode(json_pass())  = array(folder , creater , classlv , list=>array(name size type lastmdf=timestemp));
	// [input] : FILES : [array] - System _FILES Array;
	public function ADArchive_Upload_Task_Initial( $UploadData=''){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
	  $upload_data = json_decode(rawurldecode($UploadData),true);   
	  
	  try{
		  
		if(!$upload_data || !count($upload_data)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		$upload_time_flag = date('YmdHis');  // 用來識別task_upload file
		
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::REGIST_USER_UPLOAD_FOLDER());
		$DB_Query->bindValue(':owner'	, $this->USER->UserNO); 
		$DB_Query->bindValue(':ftype'	, 'folder'); 
		$DB_Query->bindValue(':name'	, $upload_data['folder']); 
		$DB_Query->bindValue(':path'	, '/'.$this->USER->UserID.'/'.$upload_data['folder']); 
		$DB_Query->bindValue(':updtime'	, $upload_time_flag); 
		
		if(!$DB_Query->execute()){
		  throw new Exception('_ARCHIVE_UPLOAD_REG_FOLDER_FAILS');	
		}
		
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_USER_UPLOAD_FOLDER());
		$DB_Query->bindValue(':owner'	, $this->USER->UserNO); 
		$DB_Query->bindValue(':name'	, $upload_data['folder']); 
		if(!$DB_Query->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		$folder = $DB_Query->fetch(PDO::FETCH_ASSOC);
		
		if(isset($folder['ufno'])){
		  if(!is_dir(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$folder['ufno'])){
			mkdir(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$folder['ufno']);	  
		  } 		  
		}
		
		// 快取使用者設定
		$result['session']['cache']['upload_folder_no'] = $folder['ufno'];
		$result['session']['cache']['upload_folder_na'] = $upload_data['folder'];
		

		// check exist file
		$checked  = array();
		$DB_Check  = $this->DBLink->prepare(SQL_AdArchive::CHECK_FILE_UPLOAD_LIST()); 
		if(count($upload_data['list'])){  
		  foreach($upload_data['list'] as $i=>$file){
			$checked[$i] = array();
			
			$flm = isset($file['lastmdf']) ? $file['lastmdf'] : date('Ymd');
			
			$hashkey = md5($file['name'].$file['size'].$flm);
			
			$DB_Check->bindValue(':hash',$hashkey);
			$DB_Check->execute();
			$chk = $DB_Check->fetchAll(PDO::FETCH_ASSOC);
			
			$checked[$i]['check']  = count($chk) ? 'double' : 'accept';
		  
		  }  
		}
		
		// return folder 
		$result['data']['folder'] = $folder['ufno'];
		$result['data']['tmflag'] = $upload_time_flag;
		$result['data']['check']  = $checked;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	//-- Upload Photo 
	// [input] : FolderCode   : [int] fno-fuploadtimeflag;
	// [input] : UploadMeta : accnum:urlencode(base64encode(json_pass()))  = array(F=>V);
	// [input] : FILES : [array] - System _FILES Array;
	public function ADArchive_Upload_Photo( $FolderCode='' , $UploadMeta='' , $FILES = array()){
	  
	  $result_key = parent::Initial_Result('upload');
	  $result  = &$this->ModelResult[$result_key];
	  
      // [name] => MyFile.jpg  / [type] => image/jpeg  /  [tmp_name] => /tmp/php/php6hst32 / [error] => UPLOAD_ERR_OK / [size] => 98174
	  // Allowed extentions.
      $notallowExts = array("exe");
      
      // Get filename.
      $temp = explode(".", $FILES["file"]["name"]);

      // Get extension.
      $extension = end($temp);
      
	  // Validate uploaded files.
	  // Do not use $_FILES["file"]["type"] as it can be easily forged.
	  $finfo = finfo_open(FILEINFO_MIME_TYPE);
	  $mime  = finfo_file($finfo, $FILES["file"]["tmp_name"]);
	  $upload_data = json_decode(base64_decode(str_replace('*','/',$UploadMeta)),true);   
	  
	  try{
		
		// 檢查參數
		if(!preg_match('/^\d+\-\d{14}$/',$FolderCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
     		
        list($folder_id,$upload_flag) = explode('-',$FolderCode);

		if (in_array(strtolower($extension), $notallowExts)) {
	      throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
	    }	
		
        if( $FILES["file"]["error"] ){
          throw new Exception('_SYSTEM_UPLOAD_ERROR:'.$FILES["file"]["error"]);  
        }
        
		
		//紀錄上傳檔案
		$hashkey = md5($FILES["file"]['name'].$FILES["file"]['size'].$FILES["file"]['lastmdf']);
		$DB_Regist = $this->DBLink->prepare(SQL_AdArchive::REGIST_FILE_UPLOAD_RECORD()); 
		$DB_Regist->bindValue(':utkid',0);
		$DB_Regist->bindValue(':folder',$folder_id);
		$DB_Regist->bindValue(':flag',$upload_flag);
		$DB_Regist->bindValue(':user',$this->USER->UserID);
		$DB_Regist->bindValue(':hash',$hashkey);
		$DB_Regist->bindValue(':creater',$upload_data['creater']);
		$DB_Regist->bindValue(':classlv',$upload_data['classlv']);
		$DB_Regist->bindValue(':name',$FILES["file"]['name']);
		$DB_Regist->bindValue(':size',$FILES["file"]['size']);
		$DB_Regist->bindValue(':mime',strtolower($FILES["file"]['type']));
		$DB_Regist->bindValue(':type',strtolower($extension));
		$DB_Regist->bindValue(':last',$FILES["file"]['lastmdf']);
		
		if(!$DB_Regist->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		$urno = $this->DBLink->lastInsertId();
		
		// 取得文件資料
		move_uploaded_file($FILES["file"]["tmp_name"], _SYSTEM_USER_PATH.$this->USER->UserID.'/'.$folder_id.'/'.str_pad($urno,8,'0',STR_PAD_LEFT).$hashkey);
		
		// 更新上傳紀錄
		$DB_Update = $this->DBLink->prepare(SQL_AdArchive::UPDATE_FILE_UPLOAD_UPLOADED()); 
		$DB_Update->bindValue(':urno',$urno );
		$DB_Update->execute();
		
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
      
	}
	
	//-- Finish Photo Upload Task 
	// [input] : FolderCode : int-timeflag;
	public function ADArchive_Upload_Task_Finish( $FolderCode=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		  
		if(!preg_match('/^\d+\-\d{14}$/',$FolderCode)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		list($folder_id,$upload_flag) = explode('-',$FolderCode);
		
		// 完成上傳  (傳輸紀錄不在 user_task 在 user_folder 紀錄，因為task是上傳完畢後才建立的)
		$DB_Update = $this->DBLink->prepare(SQL_AdArchive::FINISH_USER_UPLOAD_TASK());
		$DB_Update->bindValue(':ufno'	, intval($folder_id)); 
		$DB_Update->bindValue(':uno'	, $this->USER->UserNO); 
		if(!$DB_Update->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		// 查詢新上傳檔案
		$DB_PHO = $this->DBLink->prepare(SQL_AdArchive::SELECT_UPLOAD_PHOTO_LIST());
		$DB_PHO->bindValue(':folder', intval($folder_id)); 
		$DB_PHO->bindValue(':flag'	, $upload_flag); 
		$DB_PHO->bindValue(':user'	, $this->USER->UserID); 
		if(!$DB_PHO->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		$phos = $DB_PHO->fetchAll(PDO::FETCH_ASSOC);
		if(!count($phos)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		//註冊匯入工作
		$DB_Task = $this->DBLink->prepare(SQL_AdArchive::REGIST_USER_TASK()); 
		$DB_Task->bindValue(':user',$this->USER->UserNO);
		$DB_Task->bindValue(':task_name',"檔案上傳:"+count($phos));
		$DB_Task->bindValue(':task_type',"_PHO_IMPORT");
		$DB_Task->bindValue(':task_num',count($phos));
		$DB_Task->bindValue(':task_done',0);
		$DB_Task->bindValue(':time_initial',date('Y-m-d H:i:s'));
		$DB_Task->execute();
		$task_id = $this->DBLink->lastInsertId(); 
		
		// 將上傳資料綁定工作
		$DB_Bind = $this->DBLink->prepare(SQL_AdArchive::BIND_PHOTO_IMPORT_TASK()); 
		$DB_Bind->bindValue(':utkid',$task_id);
		foreach($phos as $pho){
		  $DB_Bind->bindValue(':urno',$pho['urno']);  	
		  $DB_Bind->execute();
		}
		
		// 開啟匯入程序
		//exec(_SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJobs/Job_Import_Upload_Files.php '.$task_id,$output);  // 做完才結束
		//file_put_contents('logs.txt',print_r($output,true));
		
		pclose(popen("start /b "._SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJobs/Job_Import_Upload_Files.php '.$task_id,"r"));  // 可以放著不管
		
		$result['data'] = $task_id;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	//-- Explode Photo Select And Initial Task 
	// [input] : ExportData : accnum:urlencode(json_pass())  = array('name'=>export_name,'type' => array('original' , 'system' , 'all') ,'data'=>array());
	public function ADArchive_Export_User_Selected( $ExportData=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  $export_data = json_decode(rawurldecode($ExportData),true);   
	  
	  try{
		  
		if(!count($export_data)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		if(!count($export_data['data'])){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// 找出所有的meta
		$meta_list = array();
		$DB_Meta = $this->DBLink->prepare(SQL_AdArchive::SELECT_EXPORT_META($export_data['data'])); 
		if(!$DB_Meta->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		while($tmp = $DB_Meta->fetch(PDO::FETCH_ASSOC)){
		  $meta_list[$tmp['identifier']] = $tmp;	
		}
		
		
		$export_name = isset($export_data['desc']) && trim($export_data['desc']) ? trim($export_data['desc']) : '圖片打包匯出';
		
		
		//註冊匯出工作
		$DB_Task = $this->DBLink->prepare(SQL_AdArchive::REGIST_USER_TASK()); 
		$DB_Task->bindValue(':user',$this->USER->UserNO);
		$DB_Task->bindValue(':task_name',$export_name);
		$DB_Task->bindValue(':task_type',"_PHO_EXPORT");
		$DB_Task->bindValue(':task_num' ,0);
		$DB_Task->bindValue(':task_done',0);
		$DB_Task->bindValue(':time_initial',date('Y-m-d H:i:s'));
		$DB_Task->execute();
		$task_id = $this->DBLink->lastInsertId(); 
		$file_count = 0;
		
		//紀錄匯出項目
		$DB_PHO = $this->DBLink->prepare(SQL_AdArchive::REGIST_FILE_EXPORT_RECORD());
		$DB_PHO->bindValue(':utkid'	, $task_id); 
		
		$export_type = isset($export_data['type']) && in_array($export_data['type'],array('alltype','system','original'))  ? $export_data['type'] : 'system';  
	    
		foreach($export_data['data'] as $meta_id){
		  
		  if(!isset($meta_list[$meta_id])) continue;  
		  
		  //system_id,identifier,image_name,image_orl,image_sys,image_tum,_store
		  $image = explode('.',$meta_list[$meta_id]['image_name']);
		  $file_export = array();  
		  
		  // 加入檔案
		  if($export_type == 'alltype' || $export_type == 'system' ){
			$file_export[] = array(
			  'locat'=>_SYSTEM_FILE_PATH.$meta_list[$meta_id]['_store'].'system/'.$meta_id.'.jpg',
			  'save'=>_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$meta_id.'_s.jpg'
			);  
		  }
		  if($export_type == 'alltype' || $export_type == 'original' ){
			$file_export[] = array(
			  'locat'=>_SYSTEM_FILE_PATH.$meta_list[$meta_id]['_store'].'original/'.$meta_id.'.'.strtolower($image[1]),
			  'save'=>_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$meta_id.'.'.strtolower($image[1])
			);  
		  }
		  
		  foreach($file_export as $file){
			$DB_PHO->bindValue(':meta_id' , $meta_id); 
			$DB_PHO->bindValue(':locat'	  , $file['locat']); 
			$DB_PHO->bindValue(':save'	  , $file['save']); 
			$DB_PHO->execute();
		    $file_count++;
		  }
		}
		// 重新計算數量
		$this->DBLink->query("UPDATE user_task SET task_num=".$file_count." WHERE utk=".$task_id.";"); 
		
		
		// 開啟匯出打包程序
		//exec(_SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJob/Job_Import_Upload_Files.php');  // 做完才結束
		pclose(popen("start /b "._SYSTEM_PHP_ROOT._SYSTEM_ROOT_PATH.'systemJobs/Job_Export_Selected_Files.php '.$task_id,"r"));  // 可以放著不管
		
		$result['data'] = $task_id;
		$result['action'] = true;
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Download Photo Package  
	// [input] : PackageHash : md5 string;
	public function ADArchive_Download_User_Package( $PackageHash=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		  
		if(strlen($PackageHash)!=32){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		// 取得資料夾
		$export = array();
		$DB_Folder = $this->DBLink->prepare(SQL_AdArchive::SELECT_EXPORT_FOLDER()); 
		$DB_Folder->bindValue(':hash', $PackageHash);
		if(!$DB_Folder->execute() || !$export = $DB_Folder->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// 取得包裹內容列表
		$pho_list = array();
		$DB_Task = $this->DBLink->prepare(SQL_AdArchive::SELECT_EXPORT_PHO_LIST()); 
		$DB_Task->bindValue(':utkid' , $export['owner']);
		if($DB_Task->execute()){ 
		  $pho_list = $DB_Task->fetchAll(PDO::FETCH_ASSOC);
		}
		// 確認檔案
		if(!is_file($export['path'])){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		// 紀錄
		$DB_DLOG = $this->DBLink->prepare(SQL_AdArchive::LOGS_FILE_DOWNLOAD());
		foreach($pho_list as $epho){
		  $DB_DLOG->bindValue(':file_id',$epho['meta_id']); 
		  $DB_DLOG->bindValue(':file_type','package'); 
		  $DB_DLOG->bindValue(':user_id',$this->USER->UserNO); 
		  $DB_DLOG->bindValue(':user_ip',$this->USER->UserIP); 
		  $DB_DLOG->bindValue(':request_url', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '' ); 
		  $DB_DLOG->execute();
		}
		
		// 更新下載次數
		$DB_DWCOUNT = $this->DBLink->prepare(SQL_AdArchive::UPD_PACKAGE_DOWNLOAD_COUNT());
		$DB_DWCOUNT->execute(array('hash'=>$PackageHash));
		
		
		$result['data']['name']   = _SYSTEM_NAME_SHORT.'_'.date('YmdHis').'.zip';
		$result['data']['size']   = filesize($export['path']);
		$result['data']['location']   = $export['path'];
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Put Select Photo Into New Folder  
	// [input] : ExportData : accnum:urlencode(json_pass())  = array('list'=>array(f1,f2,f3) ,'data'=>array());
	public function ADArchive_Putin_User_Selected( $ExportData=''){
	  
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  $select_data = json_decode(rawurldecode($ExportData),true);   
	  
	  try{
		  
		if(!count($select_data)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');	
		}  
		
		if(!count($select_data['data']) && !count($select_data['list'])){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// 找出所有的meta
		$meta_list = array();
		$DB_Meta = $this->DBLink->prepare(SQL_AdArchive::SELECT_EXPORT_META($select_data['data'])); 
		if(!$DB_Meta->execute() ){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		while($tmp = $DB_Meta->fetch(PDO::FETCH_ASSOC)){
		  $meta_list[$tmp['identifier']] = $tmp;	
		}
		
		// 處理資料夾
		$folder_return = array();
	    $folder_queue = array_unique($select_data['list']);
		$folder_putin = array();
		
		
		foreach($folder_queue as $folder){
		  
		  if(strval(intval($folder))===$folder){  // 加入舊資料夾
			$folder_putin[] = intval($folder); 
		  }else{  // 新增資料夾
			$DB_FCHK = $this->DBLink->prepare(SQL_AdArchive::CHECK_PUTIN_FOLDER_NAME());   
			$DB_FCHK->execute(array('owner'=>$this->USER->UserNO,'name'=>$folder));
			if( $fno = $DB_FCHK->fetchColumn()){
			  $folder_putin[] = $fno;
			}else{
			  $DB_FOLDER = $this->DBLink->prepare(SQL_AdArchive::REGIST_USER_PUTIN_FOLDER());   
			  $DB_FOLDER->bindValue(':owner',$this->USER->UserNO);
			  $DB_FOLDER->bindValue(':ftype','folder');
			  $DB_FOLDER->bindValue(':name',$folder);
		      $DB_FOLDER->execute(); 
			  
			  $fno = $this->DBLink->lastInsertId();
			  
		      if(!is_dir(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$fno)){
			    mkdir(_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$fno);	  
		      } 		  
		       
			  $DB_FOLDER = $this->DBLink->prepare(SQL_AdArchive::UPDATE_USER_PUTIN_FOLDER());
			  $DB_FOLDER->bindValue(':path',_SYSTEM_USER_PATH.$this->USER->UserID.'/'.$fno);
			  $DB_FOLDER->bindValue(':ufno',$fno);
			  $DB_FOLDER->execute();
			  
			  $folder_putin[] = $fno; 
			  $folder_return[$fno] = $folder;
			}
		  }
		}
		
		// 註冊folder_map
		$folder_putin = array_unique($folder_putin);
		if(count($folder_putin)){
		  $DB_PUTIN = $this->DBLink->prepare(SQL_AdArchive::INSERT_DATA_TO_FOLDER());
		  $DB_COUNT = $this->DBLink->prepare(SQL_AdArchive::RECOUNT_FOLDER_DATA());
		  foreach($folder_putin as $fid){
			foreach($meta_list as $id=>$meta){
			  $DB_PUTIN->bindValue(':fid',$fid);
			  $DB_PUTIN->bindValue(':sid',$meta['system_id']);	
			  $DB_PUTIN->execute();
			}
		    $DB_COUNT->execute(array('fid'=>$fid));
		  }
		}
		
		
		$result['data'] = $folder_return;
		$result['action'] = true;
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	//-- Rotate Photo     20170208 updated
	// [input] : DocumentId   : ;
	// [input] : RotateMode : left / right;
	public function ADArchive_Rotate_Target_Image( $DocumentId='',$RotateMode){
	  $result_key = parent::Initial_Result();
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{  
		// 檢查資料序號
	    if(!preg_match('/^\w+\_\d{6}\_\d{8}$/',$DocumentId)){
		  throw new Exception('_SYSTEM_ERROR_PARAMETER_FAILS');
		}
		
		// 查詢資料
		$DB_Query = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA());
		$DB_Query->bindValue(':id',$DocumentId); 
		
	    if(!$DB_Query->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');	
		}
		
		if(!$meta = $DB_Query->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		// 確認權限
		if(!in_array('R01',$this->USER->PermissionNow['group_roles']) && $meta['upload_user']!=$this->USER->UserID){
		  throw new Exception('_SYSTEM_ERROR_PERMISSION_DENIAL');		
	    }
		
		list($iname,$itype) = explode('.',$meta['image_name']);
		
		// 旋轉
		$rotate_degree = $RotateMode == 'left' ? -90 : 90; 
		
		$orl_img = _SYSTEM_FILE_PATH.$meta['_store'].'original/'.$meta['identifier'].'.'.strtolower($itype);
		$sys_img = _SYSTEM_FILE_PATH.$meta['_store'].'system/'.$meta['identifier'].'.jpg';
		$tmb_img = _SYSTEM_FILE_PATH.$meta['_store'].'thumb/'.$meta['identifier'].'.jpg';
		
		$lib_imagemagic = _SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/';
		
		if(!file_exists($orl_img)){
		  throw new Exception('_ARCHIVE_ORLFILE_UNUSEABLE');	
		}
		
		// 旋轉圖片
		exec($lib_imagemagic.'convert.exe '.$orl_img.' -rotate '.$rotate_degree.' '.$orl_img ,$output, $return_var);
		
		/* //取得資訊
		identify rose.jpg
        rose.jpg JPEG 70x46 70x46+0+0 8-bit sRGB 2.36KB 0.000u 0:00.000
		*/
		exec($lib_imagemagic.'identify.exe '.$orl_img ,$output, $return_var);
		$orl_image_info = explode(' ',$output[0]);
		list($iw,$ih) = explode('x',$orl_image_info[2]);
		
		//  建立系統圖
		if($iw > 1024 || $ih > 800){
		  $config = ($iw >= $ih) ?  ' -resize 1024  -strip -quality 86 ' : ' -resize x800 -strip -quality 86 ';
		  exec($lib_imagemagic.'convert.exe '.$config.$orl_img.' '.$sys_img,$result); 
		}else{
		  $config = ' -strip -quality 86 ';
		  exec($lib_imagemagic.'convert.exe '.$config.$orl_img.' '.$sys_img,$result);
		}
		//  建立縮圖 thumb  width 200 |  height 200 
		$config = ($iw >= $ih) ?  ' -thumbnail 250 ' : ' -thumbnail x300 ';
		exec($lib_imagemagic.'convert.exe '.$config.$sys_img.' '.$tmb_img ,$result);
		
		list($siw,$sih) = getimagesize($sys_img);
		list($tiw,$tih) = getimagesize($tmb_img);
		$photo_modify['image_orl'] = $orl_image_info[2];
		$photo_modify['image_sys'] = $siw.'x'.$sih;
		$photo_modify['image_tum'] = $tiw.'x'.$tih;
		
		// 執行更新
		$DB_SAVE	= $this->DBLink->prepare(SQL_AdArchive::ADMIN_ARCHIVE_UPDATE_META_DATA(array_keys($photo_modify)));
		$DB_SAVE->bindValue(':identifier' , $DocumentId);
		foreach($photo_modify as $mf => $mv){
		  $DB_SAVE->bindValue(':'.$mf , $mv);
		}
		if( !$DB_SAVE->execute()){
		  throw new Exception('_SYSTEM_ERROR_DB_ACCESS_FAIL');
		}
		// final 
		$result['data'] = $photo_modify['image_orl'];
		$result['action'] = true;
    	
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;  
	}
	
	
	//-- Download Photo Package  
	// [input] : PackageHash : md5 string;
	public function ADArchive_Get_Document_File( $DocumentId=''){
	  
	  $result_key = parent::Initial_Result('file');
	  $result  = &$this->ModelResult[$result_key];
	  
	  try{
		
		// 取得資料夾
		$document = array();
		$DB_META = $this->DBLink->prepare(SQL_AdArchive::GET_OBJECT_METADATA()); 
		$DB_META->bindValue(':id', $DocumentId);
		if(!$DB_META->execute() || !$document = $DB_META->fetch(PDO::FETCH_ASSOC)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');	
		}
		
		$file_name = $document['identifier'].'.'.$document['file_type'];
		$file_path = _SYSTEM_FILE_PATH.'document/'.substr($document['identifier'],0,1).'/'.$file_name;
		
		// 確認檔案
		if(!is_file($file_path)){
		  throw new Exception('_SYSTEM_ERROR_DB_RESULT_NULL');
		}
		
		$result['data']['name']   = $file_name;
		$result['data']['size']   = filesize($file_path);
		$result['data']['location']   = $file_path;
		$result['action'] = true;
		
	  } catch (Exception $e) {
        $result['message'][] = $e->getMessage();
      }
	  return $result;
	}
	
	
	
	
	
	
  }	
?>	