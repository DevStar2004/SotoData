<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.debevoise.com';
$spider_name = 'debevoise';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&element=search&url='.$base_url.'/professionals/?letter='.$char);
    $html = str_get_html($data);

    foreach($html->find('.listing__item.prof__item') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('a', 0))
        {
            $name = trim($item->find('a', 0)->plaintext);

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.prof__title', 0)->plaintext),
                'phone' => @trim($item->find('.prof-contact .phone-item', 0)->plaintext),
                'email' => @trim($item->find('.prof-contact a', 0)->plaintext),
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