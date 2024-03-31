<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.skadden.com';
$spider_name = 'skadden';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1890;

while ($i > 0) {

    $data = fetch($base_url.'/api/sitecore/professionals/search?skip='.$i.'&letter=&hassearched=true');
    $rows = json_decode($data, 1)['SearchResults'];

    foreach ($rows as $value) {
        
        $values[] = $value;
        $row = $value;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.$row['Url'], json_encode($row), 'pending', time(), NULL));
        
    }

    $i = $i-15;
}

echo count($values);

?><br/>