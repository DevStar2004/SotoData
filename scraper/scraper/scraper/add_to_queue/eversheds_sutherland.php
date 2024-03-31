<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.eversheds-sutherland.com';
$spider_name = 'eversheds_sutherland';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


foreach(range('A', 'Z') as $char)
{
    
    $data = fetch_quick('https://www.eversheds-sutherland.com/api/listing/search?type=People&page=1&facet='.$char.'&pageSize=500&sort=LastName');
    $data = json_decode($data, 1);

    $values = $data['Results'];

    foreach($values as $value)
    {
                $row = array(
                    'url' => $base_url.str_replace('/en/', '/en/global/', $value['Link']),
                    'name' => $value['Title'],
                    'position' => $value['Role'],
                    'country' => $value['Offices'][0],
                );

                $values[] = $row;

                $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
                $q->execute(array(
                    $spider_name,
                    $row['url'],
                    json_encode($row),
                    'pending',
                    time(),
                    NULL
                ));
    }

}

echo count($values);

?><br/>