<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.lewisroca.com';
$spider_name = 'lewisroca';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 21;
while ($i > 0) {

    $data = fetch($base_url.'/people,page'.$i.'#form-search-results');
    $html = str_get_html($data);

    foreach($html->find('.results_list li') as $item)
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

        if($item->find('.title', 0))
        {

            $name = trim($item->find('.title', 0)->plaintext);

            $row = array(
                'url' => $base_url.'/'.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.position', 0)->plaintext),
                'location' => @trim($item->find('.office a', 0)->plaintext),
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