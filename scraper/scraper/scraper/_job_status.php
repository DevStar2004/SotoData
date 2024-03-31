<?php
include 'config.php';
include 'simple_html_dom.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body style="padding: 20px;">
	<h2 style="color: #555;">Firms with no jobs</h2>
	<?php
	$q = $pdo->prepare('SELECT * FROM `job_status` ORDER BY `company_url` ASC');
	$q->execute(array());
	foreach ($q as $row) {
		$q_ = $pdo->prepare('SELECT * FROM `jobs` WHERE `image`=? LIMIT 1');
		$q_->execute(array($row['company_image']));
		if($q_->rowcount()<1)
		{
			echo '<a href="'.$row['company_url'].'" style="color: #777; display: block; margin-bottom: 10px;" target="_blank">'.$row['company_url'].'</a>';
		}
	}

	?>
</body>
</html>