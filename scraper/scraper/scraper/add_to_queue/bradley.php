<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bradley.com';
$spider_name = 'bradley';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = fetch($base_url.'/people/?letter='.$char.'&skip=100&reload=false&');
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
    
}

echo count($values);

?><br/>