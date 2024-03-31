<?php
include 'config.php';
include 'simple_html_dom.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

$q = $pdo->prepare('UPDATE `job_status` SET `last_crawl`=0');
$q->execute(array());

?>