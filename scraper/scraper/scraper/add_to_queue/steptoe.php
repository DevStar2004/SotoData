<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.steptoe.com';
$spider_name = 'steptoe';

$values = array();

foreach (range('A', 'Z') as $char) {

    $data = fetch($base_url.'/_site/search?l='.strtolower($char).'&s=999999&f=0&v=attorney&json=true');
    $rows = json_decode($data, 1)['hits']['ALL']['hits'];

    foreach ($rows as $value) {
        $values[] = $value;
    }

}

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


foreach($values as $row)
{
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>