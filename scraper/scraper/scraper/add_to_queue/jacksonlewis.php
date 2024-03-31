<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.jacksonlewis.com';
$spider_name = 'jacksonlewis';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 48;

while ($i > 0) {
    
    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.jacksonlewis.com/people?filter=people&page='.$i));
    $html = str_get_html($data);

    foreach($html->find('.views-row') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$base_url.$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('a', 0))
        {

            $email = '';
            $phone = '';

            $email = str_replace('mailto:', '', base64_decode(get_string_between($item->innertext, 'atob("', '")')));

            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'mailto:') !== false)
                {
                    $email = str_replace('mailto:', '', $link->href);
                    break;
                }
            }

            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'tel:') !== false)
                {
                    $phone = str_replace('tel:', '', $link->href);
                    break;
                }
            }

            $ex = explode(', ', $item->find('.views-field.views-field-nothing span', 0)->plaintext);

            $row = array(
                'url' => $item->find('a', 0)->href,
                'name' => $item->find('.views-field.views-field-title a', 0)->plaintext,
                'image' => $image,
                'title' => @trim($ex[0]),
                'location' => @trim($ex[1]),
                'phone' => @trim($phone),
                'email' => @trim($email)
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

    $i = $i-1;
}

echo count($values);

?><br/>