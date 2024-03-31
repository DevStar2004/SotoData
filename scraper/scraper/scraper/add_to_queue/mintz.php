<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.mintz.com';
$spider_name = 'mintz';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 55;
while ($i > 0) {

    $data = fetch($base_url.'/our-people?search=&letter=&office=All&parent_practice=All&parent_industry=All&title=All&issue=All&category=All&page='.$i);
    $html = str_get_html($data);

    foreach($html->find('.item--person-card') as $item)
    {

        if($item->find('a', 0))
        {

            $name = trim($item->find('h3.item__title', 0)->plaintext);

            if(!empty($item->find('img', 0)->src))
            {
                $image = $item->find('img', 0)->src;
            }
            else
            {
                $image = '';
            }

            $email = cfDecodeEmail(str_replace('/cdn-cgi/l/email-protection#', '', $item->find('.item__row.item__row--summary a', 0)->href));

            $row = array(
                'url' => $item->find('h3.item__title a', 0)->href,
                'name' => $name,
                'image' => $image,
                'phone' => @trim($item->find('.item__row.item__row--summary h4', 1)->plaintext),
                'email' => @trim($email),
                'title' => @trim($item->find('.item__subtitle', 0)->plaintext),
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