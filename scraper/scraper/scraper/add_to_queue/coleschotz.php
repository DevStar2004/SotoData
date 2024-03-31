<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.coleschotz.com';
$spider_name = 'coleschotz';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 19; $i++) {

	$data = file_get_contents('http://137.184.158.149:3000/?api=post&postData='.base64_encode('page='.$i.'&search=&practice=&industry=&location=&last_name=').'&url='.$base_url.'/wp-json/coleschotz/v1/filter-professionals');

	$html = str_get_html(json_decode($data, 1)['data']);

	if(strpos($html, 'professionals-card') !== false)
	{
		foreach($html->find('.professionals-card') as $item)
		{
			$values[] = array(
				'image' => $item->find('img', 0)->src,
				'url' => $item->find('a', 0)->href,
				'name' => trim(str_replace("\n", ' ', $item->find('h3.professionals-card__heading', 0)->plaintext)),
				'position' => $item->find('span.professionals-card__member-or-associate', 0)->plaintext,
				'phone' => trim(str_replace('Office', '', $item->find('.professionals-card__contact-item', 0)->plaintext)),
				'email' => trim(str_replace('Email', '', $item->find('.professionals-card__contact-item', 1)->plaintext)),
			);
		}
	}

}

foreach ($values as $row) {

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

}

echo count($values);

?><br/>