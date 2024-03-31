<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.huntonak.com';
$spider_name = 'huntonak';
$firm_name = 'Hunton Andrews Kurth';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$i = 800;
while ($i>=0) {

    $data = json_decode(file_get_contents($base_url.'/_site/search?l=&json&v=attorney&f='.$i.'&s=100'), 1);

    foreach($data['hits']['ALL']['hits'] as $item)
    {
        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$item['resource']['link'],
            json_encode($item),
            'pending',
            time(),
            NULL
        ));
    }

    $i = $i-100;
}

?><br/>