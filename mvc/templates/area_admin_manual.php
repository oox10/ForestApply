        
	  <div class='manual_continer'>	
		<div class='system_mark'>
		  <span class='manual_mark'><i class='option pic_mark_manual mark32'></i></span>
		  <span class='system_title'>
		    <span class='mark_title_word'><?php echo _SYSTEM_NAME_SHORT; ?></span>
		    <span class='mark_version_word'>Pb. <?php echo _SYSTEM_PUBLISH_VERSION; ?></span>
		  </span>
		</div>
	    <ul class='main_manual'>
		  <?php $admin_filter = isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION']['*']) ? true : false; ?>
		  
		  <li>
		    <div class='opgroup_name '>
			  <i class="fa fa-cog" aria-hidden="true"></i><span>系統管理</span>
			</div>
		    <ul class='group_manuel'>
			  <li  title='帳號管理'	class='option func_activate'	id='Staff'		><i class="fa fa-user" aria-hidden="true"></i> <span >帳號與單位管理</span> </li>
			  <li  title='回報管理'	class='option func_activate'	id='Tracking'	><i class="fa fa-wrench" aria-hidden="true"></i> <span >回報管理</span> </li>
			</ul>
		  </li>
		  <li class='option_group'>
		    <div class='opgroup_name '>
			  <i class="fa fa-code" aria-hidden="true"></i> <span>網站管理</span>
			</div>
		    <ul class='group_manuel'>
			  <?php if( $admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Area')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Area')])!=0) ): ?>
			  <li  title='轄區設定'	class='option func_activate'	id='Area'	><i class="fa fa-map-o" aria-hidden="true"></i> <span >轄區設定</span> </li>
			  <?php endif; ?>
			  
			  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Post')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Post')])!=0) ): ?>
			  <li  title='發布消息'	class='option func_activate'	id='Post'	><i class="fa fa-pencil-square-o" aria-hidden="true"></i> <span >發布消息</span> </li>
			  <?php endif; ?>
			  
			  <?php if( $admin_filter || 
			           (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Page')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Page')])!=0 && (isset($user_info['permission']['interface_mask']['admin_manual.html5tpl.php']['module_page']) && $user_info['permission']['interface_mask']['admin_manual.html5tpl.php']['module_page']))  
					): ?>
			  <li  title='網站內容編輯'	class='option func_activate'	id='Page'	><i class="fa fa-file-text-o" aria-hidden="true"></i> <span >網站內容編輯</span> </li>
			  <?php endif; ?>
			  
			 
			  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Mailer')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Mailer')])!=0 )): ?>
			  <li  title='信件寄送'  class='option func_activate'	id='Mailer'	><i class="fa fa-envelope-o" aria-hidden="true"></i> <span >信件寄送</span> </li>
			  <?php endif; ?>
			  
			  <!-- <li  title='民眾信箱' class='option func_activate'	id='Alert'   ><i class="fa fa-envelope-o" aria-hidden="true"></i> <span >民眾信箱</span> </li> -->
			</ul>
		  </li>
		  
		  <li class='option_group'>
		    <div class='opgroup_name '>
			  <i class="fa fa-calendar-check-o" aria-hidden="true"></i> <span>申請管理</span>
			</div>
		    <ul class='group_manuel'>
			  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Booking')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Booking')])!=0) ): ?>
			  <li  title='自然保留區申請管理'	 class='option func_activate'	id='Booking'	><i class="fa fa-calendar" aria-hidden="true"></i> <span >進入申請</span> </li>
			  <?php endif; ?>
			  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Lotto')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Lotto')])!=0 )): ?>
			  <li  title='申請抽籤情況'	 class='option func_activate'	id='Lotto'	><i class="fa fa-check-square" aria-hidden="true"></i> <span >申請抽籤情況</span> </li>
			  <?php endif; ?>
			  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Record')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Record')])!=0 )): ?>
			  <li  title='申請統計'	 class='option func_activate'	id='Record'	><i class="fa fa-bar-chart" aria-hidden="true"></i> <span >申請統計</span> </li>
			  <?php endif; ?>
			</ul>
		  </li>
		  <?php if($admin_filter || (isset($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Archive')]) && intval($_SESSION[_SYSTEM_NAME_SHORT]['ADMIN']['PERMISSION'][strtolower('Archive')])!=0) ): ?>	  
		  <li class='option_group'>
		    <div class='opgroup_name '>
			  <i class="fa fa-database" aria-hidden="true"></i> <span>知識管理</span>
			</div>
		    <ul class='group_manuel'>
			  <li  title=''	 class='option func_activate'	id='Archive'	><i class="fa fa-archive" aria-hidden="true"></i> <span >檔案資料庫</span> </li>
			  <li  title=''	 class='option func_activate'	id='Classify'	><i class="fa fa-folder-open-o" aria-hidden="true"></i> <span >類別編輯</span> </li>
			  
			</ul>
		  </li>
		  <?php endif; ?>
		  <li>
		    <div class='opgroup_name '>
			  <i class="fa fa-tasks" aria-hidden="true"></i> <span>使用者功能</span>
			</div>
		    <ul class='group_manuel'>
			  <li  title='錯誤回報' class='option' id='user_feedback'><i class="fa fa-bug" aria-hidden="true"></i> <span >錯誤回報</span> </li>
			</ul>
		  </li>
		  <li style='display:none;'>
		    
		  </li>
	    </ul>
	  </div>
  