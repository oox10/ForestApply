<?php
    
	/*
	數位典藏資料
	2017
	*/
	ini_set("memory_limit", "2048M");
    
    require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
    require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
    require_once(dirname(dirname(__FILE__)).'/mvc/lib/PHPExcel-1.8/Classes/PHPExcel.php');
	
	//-- load meta assist from db 
    $db = new DBModule;
    $db->db_connect('PDO'); 
	
	// logs
    file_put_contents('import.txt',"Paser Old Applied:".date('Y-m-d H:i:s')."\n");
	
	
	//新增資料失敗
	$db_update =  $db->DBLink->prepare("INSERT INTO area_booking VALUES(NULL,".
	":am_id,:apply_code,:apply_date,:applicant_name,:applicant_mail,:applicant_id,:applicant_info,".
	":apply_reason,:date_enter,:date_exit,:apply_form,:member,:member_count,:check_note,".
	":_ballot,:_ballot_date,:_ballot_result,:_review,:_stage,:_progres,:_status,:_final,:_time_create,:_time_update,:_checker,'',1);");
	
	$import_stage = 5;
	
	
	//掃描所有區域
	$areamap = [];
	$DB_GET	= $db->DBLink->prepare( "SELECT * FROM area_main WHERE _keep=1 ORDER BY ano ASC;" );
	if( !$DB_GET->execute() ){
	  throw new Exception('_DB_ERROR_GET_AREA_SQL_FAIL');
    }
	while( $tmp = $DB_GET->fetch(PDO::FETCH_ASSOC)){
	  $areamap[$tmp['area_name']] = $tmp;
	}
	
	$file_path = dirname(__FILE__).'/applied_record/';
	
	try{ 
      
	  $counter = 0;
	  $apply_years = array_slice(scandir($file_path),2);
	  if(!count($apply_years)){
		throw new Exception('No Source Files FROM :'.$file_path."\n");    
	  }
	  
	  
	  $status_queue = array();
	  
	  foreach($apply_years as $ayear){
		  
		if(!is_dir($file_path.$ayear.'/')){
		  echo "\n".$ayear.' not exist!!!.';  
		  continue;
		}  
		  
		$year_applys_folder = $file_path.$ayear.'/';  
		  
		  
		$apply_records = array_slice(scandir($year_applys_folder),2);
	    
		foreach($apply_records as $apply_file){
		  $counter++;	
		  echo "\n".str_pad($counter,7,'0',STR_PAD_LEFT).' : ['.$apply_file.']'; 	
		  $old_apply = json_decode(file_get_contents($year_applys_folder.$apply_file),true);		  
          
		  if(!is_array($old_apply)){
			file_put_contents('import.txt',$apply_file." format fail!\n",FILE_APPEND);
			exit(1);
			continue;
		  }
		  
		  
		  if(!isset($old_apply['application']) || !is_array($old_apply['application']) || !count($old_apply['application'])){
			file_put_contents('import.txt',$apply_file." applicantion fail!\n",FILE_APPEND);
			exit(1);
			continue;  
		  }
		  
		  
		  if(!isset($old_apply['members']) || !is_array($old_apply['members']) || !count($old_apply['members'])){
			file_put_contents('import.txt',$apply_file." members fail!\n",FILE_APPEND);
			//exit(1);
			continue;  
		  }
		  
		  
		  $old_apply['application'];
		  $old_apply['members'];
		  $old_apply['reviewed'];     //[_review  check_note]
		  $old_apply['attachment'];
		  
		  
		  
		  
		  $am_id = isset($areamap[$old_apply['application']['進入區域']]) ? $areamap[$old_apply['application']['進入區域']]['ano'] : '0';
		  $apply_code = isset($old_apply['application']['申請編號']) ? $old_apply['application']['申請編號'] : false;
		  $apply_date = isset($old_apply['application']['申請日期']) ? date('Y-m-d',strtotime($old_apply['application']['申請日期'])) : false;
		  
		  $applicant_name = isset($old_apply['application']['申請人']) ?   $old_apply['application']['申請人'] : false;
		  $applicant_id   = false;
		  $applicant_mail = isset($old_apply['application']['EMail']) ?   $old_apply['application']['EMail'] : false;
		  
		  $enter_member = [];
		  
		  foreach($old_apply['members'] as $member){
			
			$role = '';
			$mbr  = [];
			if($member[1] == $applicant_name){
			  $applicant_id = $member[2];
			  $role = '領隊';
			}else{
			  $role = '成員';	
			}
			
			$mbr["member_role"]	= $role;
			$mbr["member_name"]	= $member[1];
			$mbr["member_id"]  	= $member[3]=='&nbsp;' ? $member[2] : $member[3];
			$mbr["member_birth"]= $member[4];
			$mbr["member_sex"]  = "";
			$mbr["member_tel"]  = $member[6];
			$mbr["member_cell"] = $member[6];
			$mbr["member_addr"] = $member[5];
			$mbr["member_org"]  = "";
			$mbr["member_contacter"] = $member[7];
			$mbr["member_contactto"] = $member[8];
			
			$enter_member[] = $mbr;
			
		  }
		  
		  $applicant_info = [
		    'applicant_name'=> $applicant_name, 
		    'applicant_userid'=> $applicant_id,
            'applicant_mail'=> $applicant_mail, 			
		  ];
		  
		  $apply_reason = isset($old_apply['application']['進入目的']) ?   $old_apply['application']['進入目的'] : false;
		  
		  switch($apply_reason){
			case '相關團體為環境教育之需要': $apply_reason = '民眾為環境教育之需要'; break;
            default:break;;			
		  }
		  
		  
		  
		  
		  $date_enter = false;
		  $date_exit = false;
		  
		  if(isset($old_apply['application']['進入期間'])){
			$adate = explode(' &nbsp;~&nbsp;',$old_apply['application']['進入期間']);
            $date_enter = strtotime($adate[0]) ? date('Y-m-d',strtotime($adate[0])) : false;
			$date_exit = strtotime($adate[1]) ? date('Y-m-d',strtotime($adate[1])) : false;
		  }
		  
		  $apply_form = [
		    'area'=>[
			  "code"=>$areamap[$old_apply['application']['進入區域']]['area_code'],
			  "inter"=>[isset($old_apply['application']['進入範圍']) ? $old_apply['application']['進入範圍'] : ''],
			  "gate"=>[
				"entr"=> isset($old_apply['application']['進入入口及出口地點']) ? $old_apply['application']['進入入口及出口地點'] : '',
				"entr_time"=>(isset($old_apply['application']['到達入口時間']) ? $old_apply['application']['到達入口時間'] : ''),
				"exit"=>"",
				"exit_time"=>"00:00:00"
			  ]
			],
			'reason'=>[[
			  'item'=>$apply_reason,
			  'limit'=>1, 
			]],
			'attach'=>[],
			'dates'=>[[$date_enter,$date_exit]],
			'fields'=>[
			  'application_field_1'=>[
			    "field"=>"一、每日行程路線：(請簡易填寫行進路線，包含預計日期及時間、抵達地點、及從事之行為種類)：",
			    "value"=>(isset($old_apply['application']['每日行程路線']) ? $old_apply['application']['每日行程路線'] : ''),  
			  ],
			  'application_field_2'=>[
			    "field"=>"二、環境維護措施(垃圾、廢棄物處理方式)及環境教育內容簡介：",
			    "value"=>(isset($old_apply['application']['環境維護措施']) ? $old_apply['application']['環境維護措施'] : '')
			  ],
			  'application_field_3'=>[
			    "field"=>"三、緊急災難處理(應變相關裝備概述、辦理保險及撤退路線等說明)：",
			    "value"=>(isset($old_apply['application']['緊急災難處理']) ? $old_apply['application']['緊急災難處理'] : '') 
			  ],
			]
		  ];
		  
		  
		  if(isset($old_apply['attachment']) && count($old_apply['attachment'])){
			
			// 歸檔
			$upload_folder = _SYSTEM_CLIENT_PATH.$apply_code.'/';
			mkdir($upload_folder,0777,true);
			
			foreach($old_apply['attachment'] as $attach){
				
				$upload_path   = $file_path.$attach;
				// Get filename.
			    $temp = explode(".", $upload_path);

			    // Get extension.
			    $extension = strtolower(end($temp));
			    $upload_file   = strtoupper(hash('crc32',time())).'.'.$extension;
			    $upload_save   = $upload_folder.$upload_file ;
			    copy($upload_path,$upload_save);
				$apply_form['attach'][] = [
				  "code"=>$upload_file,
				  "time"=>date('Y-m-d H:i:s'),
				  "file"=>$attach  
				];	
			
			}
		  }
		  
		  
		  $enter_member;
		  $member_count = count($enter_member);
		  $check_note   = isset($old_apply['reviewed']['check_note']) ? $old_apply['reviewed']['check_note'] : ''; // 審查註記
		  
		  
		  $bollet_date  = '0000-00-00';
		  $apply_bollet = 0;
		  $_ballot_result = 0;
		  
		  
		  $_status  = isset($old_apply['application']['狀態']) ? $old_apply['application']['狀態'] : '';
		  $_final   = '';;
		  $_stage   = 0;
		  
		  $_progres = ['client'=>[ 0=>[], 1=>[["time"=>date('Y-m-d H:i:s'),"status"=>"資料匯出","note"=>"","logs"=>""]], 2=>[], 3=>[], 4=>[], 5=>[]],'review'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]],'admin'=>[ 0=>[], 1=>[], 2=>[], 3=>[], 4=>[], 5=>[]]];
		  $_progres['admin'][5][] = ["time"=>date('Y-m-d H:i:s'),"status"=>"系統匯入","note"=>"","logs"=>""];
		  
		  
		  $_time_create = $apply_date.' 00:00:00';
		  $_time_update = $apply_date.' 00:00:00';
		  $_checker		= '';
		  
		  if(!isset( $status_queue[$_status])) $status_queue[$_status] = 0;
		  $status_queue[$_status]++;
		  
		  
		  switch($_status){
			
			case '收件待審':
            
			    $_stage   = 1;
			    $areainfo = $areamap[$old_apply['application']['進入區域']];
			    $apply_bollet  = 0;  //是否需抽籤
				$applicant_mail='tpcca.rcdh.ntu@gmail.com';
				
				// 檢驗申請理由是否需要抽籤
				
				if('民眾為環境教育之需要' == $apply_reason){
				  $apply_bollet = 1;  
				}
				// 確認是否抽籤
				$bollet_date = $apply_bollet ? date('Y-m-d',strtotime('-'.($areainfo['accept_min_day']-1).' day',strtotime($date_enter))) : '0000-00-00';
				if( $apply_bollet ){  
				  $_progres['client'][1]   = [array('time'=>date('Y-m-d H:i:s'),'status'=>'收件待審','note'=>'申請項目需抽籤','logs'=>'')];
				  $_progres['review'][2][] = array('time'=>$bollet_date,'status'=>'系統抽籤','note'=>'','logs'=>'');	
				}else{
				   
				}
			  break;
			  
			case '申請取消':  case '申請註銷': case '申請駁退': case '抽籤未中':
			  $_progres['client'][5][] = ["time"=>date('Y-m-d H:i:s'),"status"=>$_status,"note"=>"","logs"=>""];
			  $_stage=5;
			  $_final=$_status;
			  break;
			
            case '申請核准':
			  $_progres['client'][5][] = ["time"=>date('Y-m-d H:i:s'),"status"=>'核准進入',"note"=>"","logs"=>""];
			  $_stage  =5;
			  $_status = '核准進入';
			  $_final  ='核准進入';
			  break;
			
			
			  
			case '正取送審':
              $_progres['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'正取送審','note'=>date('Y-m-d').'抽籤正取','logs'=>'');
			  $_stage=3;
			  $_ballot_result=1;
			  break;  			
			  
			  
			default: // 其他狀況
			  
			  if(preg_match('/^備取(成功|失敗)(.*?)$/',$_status,$match)){
				
				$_progres['review'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>$match[0],'note'=>$match[2],'logs'=>'');	
				
				if($match[1]=='成功'){
				  $progress['client'][5][] = ['time'=>date('Y-m-d H:i:s'),'status'=>'核准進入','note'=>'','logs'=>''];	
				}else{
				  $_progres['client'][5][] = ["time"=>date('Y-m-d H:i:s'),"status"=>'申請註銷',"note"=>"","logs"=>""];	
				}
				$_stage = 5;
				$_final=$_status;
				
			  }else if(preg_match('/^備取送審（順序：(\d+)）$/',$_status,$match)){
				$_status = '備取送審';
				$_progres['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取送審','note'=>date('Y-m-d').'抽籤備取 '.$match[1],'logs'=>'');
			    $_stage=3; 
				$_ballot_result=2;
				
			  }else if(preg_match('/^備取等待（順序：(\d+)）$/',$_status,$match) ){
				$_status = '備取等待';
				$_progres['client'][2][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取送審','note'=>date('Y-m-d').'抽籤備取 '.$match[1],'logs'=>'');
			    $_progres['client'][4][] = array('time'=>date('Y-m-d H:i:s'),'status'=>'備取等待','note'=>'','logs'=>'');	
				$_stage=4;   
			    $_ballot_result=2;
			  
			  }else{
				/*  處理其他相關狀態*/
				/*
				[正取補件送審] => 2
				
				*/  
			  }
			  break;
		  
		  }
		  
		  
		  if(!$_stage) continue;
		  
		  $db_update->bindValue(':am_id',$am_id);
		  $db_update->bindValue(':apply_code',$apply_code);
		  $db_update->bindValue(':apply_date',$apply_date);
		  $db_update->bindValue(':applicant_name',$applicant_name);
		  $db_update->bindValue(':applicant_mail',$applicant_mail);
		  $db_update->bindValue(':applicant_id',$applicant_id);
		  $db_update->bindValue(':applicant_info',json_encode($applicant_info));
		  
		  $db_update->bindValue(':apply_reason',$apply_reason);
		  $db_update->bindValue(':date_enter',$date_enter);
		  $db_update->bindValue(':date_exit',$date_exit);
		  $db_update->bindValue(':apply_form',json_encode($apply_form));
		  $db_update->bindValue(':member',json_encode($enter_member));
		  $db_update->bindValue(':member_count',$member_count);
		  $db_update->bindValue(':check_note',$check_note);
		  
		  $db_update->bindValue(':_ballot',$apply_bollet);
		  $db_update->bindValue(':_ballot_date',$bollet_date);
		  $db_update->bindValue(':_ballot_result',$_ballot_result);
		  
		  $db_update->bindValue(':_review',isset($old_apply['reviewed']['_review']) ? intval($old_apply['reviewed']['_review']):0 );
		  
		  $db_update->bindValue(':_stage',$_stage);
		  $db_update->bindValue(':_progres',json_encode($_progres));
		  $db_update->bindValue(':_status',$_status);
		  $db_update->bindValue(':_final',$_final);
		  
		  $db_update->bindValue(':_time_create',$_time_create);
		  $db_update->bindValue(':_time_update',$_time_update);
		  $db_update->bindValue(':_checker',$_checker);
		  
		  if(!$db_update->execute()){
		    file_put_contents('import.txt',$apply_file."\n",FILE_APPEND);
			file_put_contents('import.txt',print_r($old_apply,true),FILE_APPEND);
			file_put_contents('import.txt',"\n\n",FILE_APPEND);
			echo "Ｘ";
		  }else{
			echo "０";  
		  }
		}
	  
	  }
	  
	 
	  
	} catch (Exception $e) {
      echo $e->getMessage();
    }	
	
?>