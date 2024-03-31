<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.nelsonmullins.com';
$spider_name = 'nelsonmullins';
$firm_name = 'Nelson Mullins Riley & Scarborough LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$data = file_get_contents('http://137.184.158.149:3000/?api=get3&url='.urlencode($base_url.'/professionals?limit=all&sort=&thisView=frontend.people.index&thisModel=Page&thisId=13&thisView=frontend.people.index&thisModel=Page&thisId=13'));
$html = str_get_html($data);

foreach($html->find('.people-search-results-row') as $item)
{

    $row = array(
        'name' => $item->find('.search-result-name a', 0)->plaintext,
        'url' => $base_url.$item->find('.search-result-name a', 0)->href,
        'image' => $item->find('img', 0)->src,
        'position' => $item->find('.search-result-name p', 0)->plaintext,
        'location' => trim($item->find('.mobile-result-location', 0)->plaintext)
    );

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));

    $values[] = $row;
}

echo count($values);

?><br/>