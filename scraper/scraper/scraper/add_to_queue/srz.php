<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.srz.com';
$spider_name = 'srz';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 350;
while ($i>0) {

    $data = json_decode(fetch($base_url.'/_site/search?v=attorney&f='.$i.'&s=50&json'), 1)['hits']['ALL']['hits'];
    foreach($data as $value)
    {
        $values[] = $value;
        $row = $value;
        
        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$row['url'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));

    }

    $i = $i-50;
}

echo count($values);

?><br/>