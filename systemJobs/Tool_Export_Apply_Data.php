<?php
  
  define('ROOT',dirname(dirname(__FILE__)).'/');
  
  require_once(dirname(dirname(__FILE__)).'/conf/system_config.php');
  require_once(dirname(dirname(__FILE__)).'/mvc/core/DBModule.php');   
  
  
   try{
	
	$db = new DBModule;
    $db->db_connect('PDO'); 
  
    $target_area = 1;
	
	$DBBOOK = $db->DBLink->prepare("SELECT * FROM area_booking WHERE am_id=:amid AND _final = '核准進入' AND date_enter BETWEEN '2018-01-01' AND '2018-12-31' ORDER BY date_enter ASC");
    $DBBOOK->bindValue(':amid',$target_area);
	$DBBOOK->execute();
	$book_record = [];
	
	$data_counter = [];
	
	while($b = $DBBOOK->fetch(PDO::FETCH_ASSOC)){
	  
	  $apply = json_decode($b['apply_form'],true);
	  
	  $apply_goto = join(',',$apply['area']['inter']);
	  $apply_gate = $apply['area']['gate']['entr'];
	  $apply_mnum = $b['member_count'];
	  
	  //$apply['area']['gate']['exit']
	  if(!isset($data_counter[$apply_goto])) $data_counter[$apply_goto] = [];
	  if(!isset($data_counter[$apply_goto][$apply_gate])) $data_counter[$apply_goto][$apply_gate] = 0; 
	  $data_counter[$apply_goto][$apply_gate]+=$apply_mnum;
	  ksort($data_counter[$apply_goto]);
	}
	ksort($data_counter);
	
	file_put_contents('export.txt','');
	
	foreach($data_counter as $area_to => $gates){
      foreach($gates as $g => $c){
		file_put_contents('export.txt',$area_to."\t".$g."\t".$c."\n",FILE_APPEND);  
	  }
	}
	/*
	
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
	*/
  
  } catch (Exception $e) {
	echo $e->getMessage();
  }	
  
  
  
  

?>