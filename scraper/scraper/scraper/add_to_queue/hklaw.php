<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.hklaw.com';
$spider_name = 'hklaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i <= 100; $i++) {

	$data = file_get_contents('http://137.184.158.149:3000/?api=post&postData='.base64_encode('page='.$i).'&url='.$base_url.'/api/ProfessionalsApi/Lawyers?page='.$i);

	$results = json_decode($data, 1)['results'];
	foreach ($results as $key => $value) {
		$values[] = $value;
	}

}

foreach ($values as $row) {

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array(
        $spider_name,
        $base_url.$row['url'],
        json_encode($row),
        'pending',
        time(),
        NULL
    ));

}

echo count($values);

?><br/>