<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://frostbrowntodd.com';
$spider_name = 'frostbrowntodd';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 54;

while ($i > 0) {
    
    $data = file_get_contents('http://137.184.158.149:3000/?api=get2&url='.urlencode($base_url.'/search-results/?fwp_content_type=people&fwp_paged='.$i.'&fwp_sort=last_name_asc'));
    $html = str_get_html($data);

    foreach($html->find('.item.people') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('.name.h5', 0))
        {
            $name = trim($item->find('.name.h5', 0)->plaintext);

            $row = array(
                'url' => $item->find('.name.h5', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.position', 0)->plaintext),
                'location' => @trim($item->find('.location span', 0)->plaintext),
                'phone' => @trim(str_replace('Office Phone Number  ', '', $item->find('.contact a', 0)->plaintext)),
                'email' => @trim(str_replace($name, '', $item->find('.email-container', 0)->plaintext))
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