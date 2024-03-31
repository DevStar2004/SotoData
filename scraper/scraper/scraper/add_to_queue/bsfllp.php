<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bsfllp.com';
$spider_name = 'bsfllp';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 170;
while ($i >= 0) {
    
    $data = fetch($base_url.'/_site/search?v=attorney&f='.$i.'&s=10&json');

    $data = json_decode($data, 1)['hits']['ALL']['hits'];

    foreach($data as $row)
    {
        $values[] = $row;
        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
    }

    $i = $i-10;

}

echo count($values);

?><br/>