<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?php echo $page_title; ?></title>

<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="lib/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="js/functions.js"></script>
<link rel="stylesheet" href="css/styles.css" type="text/css" />

<?php 
if(isset($include_css_files) && is_array($include_css_files)){
foreach ($include_css_files as $css_file){ 
?>
<link rel="stylesheet" href="<?php echo $css_file; ?>" type="text/css" />
<?php }} ?>

</head>
<body>
	<div id="transtable_inbody">
		<?php echo $page_content; ?>
	</div>
</body>
</html>
