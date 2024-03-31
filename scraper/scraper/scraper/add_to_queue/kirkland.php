<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.kirkland.com';
$spider_name = 'kirkland';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {
    
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/lawyers?letter='.$char.'&page=20'));
    $html = str_get_html($data);

    foreach($html->find('.person-result') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        $email = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'mailto:') !== false || strpos($link->href, '&#109;&#97;&#105;') !== false)
            {
                $email = str_replace('mailto:', '', html_entity_decode($link->href));
            }
        }

        $phone = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'tel:') !== false)
            {
                $phone = str_replace('tel:', '', $link->href);
            }
        }

        if($item->find('.person-result__name a', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.person-result__name a', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('.person-result__name a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.person-result__level', 0)->plaintext),
                'location' => @trim($item->find('.person-result__office-title a', 0)->plaintext),
                'phone' => @str_replace('//', '', trim($phone)),
                'email' => @trim($email),
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