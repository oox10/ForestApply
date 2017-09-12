<?php

  /*  
  *   IISPhoArchive Jobs Process
  *   -  Import User Upload File To Archive    2016-01-22
  *   
  */
  ini_set('memory_limit', '1000M');	
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');
  
  class ImportPhoto{
	
	private $DB;
	private $Task		  = '';
	private $UploadRecord = array();
	
	private $UploData	   = array();
	private $UserData      = array();
	private $FileData      = array();
	private $MetaData      = array();
	
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
	
	
	//-- 
	protected function prepareImport($UplRecord){
	  
		/*	  	
		urno folder user hash creater classlv 
		name size mime type last
		_regist _upload _archived
		*/	  
		
		
	  try{ 
	    
		$this->UserData      = array();  // 使用者資訊
	    $this->FileData      = array();  // 檔案資訊 source & objectid
	    $this->MetaData      = array();  // 輸入資料庫之meta
		
		// get record data 
		$this->UploData = $UplRecord;
		$this->DB->DBLink->query("UPDATE task_upload SET _process='".date('Y-m-d H:i:s')."' WHERE urno = ".$this->UploData['urno'].";");
		
		// get uploader data
	    $DB_Access = $this->DB->DBLink->query("SELECT uid,user_name,user_idno,user_mail FROM user_login LEFT JOIN user_info ON uid=uno WHERE user_id='".$UplRecord['user']."';");
        if( $DB_Access->execute() && $user_data = $DB_Access->fetch(PDO::FETCH_ASSOC) ){
	      $this->UserData = $user_data;
        }else{
		  throw new Exception('查無使用者');  
		}
	  
	    // check file 
	    $file_location = _SYSTEM_USER_PATH.$UplRecord['user'].'/'.$UplRecord['folder'].'/'.str_pad($UplRecord['urno'],8,'0',STR_PAD_LEFT).$UplRecord['hash'];
	    if(is_file($file_location) && filesize($file_location)==$UplRecord['size'] ){
		  $this->FileData['source'] = $file_location;
	    }else{
		  throw new Exception('上傳暫存錯誤');  	
		}
	  
	    $file_header = isset($this->UserData['user_idno']) && $this->UserData['user_idno'] ? $this->UserData['user_idno'] : 'IIS';
	    $this->FileData['objectid'] = 'U'.str_pad($this->UserData['uid'],3,'0',STR_PAD_LEFT);   //U012-.jpg
	    
	    return true;
	  
	  } catch (Exception $e) {
        return $e->getMessage();
      }
	}
	
	
	//-- 執行匯入
	private function activeImport(){
	  
	  $meta = array();
	  
	  
	  switch($this->UploData['type']){
	    
		case 'pdf':
		  
		  try{
		    
			$extract = self::extractPdfMeta($this->FileData['source'],$this->UploData['name']); 
		    
			$meta['identifier']	    = '';
			$meta['classlevel']	    = '';
			$meta['file_name']		= $this->UploData['name'];
			$meta['file_type']		= $this->UploData['type'];
			$meta['doc_type']		= '新增檔案';
			$meta['title_main']		= isset($extract['title_main']) ? $extract['title_main']:'';
			$meta['title_second']	= '';
			$meta['plan_no']		= '';
			$meta['organ_main']		= '';
			$meta['organ_work']		= '';
			$meta['execute_year']	= isset($extract['execute_year']) ? $extract['execute_year']:'';
			$meta['execute_person']		= '';
			$meta['contact_person']		= '';
			$meta['research_domain']	= isset($extract['research_domain']) ? $extract['research_domain']:'';
			$meta['research_member']	= isset($extract['research_member']) ? $extract['research_member']:'';
			$meta['research_property']	= isset($extract['research_property']) ? $extract['research_property']:'';
			$meta['research_method']	= isset($extract['research_method']) ? $extract['research_method']:'';
			$meta['research_time']		= isset($extract['research_time']) ? $extract['research_time']:'';
			$meta['research_area']		= isset($extract['research_area']) ? $extract['research_area']:'';
			$meta['funding']		= '';
			$meta['keywords']		= '';
			$meta['abstract']		= '';
			$meta['fulltexts']		= isset($extract['fulltexts']) ? $extract['fulltexts']:'';
			$meta['provider']       = $this->UserData['user_name'];
			$meta['upload_user']	= $this->UploData['user'];
			$meta['upload_time']	= $this->UploData['_upload'];
			
			$meta['temp']			= '';
			$meta['PQ_YearNum']		= isset($extract['pqyearnum']) ? $extract['pqyearnum']:'';;
			$meta['AP_MapLocation']	= '[]';
			$meta['tags']		 	= '';
			$meta['update_user']	= '';
			$meta['doc_status']		= '_upload';
			$meta['_show']			= 1;
			$meta['_keep']			= 1;
			
			
			$db_insert = $this->DB->DBLink->prepare("INSERT INTO metadata VALUES(NULL,:".join(',:',array_keys($meta)).");");
			foreach($meta as $field => &$value){
			  $db_insert->bindValue(':'.$field , $value);
			}
			
			if(!$db_insert->execute()){
			  throw new Exception('導入meta資料失敗');
			}
			
			// UPDATE meta
			$meta_no = $this->DB->DBLink->lastInsertId('metadata');
			$meta['identifier'] = $this->FileData['objectid'].'-'.str_pad($meta_no,8,'0',STR_PAD_LEFT);
			$this->DB->DBLink->query("UPDATE metadata SET identifier='".$meta['identifier']."' WHERE system_id = ".$meta_no.";");
			
			$file_name = $meta['identifier'].'.'.$this->UploData['type'];
			
			// UPDATE Class
			if($this->UploData['classlv']){
			  $class = array_unique(array_filter(explode(';',$this->UploData['classlv'])));
			  foreach($class as $level){
				$lv = explode('/',$level);
				foreach($lv as $i=>$l){
				  $this->DB->DBLink->query("UPDATE search_level SET count=(count+1) WHERE info='".join('/',array_slice($lv,0,($i+1)))."' AND site='".($i+1)."';"); 
				}
			  } 
			}
			
			$this->MetaData = $meta;
			$this->MetaData['system_id'] = $meta_no;
			
			//-- Process file
			$file_save =  _SYSTEM_FILE_PATH.'document/U/'.$file_name;
			$imgprocess = copy($this->FileData['source'],$file_save);
			if(!$imgprocess){
			  throw new Exception('檔案處理失敗');	
			}
			
			return true;
		  } catch (Exception $e) {
			return $e->getMessage();
		  }
		  
		  break;  
		
		default:
		  
		  try{
		    $extract = array();
			$meta['identifier']	    = '';
			$meta['classlevel']	    = '';
			$meta['file_name']		= $this->UploData['name'];
			$meta['file_type']		= $this->UploData['type'];
			$meta['doc_type']		= '新增檔案';
			$meta['title_main']		= isset($extract['title_main']) ? $extract['title_main']:'';
			$meta['title_second']	= '';
			$meta['plan_no']		= '';
			$meta['organ_main']		= '';
			$meta['organ_work']		= '';
			$meta['execute_year']	= isset($extract['execute_year']) ? $extract['execute_year']:'';
			$meta['execute_person']		= '';
			$meta['contact_person']		= '';
			$meta['research_domain']	= isset($extract['research_domain']) ? $extract['research_domain']:'';
			$meta['research_member']	= isset($extract['research_member']) ? $extract['research_member']:'';
			$meta['research_property']	= isset($extract['research_property']) ? $extract['research_property']:'';
			$meta['research_method']	= isset($extract['research_method']) ? $extract['research_method']:'';
			$meta['research_time']		= isset($extract['research_time']) ? $extract['research_time']:'';
			$meta['research_area']		= isset($extract['research_area']) ? $extract['research_area']:'';
			$meta['funding']		= '';
			$meta['keywords']		= '';
			$meta['abstract']		= '';
			$meta['fulltexts']		= isset($extract['fulltexts']) ? $extract['fulltexts']:'';
			$meta['provider']       = $this->UserData['user_name'];
			$meta['upload_user']	= $this->UploData['user'];
			$meta['upload_time']	= $this->UploData['_upload'];
			
			$meta['temp']			= '';
			$meta['PQ_YearNum']		= isset($extract['pqyearnum']) ? $extract['pqyearnum']:'none';;
			$meta['AP_MapLocation']	= '[]';
			$meta['tags']		 	= '';
			$meta['update_user']	= '';
			$meta['doc_status']		= '_upload';
			$meta['_show']			= 1;
			$meta['_keep']			= 1;
			
			
			$db_insert = $this->DB->DBLink->prepare("INSERT INTO metadata VALUES(NULL,:".join(',:',array_keys($meta)).");");
			foreach($meta as $field => &$value){
			  $db_insert->bindValue(':'.$field , $value);
			}
			
			if(!$db_insert->execute()){
			  throw new Exception('導入meta資料失敗');
			}
			
			// UPDATE meta
			$meta_no = $this->DB->DBLink->lastInsertId('metadata');
			$meta['identifier'] = $this->FileData['objectid'].'-'.str_pad($meta_no,8,'0',STR_PAD_LEFT);
			$this->DB->DBLink->query("UPDATE metadata SET identifier='".$meta['identifier']."' WHERE system_id = ".$meta_no.";");
			
			$file_name = $meta['identifier'].'.'.$this->UploData['type'];
			
			// UPDATE Class
			if($this->UploData['classlv']){
			  $class = array_unique(array_filter(explode(';',$this->UploData['classlv'])));
			  foreach($class as $level){
				$lv = explode('/',$level);
				foreach($lv as $i=>$l){
				  $this->DB->DBLink->query("UPDATE search_level SET count=(count+1) WHERE info='".join('/',array_slice($lv,0,($i+1)))."' AND site='".($i+1)."';"); 
				}
			  } 
			}
			
			$this->MetaData = $meta;
			$this->MetaData['system_id'] = $meta_no;
			
			//-- Process file
			$file_save =  _SYSTEM_FILE_PATH.'document/U/'.$file_name;
			$imgprocess = copy($this->FileData['source'],$file_save);
			if(!$imgprocess){
			  throw new Exception('檔案處理失敗');	
			}
			
			return true;
		  } catch (Exception $e) {
			return $e->getMessage();
		  }
		  
		  break;
		
        /*		
		case 'jpg': case 'png':  case 'tiff': // 圖片檔案   
		  
		  try{
			
			$iptc = array();
			$exif = array();
			$extract = self::extractImageMeta($this->FileData['source'],$this->UploData['name']); 
			
			$photo_info = array_filter(array($extract['phototime'],$extract['model'],join(';',array_unique($extract['creater'])),join(';',array_unique($extract['copyright']))));
			
			$meta['identifier']	    = '';
			$meta['originalid']	    = 0;
			$meta['creater']        = count($extract['creater']) ? join(';',array_unique($extract['creater'])) : $this->UploData['creater'];
			$meta['provider']       = $this->UserData['user_name'];
			$meta['photo_name']	    = '';
			$meta['photo_date']	    = $extract['phototime'] ? $extract['phototime'] : '0000-00-00 00:00:00';
			$meta['photo_locat']	= count($extract['photoloc']) ? join(';',array_unique($extract['photoloc'])) : '';
			$meta['photo_title']	= '';
			$meta['photo_descrip']	= $extract['photodesc'];
			$meta['photo_keywords']	= count($extract['keywords']) ? join(';',array_unique($extract['keywords'])) : '';
			$meta['classcode']		= '圖庫上傳';
			$meta['classlevel']		= $this->UploData['classlv'];
			$meta['tags']			= '';
			$meta['score']			= $extract['score'];
			$meta['image_name']		= $this->UploData['name'];
			$meta['image_type']		= $this->UploData['mime'];
			$meta['image_size']		= $this->UploData['size'];
			$meta['upload_date']	= $this->UploData['_upload'];
			$meta['upload_user']	= $this->UserData['uid'];
			$meta['update_date']	= date('Y-m-d H:i:s');
			$meta['update_user']	= $this->UserData['uid'];
			$meta['image_orl']		= '' ;
			$meta['image_sys']		= '' ;
			$meta['image_tum']		= '' ;
			$meta['info_goe']		= '[]';
			$meta['info_iptc']		= '';
			$meta['info_exif']		= serialize($photo_info);
			$meta['PQ_YearNum']		= strtotime($meta['photo_date']) ? date('Y',strtotime($meta['photo_date'])).' - 民國'.(intval(date('Y',strtotime($meta['photo_date'])))-1911).'年' : 'none';
			$meta['_store']			= date('Y').'PHO/';
			$meta['_show']			= 1;
			$meta['_keep']			= 1;
			
			$db_insert = $this->DB->DBLink->prepare("INSERT INTO metadata VALUES(NULL,:identifier,:originalid,:creater,:provider,:photo_name,:photo_date,:photo_locat,:photo_title,:photo_descrip,:photo_keywords,:classcode,:classlevel,:tags,:score,:image_name,:image_type,:image_size,:upload_date,:upload_user,:update_date,:update_user,:image_orl,:image_sys,:image_tum,:info_goe,:info_iptc,:info_exif,:PQ_YearNum,:_store,:_show,:_keep);");
			foreach($meta as $field => &$value){
			  $db_insert->bindValue(':'.$field , $value);
			}
			
			if(!$db_insert->execute()){
			  throw new Exception('導入meta資料失敗');
			}
			
			// UPDATE meta
			$meta_no = $this->DB->DBLink->lastInsertId('metadata');
			$meta['identifier'] = $this->FileData['objectid'].'_'.str_pad($meta_no,8,'0',STR_PAD_LEFT);
			$this->DB->DBLink->query("UPDATE metadata SET identifier='".$meta['identifier']."',photo_name='".$meta['identifier']."' WHERE system_id = ".$meta_no.";");
			 
			// UPDATE Class
			if($this->UploData['classlv']){
			  $class = array_unique(array_filter(explode(';',$this->UploData['classlv'])));
			  foreach($class as $level){
				$lv = explode('/',$level);
				foreach($lv as $i=>$l){
				  $this->DB->DBLink->query("UPDATE search_level SET count=(count+1) WHERE info='".join('/',array_slice($lv,0,($i+1)))."' AND site='".($i+1)."';"); 
				}
			  } 
			}
			
			// 儲存IPTC&EXIF萃取資料
			file_put_contents(_SYSTEM_FILE_PATH.$meta['_store']."metadata/".$meta['identifier'].'.meta',print_r($extract['meta'], true),FILE_APPEND | LOCK_EX);
			
			$this->MetaData = $meta;
			$this->MetaData['system_id'] = $meta_no;
			  
			//-- Process Image
			switch($this->UploData['mime']){
				case 'image/jpeg': $imgprocess = self::process_image( 'jpg'  , $this->FileData['source'],$meta['_store'],$meta['identifier']); break;
				case 'image/png' : $imgprocess = self::process_image( 'png'  , $this->FileData['source'],$meta['_store'],$meta['identifier']); break;
				case 'image/raf': $imgprocess = self::process_image('raf',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;
				case 'image/cr2':
				case 'image/x-canon-cr2':$imgprocess = self::process_image('cr2',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;
				case 'image/tiff': 
				  if($this->UploData['type']=='dng'){
					$imgprocess = self::process_image( 'dng' , $this->FileData['source'],$meta['_store'],$meta['identifier']);   
				  }else{
					$imgprocess = self::process_image( 'tif' , $this->FileData['source'],$meta['_store'],$meta['identifier']);   
				  }  
				  break;
				case 'image/dng': $imgprocess = self::process_image('dng',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;  
				case 'application/octet-stream':  // 
				  switch($this->UploData['type']){
					case 'cr2':$imgprocess = self::process_image('cr2',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;
					case 'dng':$imgprocess = self::process_image('dng',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;
					case 'raf':$imgprocess = self::process_image('raf',$this->FileData['source'],$meta['_store'],$meta['identifier']); break;
					default:break;
				  }
				default:break;
			}
			
			if(!isset($imgprocess) || !is_array($imgprocess)){
			  throw new Exception('影像處理失敗');	
			}
			
			// UPDATE imgprocess
			$meta['identifier'] = $this->FileData['objectid'].'_'.str_pad($meta_no,8,'0',STR_PAD_LEFT);
			$this->DB->DBLink->query("UPDATE metadata SET image_orl='".$imgprocess['o']."',image_sys='".$imgprocess['s']."',image_tum='".$imgprocess['t']."' WHERE system_id = ".$meta_no.";");
			
			return true;
		  
		  } catch (Exception $e) {
			return $e->getMessage();
		  }  
	      break;
		  */
	  }
	
	
	}
	
	
	
	//-- 抽取 pdf meta 
	public function extractPdfMeta($FileSorce,$FileOrlName = ''){
	    
	  $user_temp_file = _SYSTEM_USER_PATH.$this->UploData['user'].'/doc.tmp';
		
	  system(_SYSTEM_ROOT_PATH.'mvc/lib/xpdfbin-win-3.04/bin64//pdftotext.exe -cfg "'._SYSTEM_ROOT_PATH.'mvc/lib/xpdfbin-win-3.04/xpdfrc" -layout -table -raw -nopgbrk -enc UTF-8 '.$FileSorce.' '.$user_temp_file); 
      $doc_fulltext = preg_replace('/^\s+/',' ',file_get_contents($user_temp_file));
        
	  // 整理全文
      $doc_fulltext = preg_replace('/[\s\r\n]+/',' ',$doc_fulltext);	  
	  //file_put_contents($user_temp_file,$doc_fulltext);
		
	  // get research list
	  $rh = array('domain'=>array(),'method'=>array(),'property'=>array(),'area'=>array());
	  $DB_GMTH  = $this->DB->DBLink->prepare("SELECT research_domain,research_property,research_method,research_area FROM metadata WHERE 1;");
	  if($DB_GMTH->execute()){
		while($mth = $DB_GMTH->fetch(PDO::FETCH_ASSOC)){
		  $rh['domain']   = array_merge($rh['domain'], explode(';',$mth['research_domain']));
		  $rh['method']   = array_merge($rh['method'], explode(';',$mth['research_method']));
		  $rh['property'] = array_merge($rh['property'], explode(';',$mth['research_property']));
		  $rh['area']     = array_merge($rh['area'], explode(';',$mth['research_area']));
		}
	  }	
	  	
      $extract['title_main']    = (preg_match('/.*?計畫/',mb_substr($doc_fulltext,0,300),$match)) ? $match[0] :'';
	  $extract['execute_year']  = (preg_match('/[\d一二三四五六七八九十零百０]+\s*(年|年)度?/u',$doc_fulltext,$match)) ? preg_replace('/\s+/','',$match[0]) :'';
	  $extract['fulltexts'] 	= $doc_fulltext;
	  $extract['pqyearnum']		= 'none';
      
	  if( $extract['execute_year'] ){
        $ystring =  preg_replace('/(年|年).*$/u','',$extract['execute_year']); 		
        
		if(preg_match('/^\d+$/',$ystring)){
		  if( intval($ystring) <= (date('Y')-1911) && intval($ystring) > 70 ){
			$extract['pqyearnum'] =  intval($ystring).'年度 '.(intval($ystring)+1911);
		  }else if( intval($ystring) > 1982 && intval($ystring)<= date('Y')){
			$extract['pqyearnum'] =  (intval($ystring)-1911).'年度 '.intval($ystring);
		  }
		}else if(preg_match('/^[一二三四五六七八九十零百０]+$/u',$ystring)){
		  $ycount = 0;
          for($i=0 ; $i<mb_strlen($ystring) ; $i++){
			switch(mb_substr($ystring,$i,1)){
			  case '一': $ycount+=1; break;
              case '二': $ycount+=2; break;
			  case '三': $ycount+=3; break;
			  case '四': $ycount+=4; break;
			  case '五': $ycount+=5; break;
			  case '六': $ycount+=6; break;
			  case '七': $ycount+=7; break;
			  case '八': $ycount+=8; break;
			  case '九': $ycount+=9; break;
			  case '十': $ycount*=10; break;
			  case '百': $ycount*=100; break;
			  case '０':  
			  case '零': 
			    if(mb_strlen($ystring)==3){
			      $ycount*=100;
				}else if(mb_strlen($ystring)==2){
				  $ycount*=10;
				}else{
				  $ycount=0;  	
				} 
				break;
			  default: break;	
			}
		  } 
		  
		  if( $ycount <= (date('Y')-1911) && $ycount > 70 ){
			$extract['pqyearnum'] =  intval($ycount).'年度 '.(intval($ycount)+1911);
		  }
		}
	  
	  }else if(preg_match('/(\d{4})\/\d{2}\/\d{2}/',mb_substr($doc_fulltext,0,300),$match)){
		
		$extract['pqyearnum'] = (intval($match[1])-1911).'年度 '.intval($match[1]); 
	  
	  }else if(preg_match('/^(\d{2,3})/',$FileOrlName,$match)){
		if( $match[1] <= (date('Y')-1911) && $match[1] > 70 ){
		  $extract['pqyearnum'] =  intval($match[1]).'年度 '.(intval($match[1])+1911);
		}  
	  }

	  
	  $research_field = array();
	  $cleantext = preg_replace('/\s+/','',$doc_fulltext);
	  foreach($rh as $rhfield=>$rhset){
	    $rhset = array_filter($rhset);   
	    if(count($rhset)){
		  $search_set = array_unique($rhset);
		  if(preg_match_all('/('.join('|',$search_set).')/u',$cleantext,$match,PREG_SET_ORDER)){
		    $term_get = array();
		    foreach($match as $term){
			  $term_get[] = $term[0];
		    }
		    $extract['research_'.$rhfield] = join(';',array_unique($term_get)); 
		  }else{
		    $extract['research_'.$rhfield] = '';
		  }
	    }
	  } 	
	  return $extract;	
	}
	
	
	
	
	
	//-- 抽取 image meta 
	public function extractImageMeta($FileSorce,$FileOrlName = ''){
	    
		// get meta
		exec(_SYSTEM_ROOT_PATH.'mvc/lib/exiv2-0.25-win/exiv2.exe -Pkt '.$FileSorce  , $meta_extract, $return_var);
		$extract = array(
			'phototime'=>'',
			'creater'  =>array(),
			'keywords'=>array(),
			'model'=>'',
			'photodesc'=>'',
			'photoloc'=>array(),
			'score'=>0,
			'copyright'=>array('外交部國際傳播司'),
		    'meta' => $meta_extract
		);
		
		foreach($meta_extract as $meta_line){
			$meta = preg_split('/\s+/',$meta_line);
			$tag = array_shift($meta);
			switch($tag){ 
				
				case 'Exif.Image.ImageDescription':
				case 'Iptc.Application2.Caption':
				  $desc = trim(join(' ',$meta));
				  if($desc) $extract['photodesc'] = $desc;
				  break;
				
				case 'Exif.Photo.DateTimeOriginal':
				  $tmp_time = strtotime(trim(join(' ',$meta)));
				  if($tmp_time){
					$extract['phototime'] =  date('Y-m-d H:i:s',$tmp_time);
				  }
				  break;
				
				case 'Exif.Image.Artist':
				case 'Iptc.Application2.Byline':
				case 'Xmp.dc.creator':
				  $extract['creater'][] = trim(join(' ',$meta));
				  break;
				  
				  
				case 'Iptc.Application2.City':
                case 'Xmp.photoshop.City':
                  $extract['photoloc'][] = trim(join(' ',$meta));
                  break;				  
				
				case 'Exif.Image.Model':                              //NIKON D800
				  $extract['model'] = trim(join(' ',$meta));
				  break;
				
				case 'Xmp.xmp.Rating':
				  $extract['score'] = intval(join('',$meta));
				  break;
				  
				//case 'Exif.Image.XPKeywords':  同Iptc.Application2.Keywords
				case 'Iptc.Application2.Keywords':
				  $kwstring = join(' ',$meta); // 排除作者
				  if(!in_array($kwstring,$extract['creater']) && !in_array(strtoupper($kwstring),$extract['creater'])){
					foreach($meta as $m){
					  $extract['keywords'][] = $m;	
					}
				  }
				  break;
				  
			    /*  預設為國傳司
				case 'Iptc.Application2.Copyright':                   //taiwan panorama magazine // haoprophoto
				case 'Exif.Image.Copyright': 
				  $extract['copyright'][] = trim(join(' ',$meta));		
				  break;
				*/
			    
			}
		}
		
		if(!count($extract['creater'])){
	      $extract['creater'][] = $this->UploData['creater'];
		}
		
		// set copyright
		// 中文編碼現在未知，須先轉換為 unicode \u465 格式
		$insert_string  = '外交部國際傳播司';
		$unicode_string = substr(json_encode(array($insert_string)),2,-2);
		exec(_SYSTEM_ROOT_PATH.'mvc/lib/exiv2-0.25-win/exiv2.exe -M"set Exif.Image.Copyright '.$unicode_string.'" '.$FileSorce  , $meta_extract, $return_var);
		
		
		
		
	  return $extract;	
	}
	
	
	//-- 處理影像檔案檔案
	public function process_image($itype , $fileSource , $StoreSlot , $FileName){
	  $imgprocess = array('o'=>'','s'=>'','t'=>'');	
	  
	  // 將檔案預先處理成為  JPG
      switch($itype){
		case 'jpg':  $file_process = self::process_jpg($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.jpg'); break;
		case 'png':  $file_process = self::process_png($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.png'); break;
        case 'tif':  $file_process = self::process_tif($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.tif'); break;
        case 'cr2':  $file_process = self::process_cr2($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.cr2'); break;
        case 'dng':  $file_process = self::process_dng($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.dng'); break;
		case 'raf':  $file_process = self::process_raf($fileSource,_SYSTEM_FILE_PATH.$StoreSlot.'original/'.$FileName.'.raf'); break;
        default: return false; 
	  }
	  
	  if(!$file_process || !is_file($file_process)){ return false; }
	  
	  $file_system		= _SYSTEM_FILE_PATH.$StoreSlot.'system/'.$FileName.'.jpg'; 
	  $file_thumb		= _SYSTEM_FILE_PATH.$StoreSlot.'thumb/'.$FileName.'.jpg'; 
	  
	  list($width, $height, $type, $attr) = getimagesize($file_process);  
	  $imgprocess['o']  = $width.' x '.$height;
	  $imgprocess['s']  = self::image_resize($file_process,$file_system,'m');  // 處理系統圖片
	  $imgprocess['t']  = self::image_resize($file_system,$file_thumb,'s');    // 處理縮圖
	  unlink($fileSource);
      return $imgprocess;
	}
	
	//-- 處理 JPEG 檔案
	public function process_jpg($fileSource,$fileSave){
	  if(!copy($fileSource,$fileSave)){ return false; }
	  return $fileSave;
	}
	
	
	//-- 處理 PNG 檔案
	public function process_png($fileSource , $fileSave){
	  $file_extract = preg_replace(array('/original/','/\.png$/'),array('extract','.jpg'),$fileSave);
	  if(!copy($fileSource,$fileSave)){ return false; }
	  exec(_SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/convert.exe '.$fileSave.' '.$file_extract ,$output, $return_var);
	  
	  if(count($output)) file_put_contents("process.log",print_r($output, true),FILE_APPEND | LOCK_EX);
	  if(!is_file($file_extract)){
	    $im = new Imagick( $fileSave );
	    $im->setImageFormat( 'jpg' );
	    $im->writeImage( $file_extract);
	    $im->clear();
	    $im->destroy();
	  }	
	  return $file_extract;
	}
	
	
	//-- 處理 TIFF 檔案
	public function process_tif($fileSource , $fileSave){
	  $file_extract = preg_replace(array('/original/','/\.tif$/'),array('extract','.jpg'),$fileSave);
	  if(!copy($fileSource,$fileSave)){ return false; }
	  exec(_SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/convert.exe '.$fileSave.' '.$file_extract ,$output, $return_var);
	  if(count($output)) file_put_contents("process.log",print_r($output, true),FILE_APPEND | LOCK_EX);
	  if(!is_file($file_extract)){
	    $im = new Imagick( $fileSave );
	    $im->setImageFormat( 'jpg' );
	    $im->writeImage( $file_extract);
	    $im->clear();
	    $im->destroy();
	  }	
	  return $file_extract;
	}
	
	//-- 處理 RAF 檔案
	public function process_raf($fileSource , $fileSave){
	  $file_extract = preg_replace(array('/original/','/\.raf$/'),array('extract','.jpg'),$fileSave);
	  if(!copy($fileSource,$fileSave)){ return false; }
	  exec(_SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/convert.exe '.$fileSave.' '.$file_extract ,$output, $return_var);	
	  //exec('gm -convert '.$fileSave.' '.$file_extract ,$output, $return_var);
	  if(count($output)) file_put_contents("process.log",print_r($output, true),FILE_APPEND | LOCK_EX);	
	  if(!is_file($file_extract)){
		$im = new Imagick( $fileSave );
	    $im->setImageFormat( 'jpg' );
	    $im->writeImage( $file_extract );
	    $im->clear();
	    $im->destroy();	  
	  }
	  return $file_extract; 
	}
	
	//-- 處理 DNG 檔案
	public function process_dng($fileSource , $fileSave){
	  
	  $file_extract = preg_replace(array('/original/','/\.dng$/'),array('extract','.jpg'),$fileSave);
	  if(!copy($fileSource,$fileSave)){ return false; }
	  exec(_SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/convert.exe '.$fileSave.' -sigmoidal-contrast 3,0%  '.$file_extract ,$output, $return_var);	
	  //exec(_SYSTEM_ROOT_PATH.'mvc/lib/ImageMagick-7.0.0-0-portable-Q16-x64/convert.exe '.$fileSave.' -brightness-contrast 50x25  '.$file_extract ,$output, $return_var);	
	  
	  if(count($output)) file_put_contents("process.log",print_r($output, true),FILE_APPEND | LOCK_EX);	
	  if(!is_file($file_extract)){
		$im = new Imagick( $fileSave );
	    $im->setImageFormat( 'jpg' );
	    $im->writeImage( $file_extract );
	    $im->clear();
	    $im->destroy();	  
	  }
	  return $file_extract; 
	}
	
	//-- 處理 CR2 檔案
	public function process_cr2($fileSource , $fileSave){
	  $file_extract = preg_replace(array('/original/','/\.cr2$/'),array('extract','-preview3.jpg'),$fileSave);
	  $extract_location = preg_replace('/original.*$/','extract/',$fileSave);
	  if(!copy($fileSource,$fileSave)){ return false; }
	  exec(_SYSTEM_ROOT_PATH.'mvc/lib/exiv2-0.25-win/exiv2 -ep3 -l '.$extract_location.' '.$fileSave,$output, $return_var);
	  if(count($output)) file_put_contents("process.log",print_r($output, true),FILE_APPEND | LOCK_EX);
	  if(!is_file($file_extract)){
		$im = new Imagick( $fileSave );
		$im->setImageFormat( 'jpg' );
		$im->writeImage( $file_extract);
		$im->clear();
		$im->destroy();		
	  }
	  return $file_extract; 
	}

	public function image_resize($filesource,$filesave,$size){	
		/** PHP GD : resize an image using GD library */

		// File and new size //the original image has 800x600
		$filename = $filesource;
		
		// Get new sizes
		list($width, $height) = getimagesize($filename);
		
		$bound 	 = $size == 'm' ? 1024 : 128;
		$quilty  = $size == 'm' ? 80 : 50;
		$base 	 = $width >= $height ? $width : $height; 
		
		$percent = 1;
		while( $base*$percent > $bound ){
		  $percent-=0.01;
		}
		
		$newwidth  = intval($width * $percent);
		$newheight = intval($height * $percent);
		
		$thumb = new Imagick($filesource);
		$thumb->setImageCompression(imagick::COMPRESSION_JPEG); 
        $thumb->setImageCompressionQuality($quilty); 
		$thumb->resizeImage($newwidth,$newheight,Imagick::FILTER_LANCZOS,1);
		
		// #20170208 updated
		$orientation = $thumb->getImageOrientation(); 
		switch($orientation) { 
          case imagick::ORIENTATION_BOTTOMRIGHT: 
            $image->thumb(new ImagickPixel(),  180); // rotate 180 degrees 
            break; 

          case imagick::ORIENTATION_RIGHTTOP: 
            $image->thumb(new ImagickPixel(),  90); // rotate 90 degrees CW 
            break; 

          case imagick::ORIENTATION_LEFTBOTTOM: 
            $image->thumb(new ImagickPixel(),  -90); // rotate 90 degrees CCW 
            break; 
        } 
        
		$thumb->writeImage($filesave);
		$thumb->destroy(); 
		
		
		/* GD - 畫質很差
		$thumb	 	= imagecreatetruecolor($newwidth, $newheight); // Load
		$source 	= imagecreatefromjpeg($filename);
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height); // Resize
        imagejpeg($thumb , $filesave,100);
		imagedestroy($thumb);
		*/
		
		return is_file($filesave) ? $newwidth.' x '.$newheight : '';
    }
	
	//-- 錯誤紀錄
	public function processfalse($falseLogs){
	  $this->DB->DBLink->query("UPDATE task_upload SET _process='".date('Y-m-d H:i:s')."',_logs='".$falseLogs."' WHERE urno = ".$this->UploData['urno'].";");
	}
	
	
	//-- 最終完成手續
	public function finishImport(){
      
	  // 登記資料夾檔案
      $this->DB->DBLink->query("INSERT INTO folder_map VALUES(".$this->UploData['folder'].",".$this->MetaData['system_id'].",NULL);");	   
	
	  // 資料夾計數	  
      $this->DB->DBLink->query("UPDATE user_folder SET files=(files+1) WHERE ufno=".$this->UploData['folder']." AND owner=".$this->UserData['uid'].";");	 
	  
	  // 完成上載queue
	  $this->DB->DBLink->query("UPDATE task_upload LEFT JOIN user_task ON utk=utkid SET task_done=(task_done+1),_archived='".date('Y-m-d H:i:s')."',_logs='".$this->MetaData['identifier']."' WHERE urno = ".$this->UploData['urno'].";");	   
        
	  return true;
	}
	
	//-- 最終任務
	public function finishTask(){
      $this->DB->DBLink->query("UPDATE user_task SET time_finish='".date('Y-m-d H:i:s')."' WHERE utk='".$this->Task."';");  
	  return true;
	}
	
	
	public function processImport(){
	  
	  $newfile = array();
	  
	  try{
		
        if(!count($this->UploadRecord)){
		  throw new Exception('目前無待輸入資料');  	
		}   
        
		foreach($this->UploadRecord as $ufile){
		  
		  echo $ufile['name'].":";
		  
		  //-- 讀取檔案
		  $check = self::prepareImport($ufile);
		  if($check!==true){
			self::processfalse($check);
			echo $check;
			continue;
		  }			  
		  
		  //-- 處理匯入
		  $active = self::activeImport();
		  if($active!==true){
			self::processfalse($active); 
			echo $check;
            continue;
		  }     
		  
		  //-- 完成匯入手續 
		  $finish = self::finishImport();
		  if($finish!==true){
			echo $finish;
		  }
		  echo " done.\n";
		}
		
		self::finishTask();
        
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
  
  $import_update = new ImportPhoto($db,$task_num);
  $import_update->processImport();
  


?>