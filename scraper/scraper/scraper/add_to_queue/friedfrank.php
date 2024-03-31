<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.friedfrank.com';
$spider_name = 'friedfrank';
$firm_name = 'Fried, Frank, Harris, Shriver & Jacobson LLP';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$locations = array('Brussels', 'Frankfurt', 'London', 'New York', 'Washington, DC');

foreach($locations as $location)
{
    for ($i=1; $i < 50; $i++) {

        $data = htmlspecialchars_decode(fetch('https://www.friedfrank.com/our-people.turbo_stream?office='.$location.'&page='.$i));
        $html = str_get_html($data);

        foreach($html->find('.search__autofill-result') as $item)
        {

            $row = array(
                'name' => $item->find('.search__autofill-result-name', 0)->plaintext,
                'position' => $item->find('.search__autofill-result-position', 0)->plaintext,
                'practice' => $item->find('.search__autofill-result-other', 0)->plaintext,
                'location' => $item->find('.search__autofill-result-other', 1)->plaintext,
                'phone' => $item->find('a.js-phone', 0)->plaintext,
                'email' => $item->find('a.js-email-link', 0)->plaintext,
                'url' => $item->find('a', 0)->href,
                'image' => $item->find('img', 0)->src,

            );

            $values[] = $row;

            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));

        }

        if(strpos($data, 'able to find anyone matching your search criteria.') !== false) { break; }

    }

}

echo count($values);

?><br/>