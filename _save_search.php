<?php
include 'config.php';
$q = $pdo->prepare('INSERT INTO `saved_searches` VALUES (?,?,?,?)');
$q->execute(array($_POST['title'], $_POST['content'], $_POST['member_id'], NULL));
?>