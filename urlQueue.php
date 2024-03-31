<?php
include 'config.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

$link = file_get_contents('__jobTemp.txt');

$q = $pdo->prepare('SELECT * FROM `jobs` WHERE `content`=\'\' ORDER BY `time` DESC LIMIT 1');
$q->execute(array());
$job = $q->fetch(PDO::FETCH_ASSOC);
$url = 'https://www.linkedin.com/jobs/view/'.$job['job_id'];

if(strpos($url, 'linkedin.com/jobs/view') !== false && empty($link))
{
	file_put_contents('__jobTemp.txt', $url);
}

$rand = rand(000000, 999999);
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<?php
	echo $rand;
	?>
	<script type="text/javascript">
		setTimeout(function(){
			window.location.reload();
		}, <?php echo (rand(7, 10)*1000) ?>);
	</script>
</body>
</html>