<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.curtis.com';
$spider_name = 'curtis';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('a', 'z') as $key => $char) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/our-people?refinementList%5Bletter%5D%5B0%5D='.$char));
    $html = str_get_html($data);

    foreach($html->find('.ais-InfiniteHits-list li') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        $phone = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'tel:') !== false)
            {
                $phone = str_replace('tel:', '', $link->href);
            }
        }

        if($item->find('.card-module__row-block-cont-hdr-link.card-module__row-block-cont-hdr-link--team span', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.card-module__row-block-cont-hdr-link.card-module__row-block-cont-hdr-link--team span', 0)->plaintext));

            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.card-module__row-block-cont-sub.card-module__row-block-cont-sub--team span', 0)->plaintext),
                'location' => @trim($item->find('.card-module__row-block-cont-sub.card-module__row-block-cont-sub--team span[style="white-space: nowrap;"]', 0)->plaintext),
                'phone' => @trim($item->find('.card-module__row-block-cont-txt.card-module__row-block-cont-txt--team', 0)->plaintext),
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