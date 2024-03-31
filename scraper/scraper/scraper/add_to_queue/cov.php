<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.cov.com';
$spider_name = 'cov';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/en/professionals#sort=%40titlesort%20descending&numberOfResults=50&f:@lastinitial=['.$char.']'));
    $html = str_get_html($data);

    foreach($html->find('.CoveoResult') as $item)
    {

        if($item->find('.search-results-card__content__heading', 0))
        {

            $name = trim($item->find('.search-results-card__content__heading', 0)->plaintext);

            if(!empty($item->find('img', 0)->src))
            {
                $image = $item->find('img', 0)->src;
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
                    $email = str_replace('mailto:', '', $link->href);
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

            $row = array(
                'url' => $base_url.$item->find('.search-results-card__content__heading', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => $phone,
                'email' => $email,
                'title' => trim($item->find('.search-results-card__content__sub-heading', 0)->plaintext),
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