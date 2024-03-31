<?php
include '../config.php';

$base_url = 'https://www.womblebonddickinson.com';
$spider_name = 'womblebonddickinson';
$firm_name = 'Womble Bond Dickinson LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=get0&url=https://www.womblebonddickinson.com/sites/default/files/api/uk-profiles.json'), 1);

foreach ($data as $row) {
    
    $values[] = $row;

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['path'], json_encode($row), 'pending', time(), NULL));

}

echo count($values);

?><br/>