<?php
include '../config.php';

$base_url = 'https://www.stoel.com';
$spider_name = 'stoel';
$firm_name = 'Stoel Rives LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


foreach(range('A', 'Z') as $char)
{
    $data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode('{
        "CurrentPage": 1,
        "PageCount": 10000,
        "Letter": "'.$char.'"
    }').'&url=https://www.stoel.com/siteapi/attorney/search'), 1);

    foreach($data['Items'] as $row)
    {
        $values[] = $row;
        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.$row['URL'], json_encode($row), 'pending', time(), NULL));
    }

}

echo count($values);

?><br/>