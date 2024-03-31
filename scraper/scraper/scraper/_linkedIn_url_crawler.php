<?php
include 'config.php';
include 'simple_html_dom.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

die();

$cookie = 'AQEDAR4mbfEFyKIEAAABi_iwQaIAAAGMHLzFolYAzl0pVJIo2nk96KWjjO1Ww8fPrE0HVOVA4vRyA5rdTh3OodGSf8zJ3YEOFqrMMssn-enWD5xkyd7iUzp3t5LGk-FRPvNSYSl_JP8Z9Ak5o_GqzjAO';

$q = $pdo->prepare('SELECT * FROM `people` WHERE `LinkedIn`=\'\' AND `LinkedIn`<>\'none\' ORDER BY RAND() LIMIT 40');
$q->execute();

$i = 0;

foreach ($q as $row) {

	$q = $pdo->prepare('SELECT * FROM `linkedIn` WHERE `name`=? LIMIT 1');
	$q->execute(array($row['names']));

	if($q->rowcount()>0)
	{
		$linkedIn = $q->fetch(PDO::FETCH_ASSOC);
		$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `id`=? LIMIT 1');
		$q->execute(array($linkedIn['url'], $row['id']));
		continue;
	}

	$i++;

	$names = json_decode($row['names'], 1);

	foreach ($names as $key => $value) {
		$names[$key] = trim(preg_replace('/\t+/', '', $value));
		if(strpos($value, '.') !== false || empty($value))
		{
			unset($names[$key]);
		}
	}

	$name = $names[0].' '.end($names);

	$firmName = preg_replace('/\s+/', ' ', str_replace(array('and', '&amp;'), '&', $row['firmName']));

	$data = file_get_contents('http://137.184.158.149:3000/?api=linkedIn_search&keyword='.urlencode($name.' '.$firmName).'&cookie='.$cookie);

	$html = str_get_html($data);

	foreach($html->find('a') as $item)
	{
		if(strpos($item->href, 'miniProfile') !== false)
		{
			$linkedIn = explode('?miniProfileUrn', $item->href)[0];
			break;
		}
	}

	if(isset($linkedIn) && strpos($linkedIn, 'company') === false)
	{

		$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `id`=? LIMIT 1');
		$q->execute(array($linkedIn, $row['id']));

		$q = $pdo->prepare('INSERT INTO `linkedIn` VALUES (?,?,?,?,?)');
		$q->execute(array($row['names'], $row['firmName'], $linkedIn, '', NULL));

	}
	else
	{
		$q = $pdo->prepare('UPDATE `people` SET `LinkedIn`=? WHERE `id`=? LIMIT 1');
		$q->execute(array('none', $row['id']));
	}

	unset($linkedIn);

	sleep(rand(1, 3));

}

?>