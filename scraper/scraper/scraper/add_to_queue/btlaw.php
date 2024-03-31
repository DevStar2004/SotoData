<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://btlaw.com';
$spider_name = 'btlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $key => $char) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&element=loadMoreBtn&url='.urlencode($base_url.'/professionals/xpqprofresults.aspx?xpst=professionalresults#?alpha='.$char));
    $html = str_get_html($data);

    foreach($html->find('.row.social-listing.ng-scope') as $item)
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

        if($item->find('.infoBlock', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.infoBlock .col-md-6 h4', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.infoBlock .col-md-6 h4', 1)->plaintext),
                'location' => @trim($item->find('.info-date a', 0)->plaintext),
                'phone' => @str_replace('//', '', trim($phone)),
                'email' => @trim(str_replace('mailto:', '', $item->find('[data-email-content]', 0)->{'data-email-content'})),
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