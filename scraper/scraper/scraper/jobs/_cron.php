<?php
include '../config.php';
include '../simple_html_dom.php';
include '../../vCard.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$q = $pdo->prepare('SELECT DISTINCT(spider_name) FROM `queue` WHERE `status`=\'pending\' ORDER BY RAND() LIMIT 1');
$q->execute();
$row = $q->fetch(PDO::FETCH_ASSOC);

$url = 'http://24.199.117.42/scraper/jobs/'.$row['spider_name'].'.php';

header('Location: '.$url);

?>