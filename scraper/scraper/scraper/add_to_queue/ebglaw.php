<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.ebglaw.com';
$spider_name = 'ebglaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1;
if($i == 1) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url=https://www.ebglaw.com/people/?page='.$i);

    $html = str_get_html($data);

    foreach($html->find('.results_list li') as $item)
    {

        if(!empty($item->find('div.photo img', 0)->src))
        {
            $image = @$item->find('div.photo img', 0)->src;
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

        if($item->find('div.title a', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('div.title a', 0)->plaintext));

            $row = array(
                'url' => $item->find('div.title a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('div.position', 0)->plaintext),
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

    $i++;
}

echo count($values);

?><br/>