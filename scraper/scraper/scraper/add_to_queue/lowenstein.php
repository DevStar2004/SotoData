<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.lowenstein.com';
$spider_name = 'lowenstein';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 4;
while ($i > 0) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($base_url.'/people/people-directory?letter=&practice=&location=&position=&page='.$i.'&pageSize=1000&lawSchool=&college=&undergraduate=&subTotal=8'));

    $html = str_get_html($data);

    foreach($html->find('.item') as $item)
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

        if($item->find('.profile-main.peopledir1', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.profile-main.peopledir1 h2 a', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('.profile-main.peopledir1 a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.profile-main.peopledir1 .sub-title', 0)->plaintext),
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