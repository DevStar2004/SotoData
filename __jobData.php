<?php
include 'config.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

if(!empty($_POST['content']))
{

	$url = substr($_POST['url'], 0, -3);

	if(strpos($_POST['url'], 'linkedin.com/jobs/view') !== false)
	{
		$ex = explode('/', $_POST['url']);
		$job_id = $ex[5];
		$q = $pdo->prepare('UPDATE `jobs` SET `content`=? WHERE `job_id`=? LIMIT 5');
		$q->execute(array($_POST['content'], $job_id));
	}


}
?>