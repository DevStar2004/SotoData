<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://csklegal.com';
$spider_name = 'csklegal';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 1;
while ($i > 0) {


    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url='.urlencode('https://www.csklegal.com/team?do_item_search=1&letter=#form-search-results'));
    $html = str_get_html($data);

    foreach($html->find('.results_list li') as $item)
    {

        if(!empty($item->find('img', 0)->src))
        {
            $image = @$item->find('img', 0)->src;
        }
        else
        {
            $image = '';
        }

        if($item->find('.nametitle', 0))
        {

            $name = trim(preg_replace('/\s+/', ' ', $item->find('.nametitle .title', 0)->plaintext));

            $row = array(
                'url' => 'https://www.csklegal.com/'.$item->find('a', 0)->href,
                'name' => $name,
                'image' => $image,
                'title' => @trim($item->find('.nametitle .position', 0)->plaintext),
                'location' => @trim($item->find('.office a', 0)->plaintext),
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