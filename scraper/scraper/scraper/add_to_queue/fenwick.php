<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.fenwick.com';
$spider_name = 'fenwick';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 46;
while ($i > 0) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($base_url.'/people?prod_main_alpha_sort%5Bpage%5D='.$i));
    $html = str_get_html($data);

    foreach($html->find('.ais-Hits-item.cell') as $item)
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

        if($item->find('.person__name', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.person__name', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.person__title', 0)->plaintext),
                'location' => @trim($item->find('.person__city', 0)->plaintext),
                'phone' => @trim($item->find('.phone-number', 0)->{'data-phone'}),
                'email' => @trim($item->find('.attorney-email', 0)->{'data-email'}),
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

    $i--;
}

echo count($values);

?><br/>