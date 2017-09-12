<?php
  class DBModule{
   
	private $DB_Name	 = _SYSTME_DB_NAME;
	private $DB_User	 = _SYSTEM_DB_USER;
    private $DB_Password = _SYSTEM_DB_PASS;
	private $DB_Host	 = _SYSTEM_DB_LOCA;
	
	public $DBLink;
	protected $SQLResult;
	
    public function db_connect($DBMode='PDO'){
	  switch($DBMode){
	    case 'PDO':
		  try {
            $this->DBLink = new PDO("mysql:host=".$this->DB_Host.";dbname=".$this->DB_Name.';charset=utf8', $this->DB_User, $this->DB_Password);
			$sql = "SET NAMES 'utf8'";
            $stmt = $this->DBLink->query($sql);
            //偵錯模式
			$this->DBLink->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          }catch(PDOException $e){
            echo $e->getMessage();
          }
	      break;	  
	  
	    case 'MySql':
	      $this->DBLink = mysql_connect("localhost",$this->DB_User,$this->DB_Password) or die(mysql_error());
          mysql_query("SET NAMES 'utf8'");
          mysql_select_db($this->DB_Name,$this->DBLink);
          
	      break;
	  
	    default: "dbconnect error @ DBModule" ; exit(1); break;
	  }
	}
	
  }
  
?>