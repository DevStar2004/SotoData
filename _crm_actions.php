<?php
include 'config.php';

if($_GET['action'] == 'status')
{
  $q = $pdo->prepare('DELETE FROM `crm` WHERE `type`=? AND `member_id`=? AND `person_id`=?');
  $q->execute(array(
    $_GET['action'],
    $_SESSION['id'],
    $_GET['person'],
    ));
}

$q = $pdo->prepare('SELECT `names` FROM `people` WHERE `id`=?');
$q->execute(array($_GET['person']));
$person = $q->fetch(PDO::FETCH_ASSOC);

$q = $pdo->prepare('INSERT INTO `crm` VALUES (?,?,?,?,?,?,?,?)');
$q->execute(array(
  $_GET['action'],
  $_GET['person'],
  $person['names'],
  $_SESSION['id'],
  $_GET['content'],
  0,
  time(),
  NULL
));
?>