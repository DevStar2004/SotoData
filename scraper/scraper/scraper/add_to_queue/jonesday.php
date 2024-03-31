<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.jonesday.com';
$spider_name = 'jonesday';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$locations = array();
$data = fetch($base_url.'/en/locations');
$html = str_get_html($data);
foreach($html->find('.locationslist__item a') as $item)
{
    $locations[] = $item->plaintext;
}

$lawyers = array();

foreach ($locations as $location)
{

    $i = 1;

    while($i < 20) {

        if($i < 2)
        {
            $first = '';
        }
        else
        {
            $first = $i*20;
        }

        $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/en/lawyers#first='.$first.'&sort=@fieldz95xlevelsort ascending&f:@facetz95xlocation=['.$location.']'));
        $html = str_get_html($data);

        if(!$html->find('.coveo-pager-next-icon', 0))
        {
            break;
        }

        foreach($html->find('.coveo-result-frame') as $item)
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

            if($item->find('.person__name', 0))
            {

                if(!in_array($item->find('a', 0)->href, $lawyers))
                {
                    $lawyers[] = $item->find('a', 0)->href;

                    $name = trim(preg_replace('/\s+/', ' ', $item->find('.person__name', 0)->plaintext));

                    $row = array(
                        'url' => $item->find('a', 0)->href,
                        'name' => $name,
                        'image' => $image,
                        'title' => @trim($item->find('.person__title', 0)->plaintext),
                        'location' => @trim($item->find('.person__meta', 0)->plaintext),
                        'phone' => @trim($item->find('.person__meta', 1)->plaintext),
                        'email' => @trim($item->find('.person__meta', 2)->plaintext),
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

        $i++;

    }

    

    
}

echo count($values);

?><br/>