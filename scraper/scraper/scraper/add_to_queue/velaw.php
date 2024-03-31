<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.velaw.com';
$spider_name = 'velaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 59;
while ($i > 0) {

    $data = json_decode(fetch($base_url.'/api/people?page='.$i), 1);
    foreach($data['results'] as $row)
    {
        $values[] = $row;
        
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

    $i--;

}

echo count($values);

?><br/>