<?php

include 'config.php';

ini_set('max_execution_time', 0);
set_time_limit(0);

$q = $pdo->prepare('SELECT `id`, `education` FROM `people` WHERE
(LOWER(`education`) LIKE \'%j.d.%\'
OR LOWER(`education`) LIKE \'%jd%\'
OR LOWER(`education`) LIKE \'%juris%\'
OR LOWER(`education`) LIKE \'%school of law%\'
OR LOWER(`education`) LIKE \'%j. d%\') AND (`JD_year`=\'\' OR `JD_year`=0 OR `JD_year`<1900 OR `JD_year`>2024)');
$q->execute();
$people = $q->fetchAll(PDO::FETCH_ASSOC);

foreach ($people as $row) {
    $education = json_decode($row['education'], 1);
    foreach($education as $item)
    {
        if(
            strpos(strtolower($item), 'j.d.') !== false ||
            strpos(strtolower($item), 'jd') !== false ||
            strpos(strtolower($item), 'juris') !== false ||
            strpos(strtolower($item), 'j. d') !== false
        )
        {

            $re = '~\b\d{4}\b\+?~';
            preg_match_all($re, html_entity_decode(strip_tags($item)), $years);

            if(!empty($years[0][0]))
            {
                $q = $pdo->prepare('UPDATE `people` SET `JD_year`=? WHERE `id`=? LIMIT 1');
                $q->execute(array($years[0][0], $row['id']));
            }

            unset($years);
            unset($education);
            unset($item);
        }
    }
}

?>