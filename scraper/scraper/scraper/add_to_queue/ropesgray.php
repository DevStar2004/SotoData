<?php
include '../config.php';
include '../simple_html_dom.php';

//https://www.ropesgray.com/sitecore/api/people/search?lastnameletter=&page=0&take=500&sc_lang=en&sc_site=main&sc_apikey=undefined

$base_url = 'https://www.ropesgray.com';
$spider_name = 'ropesgray';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));

$values = array();

$f = file_get_contents('https://www.ropesgray.com/sitecore/api/people/search?lastnameletter=&page=0&take=2000&sc_lang=en&sc_site=main&sc_apikey=undefined');
$data = json_decode($f, 1);

foreach($data['results'] as $item)
{
    if(isset($item['image'][0]['src']))
    {
        $image = $item['image'][0]['src'];
    }
    else
    {
        $image = '';
    }

    if($item['title'] !== 'Retired Partner')
    {
        $values[] = array(
            'image' => $image,
            'name' => $item['name'],
            'link' => $base_url.$item['url'],
            'title' => $item['title'],
            'email' => $item['email'],
            'phone' => $item['phone'][0]
        );
    }
}

foreach($values as $row)
{
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $row['link'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>