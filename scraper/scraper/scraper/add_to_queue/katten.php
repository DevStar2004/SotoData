<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://katten.com';
$spider_name = 'katten';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = fetch($base_url.'/Bio/Search/?');
$html = str_get_html($data);

foreach($html->find('.bio-result__container') as $item)
{

    if(!empty($item->find('img', 1)->src))
    {
        $image = @$item->find('img', 1)->src;
    }
    else
    {
        $image = '';
    }

    if($item->find('a', 0))
    {
        $name = trim($item->find('.bio-result__name a', 0)->plaintext);

        $email = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'mailto:') !== false)
            {
                $email = str_replace('mailto:', '', $link->href);
            }
        }

        $row = array(
            'url' => $base_url.$item->find('.bio-result__name a', 0)->href,
            'name' => $name,
            'image' => $image,
            'title' => @trim($item->find('.bio-result__title', 0)->plaintext),
            'location' => @trim($item->find('.bio-result__location a', 0)->plaintext),
            'phone' => @trim(html_entity_decode($item->find('.bio-result__phone', 0)->plaintext)),
            'email' => $email
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

echo count($values);

?><br/>