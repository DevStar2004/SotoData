<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.cliffordchance.com';
$spider_name = 'cliffordchance';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 65; $i++) {

    $data = fetch($base_url.'/people_and_places.html?_charset_=UTF-8&fname=&lname=&tags=all&tags=all&office=all&x=52&y=8&partnersview=true&page='.$i.'#partners');
    $html = str_get_html($data);

    foreach($html->find('.article_result') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = $base_url.@$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        $tel = '';

        foreach($item->find('p') as $span)
        {
            if(strpos($span->plaintext, 'Tel ') !== false)
            {
                $tel = $span->plaintext;
                $tel = str_replace('Tel  ', '', $tel);
            }
        }

        $row = array(
            'url' => $base_url.$item->find('a', 0)->href,
            'name' => $item->find('h1 a', 0)->{'title'},
            'image' => $image,
            'title' => trim($item->find('h1 a span', 0)->plaintext),
            'phone' => $tel
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