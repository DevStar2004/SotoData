<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.loeb.com';
$spider_name = 'loeb';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {

    $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&element=btn-load-more&url=https%3A%2F%2Fwww.loeb.com%2Fen%2Fpeople%23f%3A%40lastnamestartswith%3D'.$char);
    $html = @str_get_html($data);

    if(!$html)
    {

        $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&element=btn-load-more&url=https%3A%2F%2Fwww.loeb.com%2Fen%2Fpeople%23f%3A%40lastnamestartswith%3D'.$char);
        $html = @str_get_html($data);

        if(!$html)
        {
            $data = @file_get_contents('http://137.184.158.149:3000/?api=get2&element=btn-load-more&url=https%3A%2F%2Fwww.loeb.com%2Fen%2Fpeople%23f%3A%40lastnamestartswith%3D'.$char);
            $html = @str_get_html($data);
        }

    }

    if(!$html)
    {
        continue;
    }

    foreach($html->find('.CoveoResult') as $item)
    {

        if($item->find('a', 0))
        {

            $name = trim($item->find('.contact-card__name a', 0)->plaintext);

            if(!empty($item->find('img', 0)->src))
            {
                $image = $base_url.$item->find('img', 0)->src;
            }
            else
            {
                $image = '';
            }

            $email = '';
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'mailto:') !== false)
                {
                    $email = str_replace('mailto:', '', $link->href);
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

            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => $phone,
                'email' => $email,
                'title' => trim(str_replace(',', '', $item->find('.contact-card__position', 0)->plaintext)),
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