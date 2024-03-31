<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.ogletree.com';
$spider_name = 'ogletree';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach(range('A', 'Z') as $char)
{
    $data = fetch($base_url.'/people/?glossary='.$char);
    $html = str_get_html($data);

    foreach($html->find('.article-wrapper.flex.flex-row.flex-wrap article') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('a', 0))
        {
            $name = trim($item->find('a.line-clamp-4', 0)->plaintext);

            $row = array(
                'url' => trim($item->find('a', 0)->href),
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.attorney-txt .title', 0)->plaintext),
                'phone' => @trim($item->find('.attorney-txt div', 1)->plaintext),
                'location' => @trim($item->find('.attorney-txt .location', 0)->plaintext),
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