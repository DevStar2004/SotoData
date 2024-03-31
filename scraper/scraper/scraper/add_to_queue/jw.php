<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.jw.com';
$spider_name = 'jw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 51;

while ($i > 0) {

    $data = fetch($base_url.'/people/page/'.$i.'/');
    $html = str_get_html($data);

    foreach($html->find('.site-main .people-content') as $item)
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
            $name = trim($item->find('.people-name h2', 0)->plaintext);

            $email = '';
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'mailto:') !== false)
                {
                    $email = str_replace('mailto:', '', $link->href);
                }
            }

            $row = array(
                'url' => $item->find('.people-name a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.people-affiliate', 0)->plaintext),
                'location' => @trim($item->find('.people-contact-info .people-speciality', 0)->plaintext),
                'phone' => @trim($item->find('.people-contact-info .bio-phone', 0)->plaintext),
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

    $i--;
}

echo count($values);

?><br/>