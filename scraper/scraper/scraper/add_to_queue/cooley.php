<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.cooley.com';
$spider_name = 'cooley';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents($base_url.'/sitemap.xml');

$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$data = json_decode($json, TRUE);

foreach ($data['url'] as $key => $value)
{
    if(strpos($value['loc'], '/people/')>-1)
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
}

echo count($values);

?><br/>