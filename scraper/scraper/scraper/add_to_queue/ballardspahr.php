<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.ballardspahr.com';
$spider_name = 'ballardspahr';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

foreach(range('A', 'Z') as $char) {

    for ($i=0; $i < 5; $i++) {

        $data = file_get_contents($base_url.'/sitecore/api/people/search?lang=en&sc_apikey=%7B8BEE2997-A9B1-4874-A4C3-7EBA04C493EC%7D&page='.$i.'&Alpha='.strtolower($char));
        $rows = @json_decode($data, 1)['Results'];
        if(@count($rows)>0)
        {
            foreach($rows as $value)
            {
                $values[] = $value;
                $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
                $q->execute(array($spider_name, $base_url.$value['url'], json_encode($value), 'pending', time(), NULL));
            }
        }
        else
        {
            break;
        }

    }
}

echo count($values);

?><br/>