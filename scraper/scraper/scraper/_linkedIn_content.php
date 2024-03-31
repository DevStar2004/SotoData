<?php
include 'config.php';
include 'simple_html_dom.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$f = explode("\n", file_get_contents('COOKIES.txt'));

$q = $pdo->prepare('SELECT * FROM `linkedIn` WHERE `content`=\'\' ORDER BY RAND() LIMIT 50');
$q->execute();

foreach($q as $row)
{

	$cookie = $f[array_rand($f)];

	echo 'http://137.184.158.149:3000/?api=linkedIn&url='.$row['url'].'&cookie='.$cookie;

	$content = array();

	$d = file_get_contents('http://137.184.158.149:3000/?api=linkedIn&url='.$row['url'].'&cookie='.$cookie);
	$html = str_get_html($d);

	foreach($html->find('section.artdeco-card') as $item)
	{

		if($item->find('h2', 0))
		{
			$title = trim($item->find('h2', 0)->plaintext);

			if(strpos($title, 'Experience') !== false && strpos($title, 'Volunteer') === false)
			{
				$content['experience'] = $item->innertext;
			}

			if(strpos($title, 'Education') !== false)
			{
				$content['education'] = $item->innertext;
			}

		}
	}

	if(!empty($content))
	{
		$json = json_encode($content);

		$q = $pdo->prepare('UPDATE `linkedIn` SET `content`=? WHERE `id`=? LIMIT 1');
		$q->execute(array($json, $row['id']));
		sleep(2);
	}

}

?>