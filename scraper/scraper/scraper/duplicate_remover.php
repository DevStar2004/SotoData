<?php

include 'config.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$q = $pdo->prepare('SELECT * FROM `queue` ORDER BY `id` DESC');
$q->execute();

$duplicates = 0;
$peopleSources = array();

foreach ($q as $row) {

    if(in_array($row['url'], $peopleSources) !== false)
    {
        $q = $pdo->prepare('DELETE FROM `queue` WHERE `id`=? LIMIT 1');
        $q->execute(array($row['id']));
        $duplicates++;
        continue;
    }

    $peopleSources[] = $row['url'];

}

echo $duplicates.' duplicates removed from queue.';

$q = $pdo->prepare('SELECT * FROM `people` ORDER BY `id` DESC');
$q->execute();

$duplicates = 0;
$peopleSources = array();

foreach ($q as $row) {

    if(in_array($row['source'], $peopleSources) !== false)
    {
        $q = $pdo->prepare('DELETE FROM `people` WHERE `id`=? LIMIT 1');
        $q->execute(array($row['id']));
        $duplicates++;
        continue;
    }

    $peopleSources[] = $row['source'];

}

echo '<hr/>';

echo $duplicates.' duplicates removed from people.';

$q = $pdo->prepare('SELECT * FROM `linkedIn` ORDER BY `id` DESC');
$q->execute();

$duplicates = 0;
$peopleSources = array();

foreach ($q as $row) {

    if(in_array($row['name'], $peopleSources) !== false)
    {
        $q = $pdo->prepare('DELETE FROM `linkedIn` WHERE `id`=? LIMIT 1');
        $q->execute(array($row['id']));
        $duplicates++;
        continue;
    }

    $peopleSources[] = $row['name'];

}

echo '<hr/>';

echo $duplicates.' duplicates removed from linkedIn.';

?>