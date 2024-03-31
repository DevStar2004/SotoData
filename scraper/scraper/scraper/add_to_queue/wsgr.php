<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.wsgr.com';
$spider_name = 'wsgr';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$i = 13;
while ($i > 0) {
    
    $data = fetch('https://www.wsgr.com/_site/search?l=&f=0&space=1&v=attorney&s=100&page='.$i);
    $rows = json_decode($data, 1)['hits']['ALL']['hits'];

    foreach ($rows as $value) {
        $values[] = $value;
    }

    $i--;

}

foreach($values as $row)
{
    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
    $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
}

echo count($values);

?><br/>