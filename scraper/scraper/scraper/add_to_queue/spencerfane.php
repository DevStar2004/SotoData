<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.spencerfane.com';
$spider_name = 'spencerfane';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get2&url=https%3A%2F%2Fwww.spencerfane.com%2Fprofessionals%2F%3Ffirst_name%3D%26last_name%3D%26practice_id%3D%26industry_id%3D%26position_id%3D%26bar_id%3D%26court_id%3D%26office_id%3D%26education_id%3D%26keyword%3D0');
$html = str_get_html($data);

foreach($html->find('.single-professional-tile') as $item)
{

    $email = '';
    foreach($item->find('a') as $link)
    {
        if(strpos($link->href, 'mailto:') !== false)
        {
            $email = str_replace('mailto:', '', $link->href);
        }
    }

    if($item->find('a', 0))
    {
        $name = trim(str_replace('&nbsp;', ' ', $item->find('.pro-name.mb-0', 0)->plaintext));

        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $name,
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

echo count($values);

?><br/>