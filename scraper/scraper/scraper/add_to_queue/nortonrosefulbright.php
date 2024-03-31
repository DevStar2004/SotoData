<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.nortonrosefulbright.com';
$spider_name = 'nortonrosefulbright';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 900;

while ($i > 0) {
    
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.nortonrosefulbright.com/en/people#first='.$i.'&sort=%40cvpositionpriority%20descending&numberOfResults=100'));
    $html = str_get_html($data);

    foreach($html->find('.coveo-result-row') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('.coveo-title a', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.coveo-title a', 0)->plaintext));

            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.coveo-result-row .coveo-result-cell p', 0)->plaintext),
                'location' => @trim($item->find('.coveo-result-row .coveo-result-cell p', 1)->plaintext),
                'email' => @trim(str_replace('mailto:', '', $item->find('.nrf-email-link', 0)->{'aria-label'})),
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

    }

    $i = $i-100;

}

echo count($values);

?><br/>