<?php
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  
  
   try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
  
    $target_area = isset($_REQUEST['area']) && intval($_REQUEST['area']) ? intval($_REQUEST['area']) : 0;
	$date_enter  = isset($_REQUEST['date']) && strtotime($_REQUEST['date']) ? date('Y-m-d',strtotime($_REQUEST['date'])):'0000-00-00';
	$lottologs   = isset($_REQUEST['logs']) && intval($_REQUEST['logs']) ? 1:0;
    
	if(!$target_area  ||  $date_enter=='0000-00-00'){	
	   throw new Exception('參數錯誤');
	}
	
	
    $DBBOOK = $db->DBLink->prepare("SELECT * FROM area_booking WHERE am_id=:areaid AND date_enter=:denter ORDER BY _status ASC");
    $DBBOOK->bindValue(':areaid',$target_area);
	$DBBOOK->bindValue(':denter',$date_enter);
	$DBBOOK->execute();
	$book_record = [];
	while($b = $DBBOOK->fetch(PDO::FETCH_ASSOC)){
	  $book_record[$b['apply_code']] = $b; 	
      $book_record[$b['apply_code']]['lotto'] = ' X ';	  
	}
	
	
	$DBLOTTO = $db->DBLink->prepare("SELECT * FROM booking_lotto WHERE aid=:areaid AND date_enter=:denter");
    $DBLOTTO->bindValue(':areaid',$target_area);
	$DBLOTTO->bindValue(':denter',$date_enter);
	$DBLOTTO->execute();
	while($lotto = $DBLOTTO->fetch(PDO::FETCH_ASSOC)){
	  
	  $lotto_record = $lotto;
      $pool = json_decode($lotto['lotto_pool'],true);	  
	  
	  foreach($pool as $ab){
		if(isset($book_record[$ab['code']])){
		  $book_record[$ab['code']] = array_merge($book_record[$ab['code']],$ab);
	    }else{
		  echo "無申請資料 : ".$ab['code']."\t".$ab['date']."\t".$ab['leader']."\t".$ab['people']."\t".$ab['insert']."\t".$ab['accept']."\t".$ab['lotto']."\t".$ab['review']."\n";
		}
	  }
	}
	
	echo "<table border=1>";
	echo "<tr  style='font-weight:bold;'>".
	      "<td>申請編號</td><td>申請日期</td><td>申請理由</td><td>申請人</td><td>成員數量</td><td>是否抽籤</td>".
		  "<td>抽籤日期</td><td>階段</td><td>申請狀態</td><td>最終狀態</td><td>抽籤結果</td>".
		  "</tr>";
	 
    $apply_counter = 0;
	$enter_counter = 0;
	foreach( $book_record as $book){
	  echo "<tr>";
	  echo "<td>".$book['apply_code']."</td>".
	       "<td>".$book['apply_date']."</td>".
		   "<td>".$book['apply_reason']."</td>".
		   "<td>".$book['applicant_name']."</td>".
		   "<td>".$book['member_count']."</td>".
		   "<td>".$book['_ballot']."</td>".
		   "<td>".$book['_ballot_date']."</td>".
		   "<td>".$book['_stage']."</td>".
		   "<td>".$book['_status']."</td>".
		   "<td>".$book['_final']."</td>".
		   "<td>".(isset($book['review'])?$book['review']:" - ").(isset($book['queue'])?'-'.$book['queue']:" - ")."</td>";
	  echo "</tr>";
	  if($book['_final']!='申請取消' && $book['_final']!='取消申請'){
		$apply_counter+=$book['member_count'];		   
	  }	  
      if($book['_final']=='核准進入'){
		$enter_counter+=$book['member_count'];
	  }	
	}
	
	echo "</table>";
	echo "<pre>";
	echo "------------------------------------------------------\n";
	echo "進入日期: ".$date_enter."／申請: ".$apply_counter."人(排除取消申請與申請取消2種狀態) ／"."核准: ".$enter_counter." 人 (申請註銷不顯示)  \n\n\n";
	
	
	if($lottologs ){
	  echo "------------------------------------------------------\n";
	  echo "抽籤歷程:\n";
	  var_dump(json_decode($lotto_record['logs_process'],true));
	}
	
  
  } catch (Exception $e) {
	echo $e->getMessage();
  }	
  
  
  
  

?>