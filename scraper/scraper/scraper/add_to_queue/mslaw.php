<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mslaw.com';
$spider_name = 'mslaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 150;
while ($i > 0) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.mslaw.com/people?search[post_type]=person&from='.$i));
    $html = str_get_html($data);

    foreach($html->find('a') as $key => $item)
    {
        if(strpos($item->class, 'dVpuFn') !== false)
        {
            $row = array('url' => $base_url.$item->href);
            $values[] = $row;
            
            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array(
                $spider_name,
                $row['url'],
                json_encode($row),
                'pending',
                time(),
                NULL
            ));
        }
    }

    $i = $i-30;
}

echo count($values);

?><br/>