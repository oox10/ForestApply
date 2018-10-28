<?php
  
  /**************************************************
                   系統通用模組
  **************************************************/
  
  
  class System_Helper{
    /*****************************************************
        取得使用者ip
	    來源：http://www.jaceju.net/blog/archives/1913/
    *****************************************************/
    public static function get_client_ip(){
        foreach (array(
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if ((bool) filter_var($ip, FILTER_VALIDATE_IP,
				                FILTER_FLAG_IPV4 |
                                FILTER_FLAG_NO_PRIV_RANGE |
                                FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
      }
      return '::::';
    }
	
	
	/******************************************
	  取得取得client介面
	  
	    參數   
		  1. $agent  Null
		
		回傳
		  Array (
            [browser] => firefox
            [version] => 3.5
          )
         
        提醒  
	
	******************************************/
	
	public static function browser_info($agent=null) {
      
	  $known = array('msie', 'firefox', 'safari', 'opera', 'netscape','chrome');
      $agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
      $pattern = '#(' . join('|', $known) .')[/ ]+([0-9]+(?:\.[0-9]+)?)#';

      // Find all phrases (or return empty array if none found)
      //if (!preg_match($pattern, $agent, $matches)) return array();
      
	  //return array('browser' => $matches[1],'version' => $matches[2]);
      return array('browser' => 'chrome','version' => 'new');
	}
	
	public static function formatBytes($bytes, $precision = 2) { 
		$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

		$bytes = max($bytes, 0); 
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
		$pow = min($pow, count($units) - 1); 
		return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 
	
	public static function byteConvert($bytes)
    {
        $s = array('B', 'Kb', 'MB', 'GB', 'TB', 'PB');
        $e = floor(log($bytes)/log(1024));
      
        return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    }
	
	
	public static function generator_password($StringLength){
      $password_len = intval($StringLength);
      $password = '';

      // remove o,0,1,l
      $word = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ23456789';
      $len = strlen($word);

      for ($i = 0; $i < $password_len; $i++) {
        $password .= $word[rand() % $len];
      }
      return $password;
    }
	
	
	/**
	* @version $Id: str_split.php 10381 2008-06-01 03:35:53Z pasamio $
	* @package utf8
	* @subpackage strings
	*/
	public static function utf8_str_split($str, $split_len = 1)
	{
		if (!preg_match('/^[0-9]+$/', $split_len) || $split_len < 1)
			return FALSE;
 
		$len = mb_strlen($str, 'UTF-8');
		if ($len <= $split_len)
			return array($str);
 
		preg_match_all('/.{'.$split_len.'}|[^\x00]{1,'.$split_len.'}$/us', $str, $ar);
 
		return $ar[0];
	}
	
	
	/**
	文字顯示固定長度
     
	**/
	
	public static function short_string_utf8($string,$width){
	  $string_view = '';
	  
	  if(mb_strwidth($string,'UTF-8')>$width){
        $string_view = mb_strimwidth($string,0,$width,'…','UTF-8'); 
	  }else{
	    $string_view = $string;
	  }
      return $string_view; 
	}
	
	
	/****-----
	  計算影像 DPI
	*********/
	public static function get_dpi($filename){
      $a = fopen($filename,'r');
      $string = fread($a,20);
      fclose($a);

      $data = bin2hex(substr($string,14,4));
      $x = substr($data,0,4);
      $y = substr($data,0,4);

      return array(hexdec($x),hexdec($y));
	} 
	
	
	//-- check TW Id
	/*
	*  身分證字號檢查
	*  reference : http://n.sfs.tw/content/index/10563
	*  2017 05 25
	*/
	public static function check_twid($id) {
	  if( !$id )return false;
	  
	  $id = strtoupper(trim($id)); //將英文字母全部轉成大寫，消除前後空白
	  
	  //檢查第一個字母是否為英文字，第二個字元1 2 A~D 其餘為數字共十碼
	  $ereg_pattern= "/^[A-Z]{1}[12ABCD]{1}[[:digit:]]{8}$/";
	  
	  if(!preg_match($ereg_pattern, $id))return false;
	  
	  $wd_str="BAKJHGFEDCNMLVUTSRQPZWYX0000OI";   //關鍵在這行字串
	  
	  $d1=strpos($wd_str, $id[0])%10;
	  $sum=0;
		
	  if(preg_match('/[ABCD]/',$id[1])) $id[1]=ord($id[1])-65; //第2碼非數字轉換依[4]說明處理
	  
	  for($ii=1;$ii<9;$ii++) $sum+= (int)$id[$ii]*(9-$ii);
	  
	  $sum += $d1 + (int)$id[9];
	  
	  if($sum%10 != 0)return false;
	  
	  return true;
    }
	
	
	
	
	
	//-- 將 MD5 長碼轉自訂短碼
	public static function md5_string_to_short_code($MD5String){
	    
		$hax = base64_encode(md5($MD5String.time(),true));
		$clean_hax = str_split(strtr($hax, array('+'=>'-','/'=>'_')));
		shuffle($clean_hax); 
		return substr(join('',$clean_hax),rand(0,2)*7,7);
		
		/*
		$base64 = str_split('OlJFu0Gt1Hs2Ir3TgUfAz5By6Cx7Dq4KmVeNjRiShPkQpLoMnw8Ev9WdXcYbZa_-');
		shuffle($base64);
		$hash = $MD5String; 
        $output = array(); 
        */
	}
    
	
	//-- CRC32 MAP TO 62CHR
	/**
	* Small sample convert crc32 to character map
	* Based upon http://www.php.net/manual/en/function.crc32.php#105703
	* (Modified to now use all characters from $map)
	* (Modified to be 32-bit PHP safe)
	*/
	public static function khashCRC32($data)
	{
		static $map = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$hash = bcadd(sprintf('%u',crc32($data)) , 0x100000000);
		$str = "";
		do
		{
			$str = $map[bcmod($hash, 62) ] . $str;
			$hash = bcdiv($hash, 62);
		}
		while ($hash >= 1);
		return $str;
	}
	
	
	// Function to remove folders and files 
	public static   function rrmdir($dir) {
		if (is_dir($dir)) {
			$files = scandir($dir);
				foreach ($files as $file) if ($file != "." && $file != "..") self::rrmdir("$dir/$file");
				rmdir($dir);
		}else if (file_exists($dir)) unlink($dir);
	  }
	  
	  // Function to Copy folders and files       
	public static function rcopy($src, $dst) {
		if (file_exists ( $dst ))  self::rrmdir ( $dst );
		
		if (is_dir ( $src )) {
			mkdir ( $dst );
			$files = scandir ( $src );
			foreach ( $files as $file )
			   if ($file != "." && $file != "..") self::rcopy ( "$src/$file", "$dst/$file" );
		} else if (file_exists ( $src ))  copy ( $src, $dst );
	}
	////rcopy($source , $destination );  
	  
	  
	//-- 磁區大小顯示轉換  refer:http://php.net/manual/en/function.disk-total-space.php   2016/02/22
	public static function getSymbolByQuantity($bytes , $unit=0 ) {
      $symbols = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
	  $exp = intval($unit) ? $unit : floor(log($bytes)/log(1024));
      return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
    }  
	  
	  
	
	/*
	*  文字簡繁轉換
    *  use: Mediawiki zhconverter
	*  reference : 
	*		1. 使用 Mediawiki zhconverter 進行 PHP 網頁簡繁互轉 http://cw1057.blogspot.tw/2012/06/mediawiki-zhconverter-php.html
	*		2. mediawiki-zhconverter https://code.google.com/p/mediawiki-zhconverter/
	*		3. PHP繁簡轉換 http://xyz.cinc.biz/2013/03/php.html
	*   PS: mediawiki-zhconverter.inc.php 目前最多只能使用  1.15.5 版本  其他更高版本會有錯誤訊息
	*/
	
    public static function word_translate($word,$code='zh-tw'){
	  
	  define("MEDIAWIKI_PATH", _SYSTEM_ROOT_PATH."systemOption\\mediawiki-1.15.5\\");
      
	  /* Include our helper class */
      require_once _SYSTEM_ROOT_PATH."systemOption\\mediawiki-zhconverter.inc.php";

      /* Convert it, valid variants such as zh, zh-cn, zh-tw, zh-sg & zh-hk */
	  //echo MediaWikiZhConverter::convert("雪糕", "zh-tw") , ",";
	  //echo MediaWikiZhConverter::convert("記憶體", "zh-cn"), ",";
	  //echo MediaWikiZhConverter::convert("大卫·贝克汉姆", "zh-hk");
	  
	  return MediaWikiZhConverter::convert($word, $code);
	  
	}
	
	/**
    有tag string 切截
	**/
	public static function short_string_width_tags($string,$width=1){
	  
	  mb_internal_encoding("UTF-8");
	  
	  if(preg_match_all('/<.*?>.*?<\/.*?>/',$string,$tags,PREG_PATTERN_ORDER)){
        $maps = array_unique($tags[0]);
	    
		$change_pattern = array();
		$change_replace = array();
		$revarse_pattern = array();
		$revarse_replace = array();
	    
	    foreach($maps as $key => $tag_string){
		  $map_key = self::shiftSpace(chr($key+65),'full');
		  $change_pattern[] = '@'.$tag_string.'@u';
		  $change_replace[] = $map_key;
		  $revarse_pattern[] = '@'.$map_key.'@u';
		  $revarse_replace[] = $tag_string;
		}
		$encode_string = preg_replace($change_pattern ,$change_replace,$string);
		$short_string  = mb_substr($encode_string,0,$width);
		$decode_string = preg_replace($revarse_pattern ,$revarse_replace,$short_string);
		if(mb_strlen($encode_string) > mb_strlen($short_string)) $decode_string.='…';
		return $decode_string;
	  }else{
	    $string_view = mb_substr($string,0,$width);
	    if(mb_strlen($string) > mb_strlen($string_view)) $string_view.='…';
	    return $string_view;
	  }
	}
	
	
	/**
	 * ASCII 字元自動全形/半形轉換 (字碼補位法)
	 *
	 * @authro LIAO SAN-KAI
	 * 
	 * @param string $char 欲轉換的 ASCII 字元
	 * @param string $width 字形模式 half|full|auto (半形|全形|自動)
	 * @return string 轉換後的對應字元
	 */
	public static function shiftSpace($char=null, $width='auto') {

		//取得當前字元的16進位值
		$charHex = hexdec(bin2hex($char));

		//判斷當前字元為半形或全形
		$charWidth = ($char == '　' or ($charHex >= hexdec(bin2hex('！')) and $charHex <= hexdec(bin2hex ('～')))) ? 'full' : 'half';

		//如果字元字形與指定字形一樣，就直接回傳
		if($charWidth == $width) {
			return $char;
		}

		//如果是空白字元就直接比對轉換回傳
		if($char === '　' ) {
			return ' ';
		} elseif($char === ' ') {
			return '　';
		}

		//計算 ASCII 字元16進位的unicode差值
		$diff = abs(hexdec(bin2hex ('！')) - hexdec(bin2hex ('!')));

		//計算字元"_"之後的半形字元修正值(192)
		$fix = abs(hexdec(bin2hex ('＿')) - hexdec(bin2hex ('｀'))) - 1;

		//全形/半形轉換
		if($charWidth == 'full'){
			$charHex = $charHex - (($charHex > hexdec(bin2hex('＿'))) ? $diff + $fix : $diff); 
		} else {
			$charHex = $charHex + (($charHex > hexdec(bin2hex('_'))) ? $diff + $fix : $diff); 
		}

		return hex2bin(dechex($charHex));
	}
  
    
  
  }
  
  

?>