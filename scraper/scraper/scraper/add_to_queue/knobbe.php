<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.knobbe.com';
$spider_name = 'knobbe';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 14;
while ($i > 0) {

    $data = fetch($base_url.'/attorneys?page='.$i);
    $html = str_get_html($data);

    foreach($html->find('.attorney-grid .views-row') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = $base_url.$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('a', 0))
        {
            $name = trim($item->find('.co-chair-item__name a', 0)->plaintext);

            $email = '';
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'mailto:') !== false)
                {
                    $email = str_replace('mailto:', '', $link->href);
                }
            }

            $row = array(
                'url' => $base_url.$item->find('.co-chair-item__name a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.co-chair-item__position', 0)->plaintext),
                'location' => @trim($item->find('.co-chair-item__office a', 0)->plaintext),
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