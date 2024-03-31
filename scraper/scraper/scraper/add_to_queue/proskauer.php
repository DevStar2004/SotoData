<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.proskauer.com';
$spider_name = 'proskauer';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = fetch($base_url.'/professionals?general=no&prefix=&key_contact=&practice_group=&practices=&industries=&market_solutions=&offices=&languages=&titles=&educations=&schools=&degrees=&sort=&search=&search-mobile=&page=100');
$html = str_get_html($data);

foreach($html->find('.col.careers__block.no-pad') as $item)
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

    if($item->find('a', 0))
    {
        $name = trim($item->find('.careers__name', 0)->plaintext);

        $row = array(
            'url' => $base_url.$item->find('a', 0)->href,
            'name' => $name,
            'image' => $image,
            'title' => @trim($item->find('.careers__function', 0)->plaintext),
            'phone' => @trim($phone),
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