<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge" >
	<?php 
	$process_result = isset($this->vars['server']) ? $this->vars['server'] : 'array()';  
	?>
  </head>
  <body>
    <script>
	  parent.process_referupload('<?php echo json_encode($process_result);?>');
	</script>
  </body>
</html>