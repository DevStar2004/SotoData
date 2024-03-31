<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.susmangodfrey.com';
$spider_name = 'susmangodfrey';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

foreach (range('A', 'Z') as $key => $char) {

    $data = @file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode($base_url.'/attorneys/?taxonomies.poa_alpha_taxonomy='.$char));
    $data = @get_string_between($data, '<body class="archive post-type-archive post-type-archive-poa_person wp-custom-logo wp-embed-responsive full-width-content genesis-breadcrumbs-hidden" data-aos-easing="ease" data-aos-duration="400" data-aos-delay="0">', '</body>');

    if(empty($data))
    {
        continue;
    }

    $html = str_get_html($data);

    if($html->find('.ais-InfiniteHits-item'))
    {
        foreach($html->find('.ais-InfiniteHits-item') as $item)
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

            if($item->find('.entry-title-link', 0))
            {

                $name = trim(preg_replace('/\s+/', ' ', $item->find('.entry-title-link', 0)->plaintext));

                $row = array(
                    'url' => $item->find('a', 0)->href,
                    'name' => $name,
                    'image' => $image,
                    'title' => @trim($item->find('.entry-title-link', 0)->plaintext),
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

}

echo count($values);

?><br/>