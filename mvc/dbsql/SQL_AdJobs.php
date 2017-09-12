<?php
  
  /*
  *   [RCDH10 Admin Module] - Apply Admin Sql Library 
  *   System Jobs SQL SET
  *
  *   2016-11-16 ed.  
  */
  
  /* [ System Execute function Set ] */ 	
  
  class SQL_AdJobs{
	
   
	/***-- Admin Jobs SQL --***/
	
	
	//-- Admin Jobs : Get Mail Jobs
	public static function GET_MAIL_JOBS(){
	   $SQL_String = "SELECT * FROM system_mailer WHERE _status_code=0 AND _keep=1 AND _mail_date=:mail_date;";
	  return $SQL_String;
	}
	
	//-- Admin Jobs : Update Mail Jobs
	public static function UPDATE_MAIL_JOBS(){
	   $SQL_String = "UPDATE system_mailer SET _status_code=:status,_active_time=:acttime,_result=:result,_active_logs=:activelogs WHERE smno=:smno AND _keep=1;";
	  return $SQL_String;
	}
	
  }

?>  