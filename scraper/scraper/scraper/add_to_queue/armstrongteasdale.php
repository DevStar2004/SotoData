<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.armstrongteasdale.com';
$spider_name = 'armstrongteasdale';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


for ($i=0; $i < 17; $i++) {

    $data = file_get_contents('http://137.184.158.149:3000/?api=get&url=https://www.armstrongteasdale.com/people/page/'.$i.'/?search%5Bkeyword%5D=&view=all');
    $html = str_get_html($data);

    foreach($html->find('article.person-listing') as $item)
    {
        $row = array(
            'url' => $item->find('a', 0)->href,
            'name' => $item->find('.person-listing__name.person-name', 0)->plaintext,
            'image' => $item->find('img', 0)->src,
            'position' => @$item->find('.person-listing__title', 0)->plaintext,
            'phone' => @$item->find('a.phone-link', 0)->plaintext,
            'email' => $item->find('.person-email-link a', 0)->plaintext
        );
        $values[] = $row;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $row['url'], json_encode($row), 'pending', time(), NULL));
        
    }

}

echo count($values);

?><br/>