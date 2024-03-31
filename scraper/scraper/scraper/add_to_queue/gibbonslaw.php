<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.gibbonslaw.com';
$spider_name = 'gibbonslaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 22; $i++) {

    $data = fetch($base_url.'/api/professionals?page='.$i);
    $result = json_decode($data, 1)['results'];

    foreach($result as $value)
    {
        $values[] = $value;
        $row = $value;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$row['link']['url'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));
        
    }

}

echo count($values);

?><br/>