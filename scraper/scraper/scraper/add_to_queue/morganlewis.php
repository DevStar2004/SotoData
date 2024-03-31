<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.morganlewis.com';
$spider_name = 'morganlewis';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 40;

while ($i > 0) {

    $data = fetch($base_url.'/api/custom/peoplesearch/search?keyword=&category=bb82d24a9d7a45bd938533994c4e775a&sortBy=lastname&pageNum='.$i.'&numberPerPage=50&numberPerSection=5&enforceLanguage=&languageToEnforce=&school=&position=&location=&court=&judge=');
    $html = str_get_html($data);

    foreach($html->find('.c-content-team__card') as $item)
    {

        if($item->find('a', 0))
        {

            $name = trim($item->find('.c-content-team__name', 0)->plaintext);

            if(!empty($item->find('img', 0)->src))
            {
                $image = $base_url.$item->find('img', 0)->src;
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

            $phone = '';
            foreach($item->find('a') as $link)
            {
                if(strpos($link->href, 'tel:') !== false)
                {
                    $phone = str_replace('tel:', '', $link->href);
                }
            }

            $row = array(
                'url' => $base_url.$item->find('a', 0)->href,
                'image' => $image,
                'name' => @trim($name),
                'phone' => @trim($item->find('.c-content-team__number', 0)->plaintext),
                'email' => @trim($email),
                'title' => @trim(str_replace(',', '', $item->find('.c-content-team__title', 0)->plaintext)),
                'location' => @trim(str_replace(',', '', $item->find('.c-content-team__city', 0)->plaintext)),
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