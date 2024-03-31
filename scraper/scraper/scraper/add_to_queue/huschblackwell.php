<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.huschblackwell.com';
$spider_name = 'huschblackwell';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 79;

while ($i > 0) {

    $data = fetch($base_url.'/ajax/bioSearch.aspx?page='.$i.'&alpha=&sort=alpha');
    $html = str_get_html($data);

    foreach($html->find('.bio-headshot-wrap') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('.bio-name', 0))
        {
            $name = trim($item->find('.bio-name', 0)->plaintext);

            $row = array(
                'url' => $base_url.$item->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.bio-title', 0)->plaintext),
                'location' => @trim($item->find('.bio-office', 0)->plaintext),
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