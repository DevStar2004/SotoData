<?php
include '../config.php';

$base_url = 'https://www.thompsonhine.com';
$spider_name = 'thompsonhine';
$firm_name = 'Thompson Hine LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = @file_get_contents($base_url.'/poa_person-sitemap.xml');

$xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$data = json_decode($json, TRUE);

foreach ($data['url'] as $key => $value)
{
    if(strpos($value['loc'], '/professionals/')>-1)
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