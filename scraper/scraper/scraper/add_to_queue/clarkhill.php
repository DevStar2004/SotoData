<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.clarkhill.com';
$spider_name = 'clarkhill';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();
$arr = array();

foreach (range('A', 'Z') as $char) {

    for ($i=0; $i < 10; $i++) {

        $data = fetch_quick($base_url.'/api/people?page='.$i.'&letter='.$char);
        $result = json_decode($data, 1);
        if(!empty($result['results']))
        {
            foreach($result['results'] as $row)
            {

                if(!in_array($base_url.$row['url'], $arr))
                {

                    if(strpos($row['url'], 'http') !== false)
                    {
                        $url = $row['url'];
                    }
                    else
                    {
                        $url = $base_url.$row['url'];
                    }

                    $values[] = $row;
                    $arr[] = $base_url.$row['url'];

                    $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
                    $q->execute(array(
                        $spider_name,
                        $base_url.$row['url'],
                        json_encode($row),
                        'pending',
                        time(),
                        NULL
                    ));
                }

            }
        }

    }

}

echo count($values);

?><br/>