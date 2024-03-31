<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.wilsonelser.com';
$spider_name = 'wilsonelser';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();


$i = 1140;

while ($i > 0) {

	$data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://api2.greatjakes.com/attorneys?search[post_type]=person&from='.$i).'&headers=ewogICJYLVBhZ2UtU2l6ZSI6ICIyMDAwIiwKICAiWC1JbmRleCI6ICJ3aWxzb25fcHJvZHVjdGlvbiIKfQ==');

	$data = str_replace(array('<html><head></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">', '</pre></body></html>', '<html><head><meta name="color-scheme" content="light dark"></head><body><pre style="word-wrap: break-word; white-space: pre-wrap;">'), '', $data);

	$result = json_decode($data, 1)['content']['hits']['person']['hits'];

	foreach ($result as $value) {

		$row = $value['_source'];

		$row = array(
			'name' => $row['post_title'],
			'url' => $base_url.$row['slug'],
			'position' => $row['position'][0]['term'],
			'location' => $row['office_location'][0]['post_title'],
			'phone' => $row['office_location'][0]['office_phone'],
			'email' => $row['email']
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

	$i = $i-30;

}

echo count($values);

?><br/>