<?php

include 'config.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$dir = 'add_to_queue/';
$files = scandir($dir);

$i = 0;

foreach ($files as $file) {
    if(is_file('add_to_queue/'.$file))
    {
        $spider_name = str_replace('.php', '', $file);
        $q = $pdo->prepare('SELECT * FROM `spider_status` WHERE `spider_name`=? LIMIT 1');
        $q->execute(array($spider_name));
        if($q->rowcount()<1)
        {
            $q = $pdo->prepare('INSERT INTO `spider_status` VALUES (?,?,?)');
            $q->execute(array(time(), $spider_name, NULL));
            header('Location: '.$$root.'/scraper/add_to_queue/'.$file);
            exit();
        }
    }
}

?>