<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.fr.com/';
$spider_name = 'fr';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 40;

while ($i > 0) {
    
    $data = fetch($base_url.'/our-people/page/'.$i.'/');
    $html = str_get_html($data);

    foreach($html->find('.card-bio') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('.card-bio-title.h5', 0))
        {
            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => trim($item->find('.card-bio-title.h5', 0)->plaintext),
                'image' => $image,
                'title' => @$item->find('.meta dt', 0)->plaintext,
                'location' => @$item->find('.meta dd', 1)->plaintext,
                'email' => @$item->find('.meta a', 0)->plaintext,
                'phone' => @$item->find('.meta a', 1)->plaintext
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