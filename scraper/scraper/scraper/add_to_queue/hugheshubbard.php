<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.hugheshubbard.com';
$spider_name = 'hugheshubbard';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/attorneys?starts-with[]='.$char.'&language=en_us'));
    $html = str_get_html($data);

    foreach($html->find('.people-tile__container') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->href)
        {
            $name = trim($item->href);

            $row = array(
                'url' => $item->href,
                'name' => trim($item->find('.people-tile__name', 0)->plaintext),
                'image' => $image,
                'title' => @trim($item->find('.people-tile__titles', 0)->plaintext),
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

}

echo count($values);

?><br/>