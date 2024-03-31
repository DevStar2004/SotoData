<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.daypitney.com';
$spider_name = 'daypitney';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$values = array();

$data = fetch_quick($base_url.'/professionals-sitemap.xml');

$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$data = json_decode($json, TRUE);

foreach ($data['url'] as $key => $value)
{
    $row = array('url'=>$value['loc']);
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