<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.foley.com';
$spider_name = 'foley';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

for ($i=1; $i < 16; $i++) {

    $data = fetch_quick('https://www.foley.com/wp-json/wp/v2/people?page='.$i.'&per_page=100&order=desc&orderby=date&tax_relation=AND&type=1');
    $data = json_decode($data, 1);

    foreach ($data as $row) {

        $values[] = $row;
        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $row['link'], json_encode($row), 'pending', time(), NULL));

    }
}

echo count($values);

?><br/>