<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.gibsondunn.com';
$spider_name = 'gibsondunn';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 91;

while ($i > 0) {

    $data = fetch($base_url.'/?paged1='.$i.'&search=lawyer&type=lawyer&s&school');
    $html = str_get_html($data);

    foreach($html->find('.search-result-mobile-section') as $item)
    {

        $email = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'mailto:') !== false)
            {
                $email = str_replace('mailto:', '', $link->href);
            }
        }

        $phone = '';
        foreach($item->find('a') as $link)
        {
            if(strpos($link->href, 'tel:') !== false)
            {
                $phone = str_replace('tel:', '', $link->href);
            }
        }

        if($item->find('a', 0))
        {
            $name = trim($item->find('h4', 0)->plaintext);

            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $name,
                'title' => @trim($item->find('p', 0)->plaintext),
                'phone' => @trim($item->find('p', 2)->plaintext),
                'location' => @trim($item->find('p', 1)->plaintext),
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

    $i--;
}

echo count($values);

?><br/>