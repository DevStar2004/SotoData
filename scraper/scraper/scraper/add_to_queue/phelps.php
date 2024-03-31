<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.phelps.com';
$spider_name = 'phelps';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$i = 360;
while ($i > 0) {
    
    $data = fetch($base_url.'/_site/search?f='.$i.'&v=attorney');
    $rows = json_decode($data, 1)['hits']['ALL']['hits'];

    foreach ($rows as $value) {
        $values[] = $value;
        $row = $value;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
        
    }

    $i = $i-20;

}

echo count($values);

?><br/>