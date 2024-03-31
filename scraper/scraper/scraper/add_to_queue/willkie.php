<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.willkie.com';
$spider_name = 'willkie';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = fetch($base_url.'/api/sitecore/Search/ProfessionalResults?page=10000&alpha=&isAjax=false');
$html = str_get_html($data);

foreach($html->find('.col-6.col-md-4.col-lg-3') as $item)
{

    if($item->find('a', 0))
    {
        $name = trim($item->find('.h5.stretched-link', 0)->plaintext);

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
            'url' => $base_url.$item->find('a', 0)->href,
            'name' => $name,
            'title' => @trim($item->find('span.text-14.d-block', 0)->plaintext),
            'location' => @trim($item->find('.contact-info.text-14 a', 0)->plaintext),
            'email' => $email,
            'phone' => $phone
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

echo count($values);

?><br/>