<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.wnj.com';
$spider_name = 'wnj';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $key => $char) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($base_url.'/people?alpha='.$char));
    $html = str_get_html($data);

    foreach($html->find('.card') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        $phone = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'tel:') !== false)
            {
                $phone = str_replace('tel:', '', $link->href);
            }
        }

        if($item->find('.mb-0.card-title.h5 a', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.mb-0.card-title.h5 a', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => @str_replace('//', '', trim($phone)),
                'email' => @trim($item->find('a.email', 0)->plaintext),
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