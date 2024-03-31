<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.sidley.com';
$spider_name = 'sidley';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = fetch($base_url.'/en/global/people/?skip=10000&currentviewid=83e3dcaa-1264-4226-8ee6-380c20e95bea&reload=false&scroll=5310.28564453125');
$json = str_replace('\"', '"', get_string_between($data, 'initialJsonData = "', '";'));

$result = json_decode($json, 1);

foreach($result['GridData'] as $value)
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