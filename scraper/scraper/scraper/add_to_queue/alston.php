<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.alston.com';
$spider_name = 'alston';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData=eyJMYW5ndWFnZSI6ImVuIiwiU2tpcCI6MCwiVGFrZSI6MjAwMCwiSW5pdGlhbFBhZ2VTaXplIjoyMDAwfQ==&url=https://www.alston.com/api/sitecore/professionals/search'), 1);

foreach($data['GridData'] as $value)
{
	$values[] = $value;
    $row = $value;
    
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array(
        $spider_name,
        $base_url.$row['Url'],
        json_encode($row),
        'pending',
        time(),
        NULL
    ));

}

echo count($values);

?><br/>