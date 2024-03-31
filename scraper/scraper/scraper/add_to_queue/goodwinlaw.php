<?php
include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.goodwinlaw.com';
$spider_name = 'goodwinlaw';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$i = 440;
while ($i > 0) {
    
    $data = fetch($base_url.'/sitecore/api/search/search?sc_site=main&sections=023d7424bec6498e8d92234c10188970&page='.$i.'&sortBy=1');
    $rows = json_decode($data, 1)['results'];

    foreach ($rows as $value) {
        $values[] = $value;
        $row = $value;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.$row['url'], json_encode($row), 'pending', time(), NULL));
        
    }

    $i--;

}

echo count($values);

?><br/>