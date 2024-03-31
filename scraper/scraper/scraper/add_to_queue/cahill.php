<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.cahill.com';
$spider_name = 'cahill';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = fetch($base_url.'/professionals/search-results?showProfessionals=1&s_lastname='.$char);
    $html = str_get_html($data);

    foreach($html->find('.item-row') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = $base_url.@$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $item->find('.contact-info a', 0)->plaintext,
            'image' => $image,
            'title' => $item->find('span.position', 0)->plaintext,
            'email' => @$item->find('.contact-info a', 2)->plaintext,
            'phone' => @$item->find('a.mobilePhone', 0)->plaintext
        );
        $values[] = $row;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$row['url'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));

    }
    
}

echo count($values);

?><br/>