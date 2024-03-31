<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.kelleydrye.com';
$spider_name = 'kelleydrye';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1;

while ($i < 27) {
	
	$data = fetch('https://www.kelleydrye.com/people?page='.$i);
	$html = str_get_html($data);

	foreach ($html->find('.relative.space-y-2.type-body-sm') as $item) {
		$values[] = array(
			'name' => $item->find('a', 1)->plaintext,
			'url'  => $item->find('a', 1)->href
		);
	}

	$i++;
}

foreach ($values as $row) {

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

}

echo count($values);

?><br/>