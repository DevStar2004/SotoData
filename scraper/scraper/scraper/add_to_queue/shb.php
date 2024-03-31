<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.shb.com';
$spider_name = 'shb';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $char)
{
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode('https://www.shb.com/professionals?letter='.$char.'&sort=lnameSort&page=100'));
    $html = str_get_html($data);

    foreach($html->find('.person') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
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

        if($item->find('a', 0))
        {
            $name = trim($item->find('.person-title a', 0)->plaintext);

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.person-info .person-snapshot-title', 0)->plaintext),
                'location' => @trim($item->find('.person-info p', 0)->plaintext),
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

}

echo count($values);

?><br/>