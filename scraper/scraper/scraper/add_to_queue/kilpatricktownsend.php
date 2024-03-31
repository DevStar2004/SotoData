<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://ktslaw.com/';
$spider_name = 'kilpatricktownsend';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData=eyJzZWFyY2hUeXBlIjoiQmlvIiwiY3VycmVudENvdW50IjowLCJnZXRDb3VudCI6MTAwMDAsInNlYXJjaFRlcm0iOiIiLCJsZXR0ZXIiOiIiLCJmYWNldFN0ciI6IiJ9&url=https://ktslaw.com/api/sitecore/Solr/GetSearchResults'), 1);

$html = str_get_html($data);

foreach($html->find('.result-cell') as $item)
{
    $row = array(
        'name' => $item->find('.bio-heading a', 0)->plaintext,
        'email' => str_replace('mailto:', '', $item->find('a.mailIcon', 0)->{'attr-email'}),
        'phone' => trim($item->find('.contact a', 1)->plaintext),
        'url' => $base_url.$item->find('a', 0)->href,
        'image' => $base_url.$item->find('img', 0)->src,
        'position' => $item->find('p.profile', 0)->plaintext,
    );
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