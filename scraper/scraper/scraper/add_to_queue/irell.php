<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.irell.com';
$spider_name = 'irell';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$data = file_get_contents('http://137.184.158.149:3000/?api=post&postData='.base64_encode('do_item_search=1').'&url='.$base_url.'/professionals-results?results#form-search-results');

$html = str_get_html($data);

$values = array();

foreach ($html->find('.results_list li') as $item) {

	if($item->find('img', 0))
	{
		$image = $base_url.'/'.$item->find('img', 0)->src;
		$url = $base_url.'/'.$item->find('a', 0)->href;
		$name = trim($item->find('.title', 0)->plaintext);
		$position = trim(str_replace('&nbsp;|&nbsp;', '', $item->find('.bioposition', 0)->plaintext));
		$office = trim($item->find('.office', 0)->plaintext);
		$phone = trim($item->find('.phone', 0)->plaintext);
		$email = @trim(str_replace('mailto:', '', html_entity_decode($item->find('.email a', 0)->href)));

		$values[] = array(
			'image' => $image,
			'url' => $url,
			'name' => $name,
			'position' => $position,
			'office' => $office,
			'phone' => $phone,
			'email' => $email
		);
	}

}

foreach ($values as $row) {

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

}

echo count($values);

?><br/>