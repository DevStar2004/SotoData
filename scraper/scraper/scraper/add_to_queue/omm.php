<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.omm.com';
$spider_name = 'omm';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 70;
while ($i > 0) {

    $data = fetch('https://www.omm.com/professionals?page='.$i.'&engine=elastic&filter=title');

    $html = str_get_html($data);

    foreach($html->find('.search-result__professional') as $item)
    {

        $image = $item->find('.search-result__professional-image', 0)->style;

        if(empty($image))
        {
            $image = '';
        }
        else
        {
            $image = $base_url.get_string_between($image, 'background-image: url(&quot;', '&quot)');
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

        if($item->find('.search-result__professional-avatar', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.search-result__professional-name', 0)->plaintext));

            $row = array(
                'url' => $base_url.$item->find('.search-result__professional-name', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.search-result__professional-title', 0)->plaintext),
                'location' => @trim($item->find('.search-result__professional-link a', 0)->plaintext),
                'phone' => @str_replace('//', '', trim($phone)),
                'email' => @trim($item->find('[data-email]', 0)->{'data-email'}),
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