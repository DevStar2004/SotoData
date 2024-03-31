<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://lewisbrisbois.com';
$spider_name = 'lewisbrisbois';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1550;
while ($i > 0) {

    $data = fetch($base_url.'/attorneys/search-results/eyJyZXN1bHRfcGFnZSI6ImF0dG9ybmV5c1wvc2VhcmNoLXJlc3VsdHMiLCJjb2xsZWN0aW9uIjoiMSJ9/P'.$i);
    $html = str_get_html($data);

    foreach($html->find('#main li') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = $base_url.$item->find('img', 0)->src;
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
                $email = html_entity_decode(str_replace('mailto:', '', $link->href));
            }
        }

        if($item->find('a', 0))
        {
            $name = trim($item->find('h6', 0)->plaintext);

            $email = '';
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'mailto:') !== false)
                {
                    $email = str_replace('mailto:', '', $link->href);
                }
            }

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => @trim($item->find('.phone', 0)->plaintext),
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

    $i = $i-50;

}

echo count($values);

?><br/>