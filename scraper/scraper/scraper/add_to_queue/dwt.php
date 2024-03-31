<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.dwt.com';
$spider_name = 'dwt';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.dwt.com/people?viewall=true&page=100&take=1200'));
$html = str_get_html($data);

foreach($html->find('.contact-card') as $item)
{

    if(!empty($item->find('img', 0)->src))
    {
        $image = $base_url.@$item->find('img', 0)->src;
    }
    else
    {
        $image = '';
    }

    $row = array(
        'url' => $item->find('a.contact-card__name', 0)->href,
        'name' => $item->find('a.contact-card__name', 0)->plaintext,
        'image' => $image,
        'title' => @$item->find('.contact-card__title a', 0)->plaintext,
        'email' => @$item->find('.contact-card__email', 0)->plaintext,
        'phone' => @$item->find('.contact-card__phone a', 0)->plaintext
    );
    $values[] = $row;

    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array(
        $spider_name,
        $base_url.$row['url'],
        json_encode($row),
        'pending',
        time(),
        NULL
    ));

}

echo count($values);

?><br/>