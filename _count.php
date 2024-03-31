<?php
include 'config.php';
$q = $pdo->prepare('SELECT COUNT(*) AS count FROM `people`');
$q->execute();
$res = $q->fetch(PDO::FETCH_ASSOC)['count'];
echo number_format($res, 0, '.', ',');
?>