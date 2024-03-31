<?php
include '../config.php';

$base_url = 'https://www.taftlaw.com';
$spider_name = 'taftlaw';
$firm_name = 'Taft Stettinius & Hollister LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$data = json_decode(fetch_quick($base_url.'/api/query/people?letter=&perpage=99999&page=1&peopletype=all'), 1);
foreach($data['results'] as $row)
{
    $values[] = $row;
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>