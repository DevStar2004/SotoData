<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.beneschlaw.com';
$spider_name = 'beneschlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1;

while ($i < 5) {

    $data = fetch($base_url.'/_site/search?q=&f=0&v=attorney&s=100&page='.$i);

    $data = json_decode($data, 1)['hits']['ALL']['hits'];

    foreach ($data as $row) {

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$row['url'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));

        $values[] = $row;

    }

    $i++;
}

echo count($values);

?><br/>