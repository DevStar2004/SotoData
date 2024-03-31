<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.clearygottlieb.com';
$spider_name = 'clearygottlieb';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$i = 216;

while ($i >= 0) {
    
    $data = json_decode(file_get_contents('http://137.184.158.149:3000/?api=postJson&postData='.base64_encode('[{"globalSearchId":"854F9C73-B683-44AB-9C98-1166B5BE940A","searchFields":[],"sorting":[],"page":'.$i.'}]').'&url='.urlencode($base_url.'/jsapi/bio/professional-search')), 1)[0]['Results'];

    foreach($data as $row)
    {
        $values[] = $row;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array(
            $spider_name,
            $base_url.$row['ItemUrl'],
            json_encode($row),
            'pending',
            time(),
            NULL
        ));
    }
    
    $i--;

}

echo count($values);

?><br/>