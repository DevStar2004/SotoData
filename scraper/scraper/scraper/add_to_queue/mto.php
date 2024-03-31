<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mto.com';
$spider_name = 'mto';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get2&useProxy=1&url=https://www.mto.com/person-sitemap.xml');

$html = str_get_html($data);

foreach($html->find('#sitemap tbody tr') as $item)
{
    $row = array('url' => $item->find('a', 0)->href);
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

echo count($values);

?><br/>