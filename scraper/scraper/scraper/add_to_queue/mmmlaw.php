<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mmmlaw.com';
$spider_name = 'mmmlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('a', 'z') as $char) {
	
	$data = fetch($base_url.'/people/?letter='.$char.'#results');
	$html = str_get_html($data);

	foreach($html->find('.people-list__person') as $item)
	{
		$row = array(
			'url' => $base_url.$item->find('a', 0)->href,
			'image' => @str_replace('&w=36&h=41&q=95', '', $base_url.$item->find('img', 0)->src),
			'name' => preg_replace('/\s+/', ' ', $item->find('.people-list__name a', 0)->plaintext),
			'position' => trim($item->find('div.people-list__title', 0)->plaintext),
		);

		$values[] = $row;

		$q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
		$q->execute(array(
		    $spider_name,
		    $row['url'],
		    json_encode($row),
		    'pending',
		    time(),
		    NULL
		));

	}

}

echo count($values);

?><br/>