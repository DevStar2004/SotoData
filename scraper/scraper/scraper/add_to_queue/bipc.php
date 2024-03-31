<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.bipc.com';
$spider_name = 'bipc';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents($base_url.'/people');
$html = str_get_html($data);

foreach($html->find('.people-grid .vcard') as $item)
{
    $row = array(
        'url' => $base_url.'/'.$item->find('.name a', 0)->href,
        'name' => $item->find('.name a', 0)->plaintext,
        'image' => $base_url.'/'.$item->find('img', 0)->src,
        'title' => $item->find('div.title', 0)->plaintext,
        'email' => $item->find('.email a', 0)->plaintext,
        'phone' => $item->find('.phone a', 0)->plaintext
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

echo count($values);

if(count($values)<1)
{
    header("Refresh:0; url=bipc.php");
}

?><br/>