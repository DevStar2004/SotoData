<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.millercanfield.com';
$spider_name = 'millercanfield';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/people.html?do_item_search=1'));
$html = str_get_html($data);

foreach($html->find('.results_list div') as $item)
{

    if($item->find('.phone', 0))
    {

        $name = trim($item->find('a', 0)->plaintext);

        $email = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'mailto:') !== false)
            {
                $email = str_replace('mailto:', '', $link->href);
            }
        }

        $row = array(
            'url' => $base_url.'/'.$item->find('a', 0)->href,
            'name' => $name,
            'phone' => @$item->find('.phone', 0)->plaintext,
            'email' => $email,
            'title' => trim($item->find('.position', 0)->plaintext),
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