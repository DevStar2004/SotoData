<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.swlaw.com';
$spider_name = 'swlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = fetch_quick($base_url.'/people');
$html = str_get_html($data);

foreach($html->find('.table-responsive tr') as $item)
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

    if($item->find('a', 0))
    {
        $name = trim($item->find('.stretched-link span', 0)->plaintext);

        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $name,
            'image' => $image,
            'title' => @trim($item->find('td', 1)->plaintext),
            'location' => @trim($item->find('td', 2)->plaintext),
            'phone' => @trim($item->find('td', 3)->plaintext),
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