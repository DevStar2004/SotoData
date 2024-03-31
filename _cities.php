<?php
include 'config.php';
$q = $pdo->prepare('SELECT * FROM `cities` WHERE `state_id`=? ORDER BY `name` ASC');
$q->execute(array($_GET['city']));
echo json_encode($q->fetchAll());
?>