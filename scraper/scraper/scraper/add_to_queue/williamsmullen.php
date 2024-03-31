<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.williamsmullen.com';
$spider_name = 'williamsmullen';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('a', 'z') as $key => $char) {

    $data = fetch($base_url.'/people/last-name/'.$char);
    $html = str_get_html($data);

    foreach($html->find('.people_card__card') as $item)
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

        $name = trim(preg_replace('/\s+/', ' ', $item->find('.card__name_position a', 0)->plaintext));

        $row = array(
            'url' => $base_url.$item->find('.card__name_position a', 0)->href,
            'name' => $name,
            'image' => $image,
            'title' => @trim($item->find('.card__name_position h3', 0)->plaintext),
            'location' => @trim($item->find('.card__office_name', 0)->plaintext),
            'phone' => @str_replace('tel:', '', trim($item->find('.card__office_phone a', 0)->href)),
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

echo count($values);

?><br/>