<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.fragomen.com';
$spider_name = 'fragomen';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 10; $i++) {
    $data = json_decode(fetch($base_url.'/_site/search?f=0&v=attorney&s=10000&page='.$i), 1)['hits']['ALL']['hits'];
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
}

echo count($values);

?><br/>