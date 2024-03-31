<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.gmlaw.com';
$spider_name = 'gmlaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$data = file_get_contents('http://137.184.158.149:3000/?api=get2&useProxy=1&url='.urlencode($base_url.'/our-people/?letter=all'));
$html = str_get_html(get_string_between($data, '<table id="sorting" class="wow fadeInUp" cellspacing="0" border="0" cellpadding="0" width="100%">', '</table>'));

foreach($html->find('tbody td') as $item)
{

    if(!empty($item->find('img', 0)->src))
    {
        $image = @$item->find('img', 0)->src;
    }
    else
    {
        $image = '';
    }

    if($item->find('.spotblock a', 0))
    {
        $name = trim($item->find('.spotblock a', 0)->plaintext);

        $row = array(
            'url' => $item->find('.spotblock a', 0)->href,
            'name' => $name,
            'image' => $image,
            'phone' => @trim($item->find('.spotblock a', 1)->plaintext),
            'email' => @trim($item->find('.spotblock a', 2)->plaintext),
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

echo count($values);

?><br/>