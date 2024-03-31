<?php
include 'config.php';

//update people LinkedIn direct from linkedIn, use a flat file with increment for offset

$q = $pdo->prepare('SELECT `names`, `firmName` FROM `people` WHERE `LinkedIn`=\'\' ORDER BY `id` ASC LIMIT 100');
$q->execute();
foreach ($q as $row) {

	var_dump($row['names']);

	$q = $pdo->prepare('SELECT `name`, `url` FROM `linkedIn` WHERE `name`=? AND `firmName`=? LIMIT 1');
	$q->execute(array($row['names'], $row['firmName']));
	$linkedIn = $q->fetch(PDO::FETCH_ASSOC);
	if($linkedIn)
	{
		$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `names`=? AND `firmName`=? LIMIT 1');
		$q->execute(array($linkedIn['url'], $row['names'], $row['firmName']));
	}
	else
	{
		$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `names`=? AND `firmName`=? LIMIT 1');
		$q->execute(array('not_found', $row['names'], $row['firmName']));
	}

	var_dump($linkedIn);

}

echo '<script>window.location.reload();</script>';