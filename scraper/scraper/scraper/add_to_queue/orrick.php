<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.orrick.com';
$spider_name = 'orrick';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 109;
while ($i > 0) {

    $data = fetch_quick($base_url.'/People?t=personnel&l=&pg='.$i);
    $html = str_get_html($data);

    if(!$html)
    {
        continue;
    }

    foreach($html->find('.col-sm-6.col-md-4') as $item)
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

        if($item->find('.article-wrapper a', 0))
        {

            $name = strip_tags(explode('<small>', $item->find('.article-wrapper h3', 0)->innertext)[0]);

            $row = array(
                'url' => $base_url.$item->find('.article-wrapper a', 0)->href,
                'name' => @trim($name),
                'image' => $image,
                'title' => @trim(explode(', ', $item->find('h3 a small', 0)->plaintext)[0]),
                'location' => @trim($item->find('.article-wrapper p', 0)->plaintext),
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

    $i--;
}

echo count($values);

?><br/>