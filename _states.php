<?php
include 'config.php';
$q = $pdo->prepare('SELECT * FROM `states` WHERE `country_id`=? ORDER BY `name` ASC');
$q->execute(array($_GET['country']));
echo json_encode($q->fetchAll());
?>