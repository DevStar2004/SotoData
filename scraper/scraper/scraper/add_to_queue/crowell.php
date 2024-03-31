<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.crowell.com';
$spider_name = 'crowell';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 700;
while ($i > 0) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url=https://www.crowell.com/en/professionals?f='.$i);
    $html = str_get_html($data);

    foreach($html->find('.rs-result') as $item)
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
            if(strpos($link->href, 'mailto:') !== false || strpos($link->href, '&#109;&#97;&#105;') !== false)
            {
                $email = str_replace('mailto:', '', html_entity_decode($link->href));
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

        foreach($item->find('div') as $item_)
        {
            if(strpos($item_->class, 'styles__type__personTitle-')>-1)
            {
                $title = trim(preg_replace('/\s+/', ' ', $item_->plaintext));
            }
        }

        foreach($item->find('p') as $item_)
        {
            if(strpos($item_->class, 'styles__type__personName-')>-1)
            {
                $name = trim(preg_replace('/\s+/', ' ', $item_->plaintext));
            }
        }

        foreach($item->find('a') as $item_)
        {
            if(strpos($item_->class, 'styles__type__officeName-')>-1)
            {
                $location = trim(preg_replace('/\s+/', ' ', $item_->plaintext));
            }
        }

        if($name)
        {

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($title),
                'location' => @trim($location),
                'phone' => @urldecode(str_replace('//', '', trim($phone))),
                'email' => @trim(base64_decode($email)),
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

    $i = $i-20;
}

echo count($values);

?><br/>