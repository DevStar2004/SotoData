<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.dickinson-wright.com';
$spider_name = 'dickinson-wright';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.$base_url.'/our-people?letter='.$char.'&page=1&element=search-results__load-more');
    $html = str_get_html($data);

    foreach($html->find('.person-card') as $item)
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
            'url' => $item->find('a.person-card__name', 0)->href,
            'name' => $item->find('a.person-card__name', 0)->plaintext,
            'image' => $image,
            'title' => $item->find('span.person-card__level', 0)->plaintext,
            'email' => @$item->find('a.person-card__email', 0)->plaintext,
            'phone' => @$item->find('a.person-card__phone', 0)->plaintext
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