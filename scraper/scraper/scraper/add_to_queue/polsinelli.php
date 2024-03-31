<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.polsinelli.com';
$spider_name = 'polsinelli';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get&useProxy=1&url='.$base_url.'/sitemap.xml');

$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$data = json_decode($json, TRUE);

foreach ($data['body']['div'][0]['urlset']['url'] as $key => $value)
{

    if(strpos($value['loc'], 'antitrust/matters')>-1)
    {
        break;
    }

    if($key > 3543)
    {
        $row = array('url'=>$value['loc']);

        if(strpos($row['url'], 'publications') === false)
        {
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

}

echo count($values);

?><br/>