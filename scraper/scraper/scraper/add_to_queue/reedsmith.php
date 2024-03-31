<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.reedsmith.com';
$spider_name = 'reedsmith';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$rows = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode('{"Letter":"","Name":"","Keyword":"","Take":2000,"Skip":0,"Location":"","Capability":"","Topic":"","Admission":"","Diversity":"","GlobalSolution":"","Position":""}').'&url='.urlencode($base_url.'/api/professionals/search')), 1)['Results'];

foreach ($rows as $value) {
    
    $values[] = $value;
    $row = $value;

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['Url'], json_encode($row), 'pending', time(), NULL));
    
}

echo count($values);

?><br/>