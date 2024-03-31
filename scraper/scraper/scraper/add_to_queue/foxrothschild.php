<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.foxrothschild.com';
$spider_name = 'foxrothschild';
$firm_name = 'Fox Rothschild LLP';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$values = array();

$i = 1050;
while ($i > 0) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/people?search[post_type]=person&from='.$i));

    $html = str_get_html($data);

    foreach($html->find('a.sc-eCApnc.eyoygK') as $key => $item)
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

    $i = $i-50;
}

echo count($values);

?><br/>