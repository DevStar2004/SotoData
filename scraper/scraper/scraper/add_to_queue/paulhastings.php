<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.paulhastings.com';
$spider_name = 'paulhastings';
$firm_name = 'Paul Hastings, LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$data = json_decode(fetch_quick($base_url.'/page-data/professionals/page-data.json'), 1);

foreach($data['result']['data']['professionals']['nodes'] as $row)
{
    $row['url'] = $base_url.'/professionals/'.$row['slug'];
    $values[] = $row;

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

}

echo count($values);

?><br/>