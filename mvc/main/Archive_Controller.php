<?php
  
  
  /********************************************* 
     Council MtDoc Collect Archive Control Set 
  *********************************************/
	
  class Archive_Controller extends Admin_Controller{
    
	public function __construct(){
	  parent::__construct();	
      $this->Model = new Archive_Model;
	  
	}
	
	// PAGE: 圖片資料庫首頁 - default display
	public function index(){
	  $this->Model->GetUserInfo();
	  $this->Model->GetUserGroups();
	  $this->Model->Get_FileLevel_Array( 'forest','classify','');
	  $this->Model->Get_User_Folders();
	  $this->Model->Get_User_TagReference();
	  $result = $this->Model->ADArchive_Built_Search_Set( 'index' );
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('html','archive',$this->Model->ModelResult);
	}
	
	// PAGE: 資料庫欄位搜尋
	public function search($filter='', $accnum , $slot=0){
	  
	  $this->Model->GetUserInfo();
	  $this->Model->Get_FileLevel_Array( 'forest','classify','');
	  $this->Model->Get_User_Folders();
	  $this->Model->Get_User_TagReference();
	  
	  
	  $result = $this->Model->ADArchive_Built_Search_Set( 'search' , $filter , $accnum);
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('html','archive',$this->Model->ModelResult);
	}
	
	// PAGE: 資料庫類別搜尋
	public function level($filter='' , $slot=0){
	  $this->Model->GetUserInfo();
	  $this->Model->Get_User_Folders();
	  $this->Model->Get_User_TagReference();
	  $this->Model->Get_FileLevel_Array( 'forest','classify',$filter);
	  
	  $result = $this->Model->ADArchive_Built_Search_Set( 'level' , $filter  );
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('html','archive',$this->Model->ModelResult);
	}
	
	// PAGE: 資料庫資料夾
	public function folder($filter='' , $slot=0){
	  $this->Model->GetUserInfo();
	  $this->Model->Get_User_Folders();
	  $this->Model->Get_User_TagReference();
	  $this->Model->Get_FileLevel_Array( 'forest','classify','');
	  
	  $result = $this->Model->ADArchive_Built_Search_Set( 'folder' , $filter  );
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('html','archive',$this->Model->ModelResult);
	}
	
	// PAGE: 資料庫資料夾
	public function tags($filter='' , $slot=0){
	  $this->Model->GetUserInfo();
	  $this->Model->Get_User_Folders();
	  $this->Model->Get_User_TagReference();
	  $this->Model->Get_FileLevel_Array( 'forest','classify','');
	  
	  $result = $this->Model->ADArchive_Built_Search_Set( 'tags' , $filter  );
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('html','archive',$this->Model->ModelResult);
	}
	
	// AJAX: 資料庫條件篩選
	public function pquery($filter='', $accnum , $slot=0){
	  $result = $this->Model->ADArchive_Built_Search_Set( 'pquery' , $filter , $accnum);
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 資料庫設定排序
	public function order($filter='', $accnum , $slot=0){
	  $result = $this->Model->ADArchive_Built_Search_Set( 'sort'  , $filter , $accnum);
	  if($result['action']){
		$this->Model->ADArchive_Built_Result_Data();
	  }
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 建立後分類選項
	public function filter($accnum){
	  $this->Model->ADArchive_Built_Post_Query_Filter( $accnum );
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	
	// AJAX: 讀取剩下的資料
	public function loading($accnum,$solt){
	  $this->Model->ADArchive_Loading_Results( $accnum , $solt);
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	// AJAX: 讀取詮釋資料
	public function meta($image_id){
	  $this->Model->ADArchive_Read_Object_Meta( $image_id );
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	// AJAX: 儲存修改資料
	public function save($image_id,$data){
	  $meta_cahce = isset($_SESSION[_SYSTEM_NAME_SHORT]['identifier']) ? $_SESSION[_SYSTEM_NAME_SHORT]['identifier'] : '';
	  $result = $this->Model->ADArchive_Save_Target_Meta( $image_id , $data , $meta_cahce);
	  if($result['action']){
		$this->Model->ADArchive_Read_Object_Meta( $image_id );    
	  }
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	// AJAX: 刪除影像資料
	public function dele($image_id){
	  $this->Model->ADArchive_Delete_Target_Data( $image_id );
	  self::data_output('json','',$this->Model->ModelResult);  
	}
	
	
	// FILE: 讀取影像
    public function photo($store,$type,$imageid){
	  self::data_output('photo',$store.'/'.$type.'/'.$imageid);
	}
	
	// AJAX: 上傳檢查
	public function uplinit( $data ){
      $this->Model->ADArchive_Upload_Task_Initial($data); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 上傳圖片
	public function uplpho( $fno , $data ){
      $_FILES['file']['lastmdf'] = $_REQUEST['lastmdf'];
	  $this->Model->ADArchive_Upload_Photo($fno , $data , $_FILES); 
	  self::data_output('upload','',$this->Model->ModelResult);
	}
	
	// AJAX: 上傳結束
	public function uplend( $fno ){
      $this->Model->ADArchive_Upload_Task_Finish($fno); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// FILE: 下載圖片 RAW 檔
    public function dwnraw( $image_id){
	  $this->Model->ADArchive_Download_Archive_Image($image_id,'original'); 
	  self::data_output('file','',$this->Model->ModelResult);	
	}
	
	// FILE: 下載圖片 原始大小 JPG
    public function dwnorl( $image_id){
	  $this->Model->ADArchive_Download_Archive_Image($image_id,'original'); 
	  self::data_output('file','',$this->Model->ModelResult);	
	}
	
	// FILE: 下載圖片 瀏覽級縮圖 
    public function dwnmin( $image_id){
	  $this->Model->ADArchive_Download_Archive_Image($image_id,'system'); 
	  self::data_output('file','',$this->Model->ModelResult);	
	}
	
	
	// AJAX: 刪除所選資料
	public function trash( $updata ){
      $this->Model->ADArchive_Remove_User_Selected($updata); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 更新所選資料
	public function update( $updata ){
      $this->Model->ADArchive_Update_User_Selected($updata); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 所選資料打包
	public function export( $updata ){
      $this->Model->ADArchive_Export_User_Selected($updata); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// AJAX: 所選資料放入資料夾
	public function putin( $updata ){
      $this->Model->ADArchive_Putin_User_Selected($updata); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	
	// AJAX: 打包資料下載
	public function package( $package_key ){
      $result = $this->Model->ADArchive_Download_User_Package($package_key); 
	  if($result['action']){
		self::data_output('file','',$this->Model->ModelResult);   
	  }else{
		self::data_output('html','error',$this->Model->ModelResult);  
	  }
	}
	
	// FILE: 匯出資料
    public function excel($type='index',$condition1='',$condition2=''){
	  
	  switch($type){
		case 'index':
        case 'project':		
		  $access = $this->Model->ADRecord_Get_Project_Record($condition1);
          if($access['action']){
		    $this->Model->ModelResult['excel'] = array('action'=>true,'data'=>$this->Model->ModelResult['projects']['data']);
			self::data_output('xlsx',$type,$this->Model->ModelResult);
		  }
		  break;
		case 'account':
		  //$this->Model->ADRecord_Get_Project_Record($filter);
          break;
		case 'doclog':
		  $access = $this->Model->ADRecord_Get_Document_Info($condition1);
	      if($access['action']){
		    $this->Model->ADRecord_Get_Document_Logs($access['data']['docId'],$condition2);    
	        $this->Model->ModelResult['excel'] = array('action'=>true,'data'=>$this->Model->ModelResult['doclogs']['data']);
			
			
			
			self::data_output('xlsx',$type,$this->Model->ModelResult);
		  }
		  break;
	    default:break;
	  }
	}
	
	// AJAX: 旋轉圖片 
	public function rotate( $image_id , $Mode ){
      $this->Model->ADArchive_Rotate_Target_Image( $image_id , $Mode ); 
	  self::data_output('json','',$this->Model->ModelResult);
	}
	
	// FILE: 下載檔案
	public function preview( $MetaId ){
      $this->Model->ADArchive_Get_Document_File( $MetaId ); 
	  self::data_output('preview','',$this->Model->ModelResult);
	}
	
	// FILE: 下載檔案
	public function download( $MetaId ){
      $this->Model->ADArchive_Get_Document_File( $MetaId ); 
	  self::data_output('file','',$this->Model->ModelResult);
	}
	
	
	
	
	
	
  }	