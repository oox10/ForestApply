<?php
  //NTU 自動簽到退作業
  date_default_timezone_set("Asia/Taipei");
  
  define('_SYSTEM_ROOT_PATH',dirname(__FILE__).'\\');
  
  class ntulogin_2015{
	  
	protected  $_USER_NAME = '';
	protected  $_USER_PASS = '';
	
	public 		$page_content    = '';	
	public      $page_form       = array('address'=>'' ,'title'=>'' , 'action'=>'' , 'submit'=>'');	
	public      $page_post       = array();
	public      $page_refer      = 'https://my.ntu.edu.tw/attend/ssi.aspx';
	public      $page_next       = false;
	public      $page_count      = 0;
	public      $stpe_list     	 = array();
	
	public      $search_date     = '';
	
	function __construct($UserName,$UserPASS){
	  $this->_USER_NAME = $UserName; 
	  $this->_USER_PASS = $UserPASS; 	
	  if(date('H') < 12 ){
		$this->stpe_list     = array('btnLogin'=>0,'Submit'=>0,'btnSignIn'=>0,'btnLogout2myntu'=>0);
	  }else{
		$this->stpe_list     = array('btnLogin'=>0,'Submit'=>0,'btnSignOut'=>0,'btnLogout2myntu'=>0);
	  }	
	  file_put_contents(_SYSTEM_ROOT_PATH.'cookie_tmp\cookie.txt','');  
	  
	  $this->search_date     = (date('Y')-1911).'-'.date('m').'-'.date('d',strtotime('-3 day'));
	  
	}
	
	
	function __destruct(){
      $handle = fopen("logs.log", "a");
	  fwrite ( $handle , date('Y-m-d H:i:s').' '.json_encode($this->stpe_list) );
	  fclose($handle);
	  self::initial_variable(); 
	}
	
	public function initial_variable(){
	  $this->page_form       = array('address'=>'' , 'title'=>'' , 'action'=>'' , 'submit'=>'');	
	  $this->page_post       = array();	
	  $this->page_next		 = false;
	}
    
	public function load_page($Address,$Refer,$PostField=array()){
	  echo date('Y-m-d H:i:s')."-----------------------------\n";
	  echo "PAGE : ".$Address."\n";
	  echo "Post : ".join('&',$PostField)."\n";
	  
	  $this->page_content    = '';	
	  $this->page_refer 	 = $Address;
	  
	  $ch = curl_init();
      $options = array(CURLOPT_URL => $Address,
                   CURLOPT_HEADER => 1,
				   CURLOPT_NOBODY => 0,
                   CURLOPT_RETURNTRANSFER => true,
                   CURLOPT_USERAGENT 	=> "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0",
				   CURLOPT_REFERER   	=> $Refer,
				   CURLOPT_COOKIEFILE	=> _SYSTEM_ROOT_PATH.'cookie_tmp\cookie.txt',
                   CURLOPT_COOKIEJAR 	=> _SYSTEM_ROOT_PATH.'cookie_tmp\cookie.txt',
				   CURLOPT_FOLLOWLOCATION => true ,
				   CURLOPT_AUTOREFERER=> true,
				   CURLOPT_SSL_VERIFYPEER => 0,
                   CURLOPT_SSL_VERIFYHOST => 2,
                   CURLOPT_CAINFO => getcwd() . _SYSTEM_ROOT_PATH."CAcerts\GTECyberTrustGlobalRoot.crt",
                  );
		
      if(count($PostField)){
		$options[CURLOPT_POST] = 1;
		$options[CURLOPT_POSTFIELDS] = join('&',$PostField);
	  }
		
	  file_put_contents('logs.txt',print_r($PostField,true));	
	  
      curl_setopt_array($ch, $options);
	  $this->page_content = curl_exec($ch);	
	  $this->page_next    = false;
	  curl_close($ch);
	  file_put_contents(_SYSTEM_ROOT_PATH.'pages_tmp\page_'.$this->page_count.'.html',$this->page_content);
	  echo "SAVE : ".'page_'.$this->page_count.'.html'."\n";
	  $this->page_count++;
	  sleep(2);
	  return 1;
	}
	
	
	public function analysis_page(){
	 	  
	  try{
	    
		if(!$this->page_content){
		  throw new Exception('NO PAGE CONTENT');  	
		}
        
		// initial page
		self::initial_variable();
		
		// get next address
		if(preg_match('/<form.*?action="?(.*?)"?\s+/',$this->page_content,$action)){
		  $this->page_form['action'] = $action[1];  
		}
	    
		
		if(preg_match('/<title>(.*?)<\/title>/',preg_replace('/[\r\n]+/',' ',$this->page_content),$title)){
		  $this->page_form['title'] = trim($title[1]);
		}
		
		if(preg_match('/<input type="submit".*?id="(.*?)"/',$this->page_content,$submit)){
		  $this->page_form['submit'] = $submit[1];
		}
		
		echo "CONT : ";
		echo mb_convert_encoding($this->page_form['title'],'BIG5','UTF8')." / " .$this->page_form['action']."\n\n";
		
		
		// get post data
		preg_match_all('/<input type="(hidden|submit)".*?name="(.*?)".*?value="(.*?)"/',$this->page_content,$matchs,PREG_SET_ORDER);
	    $page_post = array(); 
		// built post data
	    if(count($matchs)){
		  foreach($matchs as $post){
            if($post[1]=='submit'){
			  if(array_key_exists($post[2],$this->stpe_list)&&!$this->stpe_list[$post[2]]  ){
				$this->stpe_list[$post[2]] = 1;
				$this->page_post[] = $post[2].'='.rawurlencode($post[3]);  
			    break;
			  }
			}else{
			  $this->page_post[] = $post[2].'='.rawurlencode($post[3]);	
			}
		  }    
	    }
	    
		switch($this->page_form['action']){
		  
		  case 'ADMLogin.aspx':
		    $this->page_form['address'] = 'http://pa.forest.gov.tw/ForestApply/admin/ADMLogin.aspx'; 	
		    $this->page_post[] = 'txtUId='.$this->_USER_NAME;
			$this->page_post[] = 'txtPasswd='.$this->_USER_PASS;
			$this->page_post[] = 'ibLogin.x=73';
			$this->page_post[] = 'ibLogin.y=53';	
			break;
			
		  case 'ADMReservedApply.aspx':
		    $this->page_form['address'] = 'http://pa.forest.gov.tw/ForestApply/admin/ADMReservedApply.aspx'; 	
            $this->page_post[] = 'ctl00%24ContentPlaceHolder1%24searchApplyDate='.$this->search_date ; 
			$this->page_post[] = 'ctl00%24ContentPlaceHolder1%24QueryBtn='.rawurlencode('查詢'); 
			break;
		  default: break; 
		}
		
		$this->page_next = ($this->page_form['address']) ? true : false;
		
	  } catch (Exception $e) {
        echo 'ERROR:'.$e->getMessage().' '.date('Ymd H:i:s')."\n";
      }
	}
	
	
	public function analysis_apply(){
	 	  
	  try{
	    
		if(!$this->page_content){
		  throw new Exception('NO PAGE CONTENT');  	
		}
        
		// initial page
		self::initial_variable();
		
		// get next address
		if(preg_match('/<form.*?action="?(.*?)"?\s+/',$this->page_content,$action)){
		  $this->page_form['action'] = $action[1];  
		}
	    
		
		if(preg_match('/<title>(.*?)<\/title>/',preg_replace('/[\r\n]+/',' ',$this->page_content),$title)){
		  $this->page_form['title'] = trim($title[1]);
		}
		
		if(preg_match('/<input type="submit".*?id="(.*?)"/',$this->page_content,$submit)){
		  $this->page_form['submit'] = $submit[1];
		}
		
		echo "CONT : ";
		echo mb_convert_encoding($this->page_form['title'],'BIG5','UTF8')." / " .$this->page_form['action']."\n\n";
		
		// get post data
		preg_match_all('/<input type="(hidden|submit)".*?name="(.*?)".*?value="(.*?)"/',$this->page_content,$matchs,PREG_SET_ORDER);
	    $page_post = array(); 
		// built post data
	    if(count($matchs)){
		  foreach($matchs as $post){
            
			if($post[2]!='__EVENTTARGET' && $post[2]!='__EVENTARGUMENT' &&  $post[2]!='ctl00$ContentPlaceHolder1$QueryBtn' &&  $post[2]!='ctl00$ContentPlaceHolder1$ExportBtn' ){
			  $this->page_post[] = $post[2].'='.rawurlencode($post[3]);	
			}
		  }    
	    }
	    
		// get record data
		//__doPostBack(&#39;ctl00$ContentPlaceHolder1$gv&#39;,&#39;Select$10&#39;)
		
		/*
		<tr style="background-color:White;height:22px;">
			<td align="center">苗栗三義火炎山自然保留區</td><td align="center">R10603610</td><td align="center">2017/9/1</td><td align="center">張雲興</td><td align="center">
                            <span id="ctl00_ContentPlaceHolder1_gv_ctl12_Label1">2017/9/23</span>
                            ~
                            <span id="ctl00_ContentPlaceHolder1_gv_ctl12_Label2">2017/9/23</span>
                        </td><td align="center">
                            <span id="ctl00_ContentPlaceHolder1_gv_ctl12_Label3">否</span>
                        </td><td align="center">正取送審</td><td align="center" style="width:40px;"><a href="javascript:__doPostBack(&#39;ctl00$ContentPlaceHolder1$gv&#39;,&#39;Select$10&#39;)">審核</a></td>
		</tr>
		*/
		echo "analysis:\n";
		$apply_link = array();
		$page_link = array();
		if(preg_match_all('/__doPostBack\(&#39;(.*?)&#39;,&#39;(.*?)&#39;\)/',$this->page_content,$apply_rows,PREG_SET_ORDER)){
		  foreach($apply_rows as $apply){
            
			if(preg_match('/^Select/',$apply[2])){
			  $apply_link[] = [
			    '__EVENTTARGET='.rawurlencode($apply[1]),
			    '__EVENTARGUMENT='.rawurlencode($apply[2]),
			    'ctl00%24ContentPlaceHolder1%24searchApplyDate='.$this->search_date,
			  ];
			}else if(preg_match('/^Page/',$apply[2])){
			  $page_link[] = [
			    '__EVENTTARGET='.rawurlencode($apply[1]),
			    '__EVENTARGUMENT='.rawurlencode($apply[2]),
			    'ctl00%24ContentPlaceHolder1%24searchApplyDate='.$this->search_date,
			  ];
			}
			
		  }
		}
		
		$now_page = 1;
		do{
		  
		  // access apply record 
		  echo "Paser Page.".$now_page." : ".count($apply_link)." Applied Data:\n";
		  foreach($apply_link as $i => $query_post){
		    echo "Access Apply ".($i+1).':';
		    $this->load_page($this->page_refer,$this->page_refer,array_merge($this->page_post,$query_post));
		  
		    exit(1);
		  }
		  
		  // 進入下一頁
		  $next_page_post = array_shift($page_link);
		  $now_page += 1;  
          echo "\nChange Page.\n";
		  $this->load_page($this->page_refer,$this->page_refer,array_merge($this->page_post,$page_link));			
		  
		}while(count($page_link));
		
	    
		// 換頁 :繼續
		// 設定換頁
		//$this->page_next = ($this->page_form['address']) ? true : false;
		
	  } catch (Exception $e) {
        echo 'ERROR:'.$e->getMessage().' '.date('Ymd H:i:s')."\n";
      }
	}
  
	
	
  }
  
  
  
  
  echo "ntu auto login 2015 v2  .\n";
  echo "start process - ".date('c')."start: \n";
  
  $today = intval(date('Ymd'));
  
  try{
	
    
	// 執行登入
    $hsiao = new ntulogin_2015('ntu10','1010');
    $hsiao->load_page('http://pa.forest.gov.tw/ForestApply/admin/ADMLogin.aspx','http://pa.forest.gov.tw/ForestApply/admin/ADMLogin.aspx',[]);
    $hsiao->analysis_page();  
    if($hsiao->page_next){
	  sleep(5);	
      $hsiao->load_page($hsiao->page_form['address'],$hsiao->page_refer,$hsiao->page_post);
    }
   
    // 執行主頁
    $hsiao->load_page('http://pa.forest.gov.tw/ForestApply/admin/ADMReservedApply.aspx','http://pa.forest.gov.tw/ForestApply/admin/ADMMain.aspx',[]); 
	$hsiao->analysis_page(); 
	if($hsiao->page_next){
	  sleep(5);	
      $hsiao->load_page($hsiao->page_form['address'],$hsiao->page_refer,$hsiao->page_post); //執行查詢
      $hsiao->analysis_apply();  
    
	  
	
	
	}
	
	/*
	while($hsiao->page_next){
	  sleep(5);	
      $hsiao->load_page($hsiao->page_form['address'],$hsiao->page_refer,$hsiao->page_post);	
	  $hsiao->analysis_main(); 
      	  
	  exit(1);
	}
	*/
	
	
	
	echo "-------------------------- \n";
    echo "finish";
    exit(1);

	
  } catch (Exception $e) {
    echo 'ERROR: '.$e->getMessage().' '.date('Ymd H:i:s')."\n";
  }
  
 
  
  
  
  
  
?>