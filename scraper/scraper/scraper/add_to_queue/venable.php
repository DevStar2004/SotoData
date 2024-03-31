<?php
include '../config.php';

$base_url = 'https://www.venable.com';
$spider_name = 'venable';
$firm_name = 'Venable LLP';

$values = array();

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


foreach (range('A', 'Z') as $char) {

    for ($i=1; $i < 999999; $i++) {
        $data = json_decode(file_get_contents($base_url.'/api/professionals/results?letter='.$char.'&page='.$i), 1);

        if($data['hasMoreResults'] != 'true')
        {
            break;
        }

        foreach($data['data'] as $row)
        {
            $values[] = $row;
            $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
            $q->execute(array($spider_name, $row['Url'], json_encode($row), 'pending', time(), NULL));
        }

    }

}

echo count($values);

?><br/>