<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title><?php echo HTML::chars($title) ?></title>
		<style type="text/css">
		* { margin: 0; padding: 0; }
		body { margin: 20px; }
  		ul { list-style: none; float: left; }
		li { padding: 4px 10px; margin: 0 0 10px;
			color: #333; border: 1px solid #ccc; }
		code { font-weight: bold; color: #220B8E;
			padding: 1px; background: #E9E8FF; }
		.notice { background: #FFFFB3; }
		.error { background: #FFB3B3; }
		.success { background: #B3FFB4; }
		</style>
	</head>
	<body>
		<?php echo MSG::instance()->render() ?>
	</body>
</html>