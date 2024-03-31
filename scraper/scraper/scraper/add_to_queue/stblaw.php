<?php

include '../config.php';
include '../simple_html_dom.php';

$base_url = 'https://www.stblaw.com';
$spider_name = 'stblaw';

$q = $pdo->prepare('DELETE FROM `queue` WHERE `spider_name`=?');
$q->execute(array($spider_name));


$values = array();

$payload = 'searchId=&clientSolutions=&industries=&practice=&offices=&position=&law=&lawSchoolName=&school=&schoolName=&selectedLetter=&searchCount=0&pageFilter=&pageFilterId=&roleId=&take=999999&skip=0&page=1&pageSize=999999';
$rows = json_decode(file_get_contents('http://137.184.158.149:3000/?api=post&postData='.base64_encode($payload).'&url='.urlencode($base_url.'/dataservices/DataServices.Content.Services.Json.AttorneyLookup/TeamMembers')), 1);

foreach ($rows['TeamModels'] as $value) {
    
    if(!empty($value['UrlName']))
    {
        $values[] = $value;
        $row = $value;

        $q = $pdo->prepare('INSERT INTO `queue` VALUES (?, ?, ?, ?, ?, ?)');
        $q->execute(array($spider_name, $base_url.'/our-team/partners/'.$row['UrlName'], json_encode($row), 'pending', time(), NULL));
    }
    
}

echo count($values);

?><br/>