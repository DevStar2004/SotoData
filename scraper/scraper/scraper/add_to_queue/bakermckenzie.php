<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bakermckenzie.com';
$spider_name = 'bakermckenzie';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{


    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url=https://www.bakermckenzie.com/en/people/?letter='.$char.'&skip=1000&sort=2&scroll=3300');

    $json = str_replace('\"', '"', get_string_between($data, 'initialJsonData = "', '";'));

    $result = json_decode($json, 1);

    foreach($result['GridData'] as $value)
    {
        $row = $value;
        $values[] = $value;
        
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

}

echo count($values);

?><br/>