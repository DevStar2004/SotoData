<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mwe.com';
$spider_name = 'mwe';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents($base_url.'/wp-admin/admin-ajax.php?id=people-alm&post_id=84856&slug=wilmington&canonical_url=https%3A%2F%2Fwww.mwe.com%2Flocations%2Fwilmington%2F&posts_per_page=99999&page=0&offset=0&post_type=people&repeater=default&seo_start_page=1&preloaded=false&preloaded_amount=0&lang=en&order=DESC&orderby=date&action=alm_get_posts&query_type=standard');

$html = str_get_html(json_decode($data, 1)['html']);

foreach($html->find('.col-lg-4.col-md-6.col-12.default-margin-top') as $item)
{

    if($item->find('a', 0))
    {

        $name = trim($item->find('.profile-title', 0)->plaintext);

        if(!empty($item->find('img', 0)->src))
        {
            $image = $item->find('img', 0)->src;
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

        $email = get_string_between($item->innertext, 'setPeopleEmail(\'', '\')');
        $ex = explode('|', $item->find('.location.text-left', 0)->plaintext);

        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $name,
            'image' => $image,
            'phone' => $phone,
            'email' => @trim($email),
            'title' => @trim($ex[0]),
            'location' => @trim($ex[1])
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