<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bclplaw.com';
$spider_name = 'bclplaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 14; $i++) {
    $data = json_decode(fetch($base_url.'/_site/search?l=&f=0&s=10000&space=1019805&v=attorney&page='.$i), 1)['hits']['ALL']['hits'];
    
    foreach($data as $value)
    {

        $row = $value;
        $values[] = $value;

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
}

echo count($values);

?><br/>