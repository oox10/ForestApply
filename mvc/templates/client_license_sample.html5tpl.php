<!DOCTYPE HTML>
<html style='height:100%;'>
  <head >
    <meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<title><?php echo defined('_SYSTEM_HTML_TITLE') ? _SYSTEM_HTML_TITLE:'資料庫';?></title>
	<!-- CSS -->
	<link type="text/css" href="tool/font-awesome-4.6.2/css/font-awesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_default.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_system.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_client.css" />
	<link rel="stylesheet" type="text/css" href="theme/css/css_booking.css" />
	
	<!-- JS -->
	<script type="text/javascript" src="tool/jquery-2.1.4.min.js"></script>
	<script type="text/javascript" src="tool/jquery-ui-1.11.2.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="tool/canvasloader-min.js"></script>	
	
	<script type="text/javascript" src="js_library.js"></script>
	<script type="text/javascript" src="js_client.js"></script>
	<script type="text/javascript" src="js_apply.js"></script>
	
	<!-- PHP DATA -->
	<?php
	$page_info = isset($this->vars['server']['info']) ? $this->vars['server']['info'] : '';	
	$license = isset($this->vars['server']['data']['license'] ) ? $this->vars['server']['data']['license']  : '' ;
	?>
	
  </head>
  
  <body style='min-height:100%;'>
    <div class='system_body_area' style='height:100%;'>
	  <div class='page-container'>
	  <?php echo $license['page']; ?> 
	  </div>
	</div>  
	 
  </body>
  
  
  
</html>
